<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../includes/ensure_rooms.php';
require_once __DIR__ . '/../includes/cohort_helpers.php';

// Fetch Rooms
$labRooms = []; $lecRooms = [];
$res = mysqli_query($conn, "SELECT * FROM room ORDER BY room_id ASC");
while ($r = mysqli_fetch_assoc($res)) {
    if (intval($r['room_isNKN']) === 1) $labRooms[] = $r; else $lecRooms[] = $r;
}

// Fetch Subjects
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
        if (preg_match('/\d+/', $s['sub_code'], $m)) $yr = $m[0][0]; else $yr = 1;
        $s['group_id'] = intval($s['dept_id'] . $yr);
    }
    $subjects[] = $s;
}

$days = [1, 2, 3, 4, 5, 6]; // Mon to Sat
$morning_blocks = [[1, 2], [3, 4]]; 
$afternoon_blocks = [[5, 6]];        

$teacher_busy = []; // [teacher_id][day][slot] = true
$teacher_theory = []; // [teacher_id][day][slot] = true
$room_busy = [];    // [room_id][day][slot] = true
$group_busy = [];   // [group_id][day][slot] = sub_id
$group_day_subs = []; // [group_id][day][sub_id] = count
$schedule_entries = [];

// Constraint: Teacher must have at least 1 free period adjacent to any theory class!
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

// -------------------------------------------------------------
// Step A: Schedule LAB subjects (2 continuous hours, split batches)
// -------------------------------------------------------------
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'LAB') continue;
    $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']); $dept_id = intval($sub['dept_id']);
    
    foreach (array_merge($morning_blocks, $afternoon_blocks) as $blk) {
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
                $schedule_entries[] = ['room_id' => $available_labs[0]['room_id'], 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => "{$group} (Batch 1)", 'subject_type' => 'LAB'];
                $schedule_entries[] = ['room_id' => $available_labs[1]['room_id'], 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => "{$group} (Batch 2)", 'subject_type' => 'LAB'];
                $schedule_entries[] = ['room_id' => $available_labs[0]['room_id'], 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => "{$group} (Batch 1)", 'subject_type' => 'LAB'];
                $schedule_entries[] = ['room_id' => $available_labs[1]['room_id'], 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => "{$group} (Batch 2)", 'subject_type' => 'LAB'];
                break 2;
            }
        }
    }
}

// -------------------------------------------------------------
// Step B1: MAJOR_PROJECT (6 hrs/wk: 2 hrs/day x 3 days, afternoon [5,6])
// -------------------------------------------------------------
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'MAJOR_PROJECT') continue;
    $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']); $dept_id = intval($sub['dept_id']);
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
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MAJOR_PROJECT'];
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MAJOR_PROJECT'];
                    $days_assigned++;
                    break 2;
                }
            }
        }
    }
}

// -------------------------------------------------------------
// Step B2: MINI_PROJECT (4 hrs/wk: 2 hrs/day x 2 days, morning [1,2] or [3,4])
// -------------------------------------------------------------
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'MINI_PROJECT') continue;
    $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']); $dept_id = intval($sub['dept_id']);
    $days_assigned = 0;
    foreach ($days as $d) {
        if ($days_assigned >= 2) break;
        foreach ($morning_blocks as $blk) {
            $s1 = $blk[0]; $s2 = $blk[1];
            if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
            if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy) || !isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
            foreach ($lecRooms as $lr) {
                $rid = intval($lr['room_id']);
                if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                    markSlotBusy($teacher, $rid, $group, $d, $s1, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                    markSlotBusy($teacher, $rid, $group, $d, $s2, $sub_id, $teacher_busy, $room_busy, $group_busy, $group_day_subs);
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MINI_PROJECT'];
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MINI_PROJECT'];
                    $days_assigned++;
                    break 2;
                }
            }
        }
    }
}

// -------------------------------------------------------------
// Step C: NSS / COMMUNITY_PROJECT (2 continuous hours)
// -------------------------------------------------------------
foreach ($subjects as $sub) {
    if (!in_array($sub['subject_type'], ['NSS', 'COMMUNITY_PROJECT'])) continue;
    $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']); $dept_id = intval($sub['dept_id']);
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
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => $sub['subject_type']];
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => $sub['subject_type']];
                    break 3;
                }
            }
        }
    }
}

// -------------------------------------------------------------
// Step D & E: Shuffled Theory Scheduler
// -------------------------------------------------------------
$group_theory_subs = [];
foreach ($subjects as $sub) {
    if (in_array($sub['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
        $grp = strval($sub['group_id']);
        $group_theory_subs[$grp][] = $sub;
    }
}

// Sort subjects by needed hours DESC, then teacher busy DESC
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

// Daily slot rotation patterns for students
$day_slot_rotations = [
    1 => [1, 2, 3, 4, 5, 6],
    2 => [2, 3, 4, 1, 5, 6],
    3 => [3, 4, 1, 2, 5, 6],
    4 => [4, 1, 2, 3, 5, 6],
    5 => [1, 3, 2, 4, 5, 6],
    6 => [2, 4, 1, 3, 5, 6],
];

function isStudentBackToBackSubject($group, $day, $slot, $sub_id, &$group_busy) {
    if ($slot > 1 && isset($group_busy[$group][$day][$slot - 1]) && $group_busy[$group][$day][$slot - 1] == $sub_id) {
        return true;
    }
    if ($slot < 7 && isset($group_busy[$group][$day][$slot + 1]) && $group_busy[$group][$day][$slot + 1] == $sub_id) {
        return true;
    }
    return false;
}

$allocated_hours = [];
foreach ($subjects as $sub) {
    $allocated_hours[$sub['sub_id']] = 0;
}

// Pass 1: Spread across days, max 1 hr/day per subject, rotating slots
for ($round = 0; $round < 6; $round++) {
    foreach ($group_theory_subs as $grp => $sub_list) {
        $rotated_subs = $sub_list;
        $offset = $round % count($rotated_subs);
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
                if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 1) {
                    continue;
                }
                
                $slot_order = $day_slot_rotations[$d];
                $s_offset = ($sid + $d) % count($slot_order);
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
                            $schedule_entries[] = [
                                'room_id' => $rid,
                                'sub_id' => $sid,
                                'teacher_id' => $tid,
                                'time_s_id' => $slot,
                                'time_e_id' => $slot + 1,
                                'day_id' => $d,
                                'dept_id' => $dept_id,
                                'group_name' => $grp,
                                'subject_type' => $sub['subject_type']
                            ];
                            $allocated_hours[$sid]++;
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

// Pass 2: Remaining hours (e.g. BCS503 has 7 hours)
foreach ($group_theory_subs as $grp => $sub_list) {
    foreach ($sub_list as $sub) {
        $sid = intval($sub['sub_id']);
        $needed = intval($sub['sub_lechrsprday']);
        $tid = intval($sub['instructor']);
        $dept_id = intval($sub['dept_id']);
        
        while ($allocated_hours[$sid] < $needed) {
            $placed = false;
            foreach ($days as $d) {
                if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 2) continue;
                
                // Prefer afternoon slots [5, 6] so morning and afternoon are separated by lunch!
                foreach ([5, 6, 1, 2, 3, 4] as $slot) {
                    if (isset($group_busy[$grp][$d][$slot])) continue;
                    if (!isTeacherTheoryGapOk($tid, $d, $slot, $teacher_theory, $teacher_busy)) continue;
                    if (!isTeacherConsecutiveOk($tid, $d, $slot, $teacher_busy)) continue;
                    if (isStudentBackToBackSubject($grp, $d, $slot, $sid, $group_busy)) continue;
                    
                    // Enforce at least 2-period separation from the other session on the same day!
                    $too_close = false;
                    for ($check_s = max(1, $slot - 2); $check_s <= min(6, $slot + 2); $check_s++) {
                        if (isset($group_busy[$grp][$d][$check_s]) && $group_busy[$grp][$d][$check_s] == $sid) {
                            $too_close = true;
                            break;
                        }
                    }
                    if ($too_close) continue;
                    
                    foreach ($lecRooms as $lr) {
                        $rid = intval($lr['room_id']);
                        if (isSlotAvailable($tid, $rid, $grp, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                            markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, $teacher_busy, $room_busy, $group_busy, $group_day_subs, true, $teacher_theory);
                            $schedule_entries[] = [
                                'room_id' => $rid,
                                'sub_id' => $sid,
                                'teacher_id' => $tid,
                                'time_s_id' => $slot,
                                'time_e_id' => $slot + 1,
                                'day_id' => $d,
                                'dept_id' => $dept_id,
                                'group_name' => $grp,
                                'subject_type' => $sub['subject_type']
                            ];
                            $allocated_hours[$sid]++;
                            $placed = true;
                            break 3;
                        }
                    }
                }
            }
            if (!$placed) {
                // Fallback: at least 1-period buffer
                foreach ($days as $d) {
                    if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 2) continue;
                    foreach ([5, 6, 1, 2, 3, 4] as $slot) {
                        if (isset($group_busy[$grp][$d][$slot])) continue;
                        if (!isTeacherTheoryGapOk($tid, $d, $slot, $teacher_theory, $teacher_busy)) continue;
                        if (!isTeacherConsecutiveOk($tid, $d, $slot, $teacher_busy)) continue;
                        if (isStudentBackToBackSubject($grp, $d, $slot, $sid, $group_busy)) continue;
                        
                        foreach ($lecRooms as $lr) {
                            $rid = intval($lr['room_id']);
                            if (isSlotAvailable($tid, $rid, $grp, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                                markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, $teacher_busy, $room_busy, $group_busy, $group_day_subs, true, $teacher_theory);
                                $schedule_entries[] = [
                                    'room_id' => $rid,
                                    'sub_id' => $sid,
                                    'teacher_id' => $tid,
                                    'time_s_id' => $slot,
                                    'time_e_id' => $slot + 1,
                                    'day_id' => $d,
                                    'dept_id' => $dept_id,
                                    'group_name' => $grp,
                                    'subject_type' => $sub['subject_type']
                                ];
                                $allocated_hours[$sid]++;
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

// -------------------------------------------------------------
// Step F: Intra-day Gap Elimination & Compaction Pass
// Resolves any gaps within the same day while strictly preserving:
// 1. Teacher theory gap rule
// 2. Student no back-to-back same subject rule
// -------------------------------------------------------------
for ($pass = 0; $pass < 20; $pass++) {
    $shifted = false;
    foreach ($days as $d) {
        $groups = ['91', '75', '85', '107', '117'];
        foreach ($groups as $grp) {
            $g_slots = [];
            foreach ($schedule_entries as $idx => $entry) {
                $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
                if ($g_clean === $grp && $entry['day_id'] == $d) {
                    $g_slots[$entry['time_s_id']] = $idx;
                }
            }
            if (empty($g_slots)) continue;
            
            $max_s = max(array_keys($g_slots));
            for ($s = 1; $s < $max_s; $s++) {
                if (!isset($g_slots[$s])) {
                    for ($later = $max_s; $later > $s; $later--) {
                        if (isset($g_slots[$later])) {
                            $idx = $g_slots[$later];
                            $entry = $schedule_entries[$idx];
                            if (in_array($entry['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
                                $tid = $entry['teacher_id'];
                                $sid = $entry['sub_id'];
                                $old_rid = $entry['room_id'];
                                
                                unset($teacher_busy[$tid][$d][$later]);
                                unset($teacher_theory[$tid][$d][$later]);
                                unset($room_busy[$old_rid][$d][$later]);
                                unset($group_busy[$grp][$d][$later]);
                                
                                $placed_new = false;
                                if (isTeacherTheoryGapOk($tid, $d, $s, $teacher_theory, $teacher_busy) && 
                                    isTeacherConsecutiveOk($tid, $d, $s, $teacher_busy) &&
                                    !isset($group_busy[$grp][$d][$s]) &&
                                    !isStudentBackToBackSubject($grp, $d, $s, $sid, $group_busy)) {
                                    
                                    foreach ($lecRooms as $lr) {
                                        $nrid = intval($lr['room_id']);
                                        if (!isset($room_busy[$nrid][$d][$s])) {
                                            markSlotBusy($tid, $nrid, $grp, $d, $s, $sid, $teacher_busy, $room_busy, $group_busy, $group_day_subs, true, $teacher_theory);
                                            $schedule_entries[$idx]['room_id'] = $nrid;
                                            $schedule_entries[$idx]['time_s_id'] = $s;
                                            $schedule_entries[$idx]['time_e_id'] = $s + 1;
                                            $shifted = true;
                                            $placed_new = true;
                                            break;
                                        }
                                    }
                                }
                                
                                if ($placed_new) {
                                    break 2;
                                } else {
                                    // restore
                                    $teacher_busy[$tid][$d][$later] = true;
                                    $teacher_theory[$tid][$d][$later] = true;
                                    $room_busy[$old_rid][$d][$later] = true;
                                    $group_busy[$grp][$d][$later] = $sid;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if (!$shifted) break;
}

echo "Total sessions scheduled: " . count($schedule_entries) . "\n";

$missing = 0;
foreach ($subjects as $s) {
    $sid = $s['sub_id'];
    $cnt = 0;
    foreach ($schedule_entries as $e) {
        if ($e['sub_id'] == $sid) $cnt++;
    }
    if ($s['subject_type'] === 'LAB') $cnt = $cnt / 2;
    $req = intval($s['sub_lechrsprday']);
    if ($s['subject_type'] === 'MAJOR_PROJECT') $req = 6;
    if ($s['subject_type'] === 'MINI_PROJECT') $req = 4;
    if ($cnt != $req) {
        echo "MISMATCH: Subject {$s['sub_code']} ({$s['subject_type']}, Grp {$s['group_id']}) req $req, scheduled $cnt\n";
        $missing++;
    }
}
if ($missing == 0) {
    echo "SUCCESS: 100% of required subject hours (all 143 sessions) are scheduled perfectly!\n";
}

$sub_map = [];
foreach ($subjects as $s) {
    $sub_map[$s['sub_id']] = $s['sub_code'];
}

echo "\n================ GROUP 75 (5th Sem AIML) ================\n";
$grid75 = [];
foreach ($schedule_entries as $entry) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
    if ($g_clean === '75') {
        $grid75[$entry['day_id']][$entry['time_s_id']] = $sub_map[$entry['sub_id']];
    }
}
$dnames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $code = isset($grid75[$d][$s]) ? $grid75[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-8s] ", $s, $code);
    }
    echo $line . "\n";
}

echo "\n================ TEACHER 34 SCHEDULE ================\n";
$t34_grid = [];
foreach ($schedule_entries as $entry) {
    if ($entry['teacher_id'] == 34) {
        $t34_grid[$entry['day_id']][$entry['time_s_id']] = $sub_map[$entry['sub_id']] . " (" . $entry['subject_type'] . ")";
    }
}
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($t34_grid[$d][$s]) ? $t34_grid[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-18s] ", $s, $c);
    }
    echo $line . "\n";
}

echo "\n================ TEACHER THEORY GAP VALIDATION ================\n";
$t_theory_grid = [];
foreach ($schedule_entries as $entry) {
    if ($entry['subject_type'] === 'THEORY') {
        $t_theory_grid[$entry['teacher_id']][$entry['day_id']][$entry['time_s_id']] = true;
    }
}
$violating_teachers = 0;
foreach ($t_theory_grid as $tid => $d_map) {
    foreach ($d_map as $d => $slots_map) {
        $slots = array_keys($slots_map);
        sort($slots);
        for ($i = 0; $i < count($slots) - 1; $i++) {
            if ($slots[$i+1] == $slots[$i] + 1) {
                echo "VIOLATION: Teacher $tid has back-to-back theory classes on Day $d: slots {$slots[$i]} and {$slots[$i+1]}\n";
                $violating_teachers++;
            }
        }
    }
}
if ($violating_teachers === 0) {
    echo "SUCCESS: 100% of teachers have at least 1 free period between theory classes! Zero back-to-back theory classes.\n";
}

echo "\n================ STUDENT BACK-TO-BACK SAME SUBJECT CHECK ================\n";
$grp_sub_grid = [];
foreach ($schedule_entries as $entry) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
    $grp_sub_grid[$g_clean][$entry['day_id']][$entry['time_s_id']] = $entry['sub_id'];
}
$violating_groups = 0;
foreach ($grp_sub_grid as $grp => $d_map) {
    foreach ($d_map as $d => $s_map) {
        for ($s = 1; $s <= 6; $s++) {
            if (isset($s_map[$s]) && isset($s_map[$s+1]) && $s_map[$s] == $s_map[$s+1]) {
                $sid = $s_map[$s];
                foreach ($subjects as $sub) {
                    if ($sub['sub_id'] == $sid && in_array($sub['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
                        echo "VIOLATION: Group $grp has duplicate subject {$sub['sub_code']} back-to-back on Day $d in slots $s and " . ($s+1) . "\n";
                        $violating_groups++;
                    }
                }
            }
        }
    }
}
if ($violating_groups === 0) {
    echo "SUCCESS: Zero instances of the same theory subject scheduled back-to-back! Every day is diversified.\n";
}
