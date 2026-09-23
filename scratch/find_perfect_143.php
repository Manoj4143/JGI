<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../includes/ensure_rooms.php';
require_once __DIR__ . '/../includes/cohort_helpers.php';

function run_scheduler($seed = 0) {
    global $conn;
    
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

    // Load faculty unavailability from faculty_timing
    $res_ft = mysqli_query($conn, "SELECT teacher_id, day_id, slot_id, is_available FROM faculty_timing WHERE is_available = 0");
    if ($res_ft) {
        while ($ft = mysqli_fetch_assoc($res_ft)) {
            $teacher_busy[intval($ft['teacher_id'])][intval($ft['day_id'])][intval($ft['slot_id'])] = true;
        }
    }


    // Constraint: Teacher must have at least 1 free period adjacent to any theory class!
    $isTeacherTheoryGapOk = function($teacher, $day, $slot) use (&$teacher_theory, &$teacher_busy) {
        if (isset($teacher_busy[$teacher][$day][$slot])) return false;
        if ($slot > 1 && isset($teacher_theory[$teacher][$day][$slot - 1])) return false;
        if ($slot < 7 && isset($teacher_theory[$teacher][$day][$slot + 1])) return false;
        return true;
    };

    $isTeacherConsecutiveOk = function($teacher, $day, $slot) use (&$teacher_busy) {
        if ($slot > 2 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot - 2])) return false;
        if ($slot > 1 && $slot < 7 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot + 1])) return false;
        if ($slot < 6 && isset($teacher_busy[$teacher][$day][$slot + 1]) && isset($teacher_busy[$teacher][$day][$slot + 2])) return false;
        return true;
    };

    $isSlotAvailable = function($teacher, $room, $group, $day, $slot) use (&$teacher_busy, &$room_busy, &$group_busy) {
        if (isset($teacher_busy[$teacher][$day][$slot])) return false;
        if (isset($room_busy[$room][$day][$slot])) return false;
        if (isset($group_busy[$group][$day][$slot])) return false;
        return true;
    };

    $markSlotBusy = function($teacher, $room, $group, $day, $slot, $sub_id, $is_theory = false) use (&$teacher_busy, &$room_busy, &$group_busy, &$group_day_subs, &$teacher_theory) {
        $teacher_busy[$teacher][$day][$slot] = true;
        $room_busy[$room][$day][$slot] = true;
        $group_busy[$group][$day][$slot] = $sub_id;
        if (!isset($group_day_subs[$group][$day][$sub_id])) {
            $group_day_subs[$group][$day][$sub_id] = 0;
        }
        $group_day_subs[$group][$day][$sub_id]++;
        if ($is_theory) {
            $teacher_theory[$teacher][$day][$slot] = true;
        }
    };

    $isStudentBackToBackSubject = function($group, $day, $slot, $sub_id) use (&$group_busy) {
        if ($slot > 1 && isset($group_busy[$group][$day][$slot - 1]) && $group_busy[$group][$day][$slot - 1] == $sub_id) return true;
        if ($slot < 7 && isset($group_busy[$group][$day][$slot + 1]) && $group_busy[$group][$day][$slot + 1] == $sub_id) return true;
        return false;
    };

    // Step A: LAB
    foreach ($subjects as $sub) {
        if ($sub['subject_type'] !== 'LAB') continue;
        $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']); $dept_id = intval($sub['dept_id']);
        
        foreach (array_merge($morning_blocks, $afternoon_blocks) as $blk) {
            $s1 = $blk[0]; $s2 = $blk[1];
            foreach ($days as $d) {
                if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
                if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
                if (!$isTeacherConsecutiveOk($teacher, $d, $s1) || !$isTeacherConsecutiveOk($teacher, $d, $s2)) continue;
                
                $available_labs = [];
                foreach ($labRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) $available_labs[] = $lr;
                }
                if (count($available_labs) >= 2) {
                    $markSlotBusy($teacher, $available_labs[0]['room_id'], $group, $d, $s1, $sub_id);
                    $markSlotBusy($teacher, $available_labs[0]['room_id'], $group, $d, $s2, $sub_id);
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

    // Step B1: MAJOR_PROJECT
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
                if (!$isTeacherConsecutiveOk($teacher, $d, $s1) || !$isTeacherConsecutiveOk($teacher, $d, $s2)) continue;
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                        $markSlotBusy($teacher, $rid, $group, $d, $s1, $sub_id);
                        $markSlotBusy($teacher, $rid, $group, $d, $s2, $sub_id);
                        $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MAJOR_PROJECT'];
                        $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MAJOR_PROJECT'];
                        $days_assigned++;
                        break 2;
                    }
                }
            }
        }
    }

    // Step B2: MINI_PROJECT
    // Allow both morning blocks [[3, 4], [1, 2]] so teacher doesn't block early slots if they also teach theory
    foreach ($subjects as $sub) {
        if ($sub['subject_type'] !== 'MINI_PROJECT') continue;
        $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']); $dept_id = intval($sub['dept_id']);
        $days_assigned = 0;
        // If seed % 2 == 1, try [3, 4] first
        $try_blocks = ($seed % 2 == 1) ? [[3, 4], [1, 2]] : [[1, 2], [3, 4]];
        foreach ($days as $d) {
            if ($days_assigned >= 2) break;
            foreach ($try_blocks as $blk) {
                $s1 = $blk[0]; $s2 = $blk[1];
                if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
                if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
                if (!$isTeacherConsecutiveOk($teacher, $d, $s1) || !$isTeacherConsecutiveOk($teacher, $d, $s2)) continue;
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                        $markSlotBusy($teacher, $rid, $group, $d, $s1, $sub_id);
                        $markSlotBusy($teacher, $rid, $group, $d, $s2, $sub_id);
                        $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MINI_PROJECT'];
                        $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MINI_PROJECT'];
                        $days_assigned++;
                        break 2;
                    }
                }
            }
        }
    }

    // Step C: NSS / COMMUNITY_PROJECT
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
                if (!$isTeacherConsecutiveOk($teacher, $d, $s1) || !$isTeacherConsecutiveOk($teacher, $d, $s2)) continue;
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                        $markSlotBusy($teacher, $rid, $group, $d, $s1, $sub_id);
                        $markSlotBusy($teacher, $rid, $group, $d, $s2, $sub_id);
                        $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => $sub['subject_type']];
                        $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => $sub['subject_type']];
                        break 3;
                    }
                }
            }
        }
    }

    // Step D & E: Shuffled Theory Scheduler
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

    $day_slot_rotations = [
        1 => [1, 2, 3, 4, 5, 6],
        2 => [2, 3, 4, 1, 5, 6],
        3 => [3, 4, 1, 2, 5, 6],
        4 => [4, 1, 2, 3, 5, 6],
        5 => [1, 3, 2, 4, 5, 6],
        6 => [2, 4, 1, 3, 5, 6],
    ];

    $allocated_hours = [];
    foreach ($subjects as $sub) {
        $allocated_hours[$sub['sub_id']] = 0;
    }

    // Pass 1: Spread across days, max 1 hr/day per subject, rotating slots
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
                    if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 1) {
                        continue;
                    }
                    
                    $slot_order = $day_slot_rotations[$d];
                    $s_offset = ($sid + $d + $seed) % count($slot_order);
                    $slot_order = array_merge(array_slice($slot_order, $s_offset), array_slice($slot_order, 0, $s_offset));
                    
                    $placed = false;
                    foreach ($slot_order as $slot) {
                        if (isset($group_busy[$grp][$d][$slot])) continue;
                        if (!$isTeacherTheoryGapOk($tid, $d, $slot)) continue;
                        if (!$isTeacherConsecutiveOk($tid, $d, $slot)) continue;
                        if ($isStudentBackToBackSubject($grp, $d, $slot, $sid)) continue;
                        
                        foreach ($lecRooms as $lr) {
                            $rid = intval($lr['room_id']);
                            if ($isSlotAvailable($tid, $rid, $grp, $d, $slot)) {
                                $markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, true);
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

    // Pass 2: Remaining hours
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
                    
                    // Try slot orders preferring afternoon [5, 6] then [1, 2, 3, 4]
                    foreach ([5, 6, 1, 2, 3, 4] as $slot) {
                        if (isset($group_busy[$grp][$d][$slot])) continue;
                        if (!$isTeacherTheoryGapOk($tid, $d, $slot)) continue;
                        if (!$isTeacherConsecutiveOk($tid, $d, $slot)) continue;
                        if ($isStudentBackToBackSubject($grp, $d, $slot, $sid)) continue;
                        
                        // Separation check
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
                            if ($isSlotAvailable($tid, $rid, $grp, $d, $slot)) {
                                $markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, true);
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
                    // Fallback: at least 1 slot away (not back to back)
                    foreach ($days as $d) {
                        if (isset($group_day_subs[$grp][$d][$sid]) && $group_day_subs[$grp][$d][$sid] >= 2) continue;
                        foreach ([5, 6, 1, 2, 3, 4] as $slot) {
                            if (isset($group_busy[$grp][$d][$slot])) continue;
                            if (!$isTeacherTheoryGapOk($tid, $d, $slot)) continue;
                            if (!$isTeacherConsecutiveOk($tid, $d, $slot)) continue;
                            if ($isStudentBackToBackSubject($grp, $d, $slot, $sid)) continue;
                            
                            foreach ($lecRooms as $lr) {
                                $rid = intval($lr['room_id']);
                                if ($isSlotAvailable($tid, $rid, $grp, $d, $slot)) {
                                    $markSlotBusy($tid, $rid, $grp, $d, $slot, $sid, true);
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

    // Step F: Intra-day gap compaction preserving all rules
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
                                    if ($isTeacherTheoryGapOk($tid, $d, $s) && 
                                        $isTeacherConsecutiveOk($tid, $d, $s) &&
                                        !isset($group_busy[$grp][$d][$s]) &&
                                        !$isStudentBackToBackSubject($grp, $d, $s, $sid)) {
                                        
                                        foreach ($lecRooms as $lr) {
                                            $nrid = intval($lr['room_id']);
                                            if (!isset($room_busy[$nrid][$d][$s])) {
                                                $markSlotBusy($tid, $nrid, $grp, $d, $s, $sid, true);
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
            $missing += ($req - $cnt);
        }
    }

    $internal_gaps = 0;
    foreach ($group_busy as $grp => $d_map) {
        foreach ($d_map as $d => $s_map) {
            $slots = array_keys($s_map);
            sort($slots);
            if (count($slots) > 1) {
                for ($i = 0; $i < count($slots) - 1; $i++) {
                    if ($slots[$i+1] > $slots[$i] + 1) {
                        if (!($slots[$i] == 4 && $slots[$i+1] == 5)) {
                            $internal_gaps += ($slots[$i+1] - $slots[$i] - 1);
                        }
                    }
                }
            }
        }
    }

    return [
        'seed' => $seed,
        'scheduled' => count($schedule_entries),
        'missing' => $missing,
        'internal_gaps' => $internal_gaps,
        'entries' => $schedule_entries,
        'teacher_theory' => $teacher_theory,
        'group_busy' => $group_busy,
        'subjects' => $subjects
    ];
}

$best_seed = -1;
$best_score = 9999;
for ($s = 0; $s < 100; $s++) {
    $res = run_scheduler($s);
    if ($res['missing'] === 0) {
        echo "Seed $s: scheduled={$res['scheduled']}, missing={$res['missing']}, internal_gaps={$res['internal_gaps']}\n";
        if ($res['internal_gaps'] < $best_score) {
            $best_score = $res['internal_gaps'];
            $best_seed = $s;
            if ($best_score === 0) {
                echo "FOUND PERFECT 0-GAP SEED: $s!\n";
                break;
            }
        }
    }
}
echo "Best Seed: $best_seed with gaps = $best_score\n";

