<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../includes/ensure_rooms.php';
require_once __DIR__ . '/../includes/cohort_helpers.php';

$res_rooms = mysqli_query($conn, "SELECT * FROM room ORDER BY room_id ASC");
$rooms = []; $labRooms = []; $lecRooms = [];
while ($r = mysqli_fetch_assoc($res_rooms)) {
    $rooms[] = $r;
    if ($r['room_desc'] === 'COMPUTER lAB' || $r['room_desc'] === 'Computer Lab 1' || $r['room_desc'] === 'Computer Lab 2') {
        $labRooms[] = $r;
    } else {
        $lecRooms[] = $r;
    }
}

$subjects = [];
$res = mysqli_query($conn, "SELECT * FROM subjects WHERE sub_lechrsprday > 0 ORDER BY 
    CASE subject_type 
        WHEN 'LAB' THEN 1 
        WHEN 'MAJOR_PROJECT' THEN 2
        WHEN 'MINI_PROJECT' THEN 3
        WHEN 'NSS' THEN 4 
        WHEN 'COMMUNITY_PROJECT' THEN 5
        WHEN 'THEORY' THEN 6 
        WHEN 'ONE_CREDIT' THEN 7 
        ELSE 8 
    END, 
    sub_lechrsprday DESC");

while ($s = mysqli_fetch_assoc($res)) {
    if (empty($s['group_id']) || intval($s['group_id']) === 0) {
        if (preg_match('/\d+/', $s['sub_code'], $m)) {
            $yr = $m[0][0];
        } else {
            $yr = 1;
        }
        $s['group_id'] = intval($s['dept_id'] . $yr);
    }
    $subjects[] = $s;
}

$days = [1, 2, 3, 4, 5, 6];
$morning_blocks = [[1, 2], [3, 4]];
$afternoon_blocks = [[5, 6]];
$all_2hr_blocks = array_merge($morning_blocks, $afternoon_blocks);

function isTeacherTheoryGapOk($teacher, $day, $slot, &$teacher_theory, &$teacher_busy) {
    if (isset($teacher_busy[$teacher][$day][$slot])) return false;
    if ($slot > 1 && isset($teacher_theory[$teacher][$day][$slot - 1])) return false;
    if ($slot < 7 && isset($teacher_theory[$teacher][$day][$slot + 1])) return false;
    return true;
}

function isTeacherConsecutiveOk($teacher, $day, $slot, &$teacher_busy) {
    if ($slot > 2 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot - 2])) return false;
    if ($slot > 1 && $slot < 7 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot + 1])) return false;
    if ($slot < 6 && isset($teacher_busy[$teacher][$day][$slot + 1]) && isset($teacher_busy[$teacher][$day][$slot + 2])) return false;
    return true;
}

function isSlotAvailable($teacher, $room, $group, $day, $slot, &$teacher_busy, &$room_busy, &$group_busy) {
    if (isset($teacher_busy[$teacher][$day][$slot])) return false;
    if (isset($room_busy[$room][$day][$slot])) return false;
    if (isset($group_busy[$group][$day][$slot])) return false;
    return true;
}

function markSlotBusy($teacher, $room, $group, $day, $slot, $sub_id, &$teacher_busy, &$room_busy, &$group_busy, &$group_day_subs, $is_theory = false, &$teacher_theory = null) {
    $teacher_busy[$teacher][$day][$slot] = true;
    $room_busy[$room][$day][$slot] = true;
    $group_busy[$group][$day][$slot] = $sub_id;
    if (!isset($group_day_subs[$group][$day][$sub_id])) {
        $group_day_subs[$group][$day][$sub_id] = 0;
    }
    $group_day_subs[$group][$day][$sub_id]++;
    if ($is_theory && $teacher_theory !== null) {
        $teacher_theory[$teacher][$day][$slot] = true;
    }
}

function isStudentBackToBackSubject($group, $day, $slot, $sub_id, &$group_busy) {
    if ($slot > 1 && isset($group_busy[$group][$day][$slot - 1]) && $group_busy[$group][$day][$slot - 1] == $sub_id) {
        return true;
    }
    if ($slot < 6 && isset($group_busy[$group][$day][$slot + 1]) && $group_busy[$group][$day][$slot + 1] == $sub_id) {
        return true;
    }
    return false;
}

for ($seed = 0; $seed <= 30; $seed++) {
    $teacher_busy = []; $teacher_theory = []; $room_busy = []; $group_busy = []; $group_day_subs = []; $schedule_entries = [];

    $res_ft = mysqli_query($conn, "SELECT teacher_id, day_id, slot_id, is_available FROM faculty_timing WHERE is_available = 0");
    if ($res_ft) {
        while ($ft = mysqli_fetch_assoc($res_ft)) {
            $teacher_busy[intval($ft['teacher_id'])][intval($ft['day_id'])][intval($ft['slot_id'])] = true;
        }
    }

    // Step A: LAB
    foreach ($subjects as $sub) {
        if ($sub['subject_type'] !== 'LAB') continue;
        $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']); $dept_id = intval($sub['dept_id']);
        foreach ($all_2hr_blocks as $blk) {
            $s1 = $blk[0]; $s2 = $blk[1];
            foreach ($days as $d) {
                if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
                if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
                if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy) || !isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
                $available_labs = [];
                foreach ($labRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) $available_labs[] = $lr;
                }
                if (count($available_labs) >= 2) {
                    markSlotBusy($teacher, $available_labs[0]['room_id'], $group, $d, $s1, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                    markSlotBusy($teacher, $available_labs[0]['room_id'], $group, $d, $s2, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                    $room_busy[$available_labs[1]['room_id']][$d][$s1] = true;
                    $room_busy[$available_labs[1]['room_id']][$d][$s2] = true;
                    $schedule_entries[] = 1; $schedule_entries[] = 1; $schedule_entries[] = 1; $schedule_entries[] = 1;
                    break 2;
                }
            }
        }
    }

    // Step B1: MAJOR_PROJECT
    foreach ($subjects as $sub) {
        if ($sub['subject_type'] !== 'MAJOR_PROJECT') continue;
        $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']);
        $days_assigned = 0;
        foreach ($days as $d) {
            if ($days_assigned >= 3) break;
            foreach ($afternoon_blocks as $blk) {
                $s1 = $blk[0]; $s2 = $blk[1];
                if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
                if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
                if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy) || !isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                        markSlotBusy($teacher, $rid, $group, $d, $s1, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                        markSlotBusy($teacher, $rid, $group, $d, $s2, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                        $schedule_entries[] = 1; $schedule_entries[] = 1;
                        $days_assigned++;
                        break 2;
                    }
                }
            }
        }
    }

    // Step B2: MINI_PROJECT (Afternoon)
    foreach ($subjects as $sub) {
        if ($sub['subject_type'] !== 'MINI_PROJECT') continue;
        $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']);
        $days_assigned = 0;
        foreach ($days as $d) {
            if ($days_assigned >= 2) break;
            foreach ($afternoon_blocks as $blk) {
                $s1 = $blk[0]; $s2 = $blk[1];
                if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
                if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
                if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy) || !isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                        markSlotBusy($teacher, $rid, $group, $d, $s1, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                        markSlotBusy($teacher, $rid, $group, $d, $s2, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                        $schedule_entries[] = 1; $schedule_entries[] = 1;
                        $days_assigned++;
                        break 2;
                    }
                }
            }
        }
    }

    // Step C: NSS & COMMUNITY
    foreach ($subjects as $sub) {
        if (!in_array($sub['subject_type'], ['NSS', 'COMMUNITY_PROJECT'])) continue;
        $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']);
        $blocks_to_try = ($sub['subject_type'] === 'NSS') ? $afternoon_blocks : [[3, 4], [1, 2], [5, 6]];
        $sorted_d = $days;
        usort($sorted_d, function($a, $b) use ($group, $group_busy) {
            $ga = isset($group_busy[$group][$a]) ? count($group_busy[$group][$a]) : 0;
            $gb = isset($group_busy[$group][$b]) ? count($group_busy[$group][$b]) : 0;
            return $ga - $gb;
        });
        foreach ($blocks_to_try as $blk) {
            $s1 = $blk[0]; $s2 = $blk[1];
            foreach ($sorted_d as $d) {
                if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
                if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
                if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy) || !isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                        markSlotBusy($teacher, $rid, $group, $d, $s1, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                        markSlotBusy($teacher, $rid, $group, $d, $s2, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                        $schedule_entries[] = 1; $schedule_entries[] = 1;
                        break 3;
                    }
                }
            }
        }
    }

    // Step D & E: Theory
    $group_theory_subs = [];
    foreach ($subjects as $sub) {
        if (in_array($sub['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
            $grp = strval($sub['group_id']);
            $group_theory_subs[$grp][] = $sub;
        }
    }

    foreach ($group_theory_subs as $grp => &$sub_list) {
        usort($sub_list, function($a, $b) use ($teacher_busy, $days) {
            $req_a = intval($a['sub_lechrsprday']);
            $req_b = intval($b['sub_lechrsprday']);
            if ($req_a !== $req_b) return $req_b - $req_a;
            $t_a = intval($a['instructor']);
            $t_b = intval($b['instructor']);
            $busy_a = 0; $busy_b = 0;
            foreach ($days as $d) {
                for ($s = 1; $s <= 6; $s++) {
                    if (isset($teacher_busy[$t_a][$d][$s])) $busy_a++;
                    if (isset($teacher_busy[$t_b][$d][$s])) $busy_b++;
                }
            }
            return $busy_b - $busy_a;
        });
    }
    unset($sub_list);

    $day_slot_rotations = [
        1 => [1, 2, 3, 4, 5, 6],
        2 => [2, 3, 4, 1, 5, 6],
        3 => [3, 4, 1, 2, 5, 6],
        4 => [4, 1, 2, 3, 5, 6],
        5 => [1, 3, 2, 4, 5, 6],
        6 => [2, 4, 1, 3, 5, 6],
    ];

    $allocated_hours = [];
    foreach ($subjects as $sub) $allocated_hours[$sub['sub_id']] = 0;

    for ($round = 0; $round < 6; $round++) {
        foreach ($group_theory_subs as $grp => $sub_list) {
            $rotated_subs = $sub_list;
            $offset = ($round + $seed) % count($rotated_subs);
            $rotated_subs = array_merge(array_slice($rotated_subs, $offset), array_slice($rotated_subs, 0, $offset));
            
            foreach ($rotated_subs as $sub) {
                $sid = intval($sub['sub_id']);
                $needed = intval($sub['sub_lechrsprday']);
                if ($allocated_hours[$sid] >= $needed) continue;
                $tid = intval($sub['instructor']);
                $dept_id = intval($sub['dept_id']);

                $cand_days = $days;
                usort($cand_days, function($a, $b) use ($grp, $group_busy, $sid, $group_day_subs) {
                    $on_a = isset($group_day_subs[$grp][$a][$sid]) ? $group_day_subs[$grp][$a][$sid] : 0;
                    $on_b = isset($group_day_subs[$grp][$b][$sid]) ? $group_day_subs[$grp][$b][$sid] : 0;
                    if ($on_a !== $on_b) return $on_a - $on_b;
                    $ca = isset($group_busy[$grp][$a]) ? count($group_busy[$grp][$a]) : 0;
                    $cb = isset($group_busy[$grp][$b]) ? count($group_busy[$grp][$b]) : 0;
                    return $ca - $cb;
                });

                foreach ($cand_days as $d) {
                    if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 1) continue;
                    $slot_order = $day_slot_rotations[$d];
                    $s_offset = ($sid + $d + $seed) % count($slot_order);
                    $slot_order = array_merge(array_slice($slot_order, $s_offset), array_slice($slot_order, 0, $s_offset));
                    
                    $placed = false;
                    foreach ($slot_order as $slot) {
                        if (isset($group_busy[$grp][$d][$slot])) continue;
                        if (!isTeacherTheoryGapOk($tid, $d, $slot, $teacher_theory, $teacher_busy)) continue;
                        if (!isTeacherConsecutiveOk($tid, $d, $slot, $teacher_busy)) continue;
                        if (isStudentBackToBackSubject($grp, $d, $slot, $sid, $group_busy)) continue;
                        foreach ($lecRooms as $lr) {
                            $rid = intval($lr['room_id']);
                            if (isSlotAvailable($tid, $rid, $grp, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                                markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, $teacher_busy, $room_busy, $group_busy, $group_day_subs, true, $teacher_theory);
                                $allocated_hours[$sid]++;
                                $schedule_entries[] = 1;
                                $placed = true;
                                break 2;
                            }
                        }
                    }
                    if ($placed) break;
                }
            }
        }
    }

    // Pass 2
    foreach ($group_theory_subs as $grp => $sub_list) {
        foreach ($sub_list as $sub) {
            $sid = intval($sub['sub_id']);
            $needed = intval($sub['sub_lechrsprday']);
            $tid = intval($sub['instructor']);
            while ($allocated_hours[$sid] < $needed) {
                $placed = false;
                foreach ($days as $d) {
                    if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 2) continue;
                    foreach ([5, 6, 1, 2, 3, 4] as $slot) {
                        if (isset($group_busy[$grp][$d][$slot])) continue;
                        if (!isTeacherTheoryGapOk($tid, $d, $slot, $teacher_theory, $teacher_busy)) continue;
                        if (!isTeacherConsecutiveOk($tid, $d, $slot, $teacher_busy)) continue;
                        if (isStudentBackToBackSubject($grp, $d, $slot, $sid, $group_busy)) continue;
                        $too_close = false;
                        for ($check_s = max(1, $slot - 2); $check_s <= min(6, $slot + 2); $check_s++) {
                            if (isset($group_busy[$grp][$d][$check_s]) && $group_busy[$grp][$d][$check_s] == $sid) {
                                $too_close = true; break;
                            }
                        }
                        if ($too_close) continue;
                        foreach ($lecRooms as $lr) {
                            $rid = intval($lr['room_id']);
                            if (isSlotAvailable($tid, $rid, $grp, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                                markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, $teacher_busy, $room_busy, $group_busy, $group_day_subs, true, $teacher_theory);
                                $allocated_hours[$sid]++;
                                $schedule_entries[] = 1;
                                $placed = true;
                                break 3;
                            }
                        }
                    }
                }
                if (!$placed) {
                    // Fallback
                    $actual_teaching = [];
                    foreach ($days as $d) {
                        if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 2) continue;
                        foreach ([5, 6, 1, 2, 3, 4] as $slot) {
                            if (isset($group_busy[$grp][$d][$slot])) continue;
                            if (!isTeacherTheoryGapOk($tid, $d, $slot, $teacher_theory, $teacher_busy)) continue;
                            if (isStudentBackToBackSubject($grp, $d, $slot, $sid, $group_busy)) continue;
                            foreach ($lecRooms as $lr) {
                                $rid = intval($lr['room_id']);
                                if (isSlotAvailable($tid, $rid, $grp, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                                    markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, $teacher_busy, $room_busy, $group_busy, $group_day_subs, true, $teacher_theory);
                                    $allocated_hours[$sid]++;
                                    $schedule_entries[] = 1;
                                    $placed = true;
                                    break 3;
                                }
                            }
                        }
                    }
                    if (!$placed) break;
                }
            }
        }
    }

    $c = count($schedule_entries);
    if ($c >= 142) {
        echo "Exact Seed $seed => $c / 143\n";
    }
}
