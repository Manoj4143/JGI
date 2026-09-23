<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../includes/ensure_rooms.php';
require_once __DIR__ . '/../includes/cohort_helpers.php';

// Copy the full generation from debug_bist.php, but add intra-day compaction!
$labRooms = []; $lecRooms = [];
$res = mysqli_query($conn, "SELECT * FROM room ORDER BY room_id ASC");
while ($r = mysqli_fetch_assoc($res)) {
    if (intval($r['room_isNKN']) === 1) $labRooms[] = $r; else $lecRooms[] = $r;
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
        if (preg_match('/\d+/', $s['sub_code'], $m)) $yr = $m[0][0]; else $yr = 1;
        $s['group_id'] = intval($s['dept_id'] . $yr);
    }
    $subjects[] = $s;
}

$days = [1, 2, 3, 4, 5, 6];
$morning_blocks = [[1, 2], [3, 4]]; 
$afternoon_blocks = [[5, 6]];        
$teacher_busy = []; $room_busy = []; $group_busy = []; $subject_days = [];
$schedule_entries = [];

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
function markSlotBusy($teacher, $room, $group, $day, $slot, &$teacher_busy, &$room_busy, &$group_busy) {
    $teacher_busy[$teacher][$day][$slot] = true;
    $room_busy[$room][$day][$slot] = true;
    $group_busy[$group][$day][$slot] = true;
}

// Step A: LAB
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
                markSlotBusy($teacher, $available_labs[0]['room_id'], $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                markSlotBusy($teacher, $available_labs[0]['room_id'], $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
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
            if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy) || !isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
            foreach ($lecRooms as $lr) {
                $rid = intval($lr['room_id']);
                if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                    markSlotBusy($teacher, $rid, $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                    markSlotBusy($teacher, $rid, $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
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
                    markSlotBusy($teacher, $rid, $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                    markSlotBusy($teacher, $rid, $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MINI_PROJECT'];
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => 'MINI_PROJECT'];
                    $days_assigned++;
                    break 2;
                }
            }
        }
    }
}

// Step C: NSS / Community Project
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
                    markSlotBusy($teacher, $rid, $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                    markSlotBusy($teacher, $rid, $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s1, 'time_e_id' => $s1 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => $sub['subject_type']];
                    $schedule_entries[] = ['room_id' => $rid, 'sub_id' => $sub_id, 'teacher_id' => $teacher, 'time_s_id' => $s2, 'time_e_id' => $s2 + 1, 'day_id' => $d, 'dept_id' => $dept_id, 'group_name' => $group, 'subject_type' => $sub['subject_type']];
                    break 3;
                }
            }
        }
    }
}

// Contiguous Ordering Function
function getStrictlyContiguousSlots($group, $day, &$group_busy) {
    $busy = isset($group_busy[$group][$day]) ? $group_busy[$group][$day] : [];
    $has_aft = (isset($busy[5]) || isset($busy[6]));
    $has_morn = (isset($busy[1]) || isset($busy[2]) || isset($busy[3]) || isset($busy[4]));
    $order = [];
    if (!$has_aft && !$has_morn) {
        $order = [1, 2, 3, 4, 5, 6];
    } elseif ($has_aft && !$has_morn) {
        $order = [4, 3, 2, 1];
    } elseif ($has_morn && !$has_aft) {
        if (isset($busy[1]) && !isset($busy[2])) $order[] = 2;
        if (isset($busy[2]) && !isset($busy[3])) $order[] = 3;
        if (isset($busy[3]) && !isset($busy[4])) $order[] = 4;
        if (isset($busy[4]) && !isset($busy[5])) $order[] = 5;
        if (isset($busy[5]) && !isset($busy[6])) $order[] = 6;
        if (isset($busy[3]) && !isset($busy[2])) $order[] = 2;
        if (isset($busy[2]) && !isset($busy[1])) $order[] = 1;
        for ($s = 1; $s <= 6; $s++) {
            if (!in_array($s, $order)) $order[] = $s;
        }
    } else {
        // Bridge internal holes first
        for ($s = 1; $s <= 6; $s++) {
            if (!isset($busy[$s])) {
                $adj_left = ($s > 1 && isset($busy[$s - 1]));
                $adj_right = ($s < 7 && isset($busy[$s + 1]));
                if ($adj_left && $adj_right) array_unshift($order, $s);
                elseif ($adj_left || $adj_right) $order[] = $s;
            }
        }
        for ($s = 1; $s <= 6; $s++) {
            if (!in_array($s, $order)) $order[] = $s;
        }
    }
    $final = [];
    foreach ($order as $s) {
        if (!isset($busy[$s])) $final[] = $s;
    }
    return $final;
}

$single_hr_subjects = [];
foreach ($subjects as $sub) {
    if (in_array($sub['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
        $single_hr_subjects[] = $sub;
    }
}

foreach ($single_hr_subjects as $sub) {
    $group = strval($sub['group_id']);
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    $dept_id = intval($sub['dept_id']);
    $needed_hours = intval($sub['sub_lechrsprday']);
    $allocated = 0;
    
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $sorted_days = $days;
        usort($sorted_days, function($a, $b) use ($group, $group_busy) {
            $ca = isset($group_busy[$group][$a]) ? count($group_busy[$group][$a]) : 0;
            $cb = isset($group_busy[$group][$b]) ? count($group_busy[$group][$b]) : 0;
            return $ca - $cb;
        });
        
        foreach ($sorted_days as $d) {
            if ($allocated >= $needed_hours) break;
            $current_on_day = isset($subject_days[$sub_id][$d]) ? $subject_days[$sub_id][$d] : 0;
            if ($attempt == 1 && $current_on_day >= 1) continue;
            if ($attempt == 2 && $current_on_day >= 2) continue;
            
            $slots = getStrictlyContiguousSlots($group, $d, $group_busy);
            
            foreach ($slots as $slot) {
                if ($allocated >= $needed_hours) break;
                if ($attempt <= 3 && !isTeacherConsecutiveOk($teacher, $d, $slot, $teacher_busy)) continue;
                
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (isSlotAvailable($teacher, $rid, $group, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                        markSlotBusy($teacher, $rid, $group, $d, $slot, $teacher_busy, $room_busy, $group_busy);
                        $subject_days[$sub_id][$d] = $current_on_day + 1;
                        $schedule_entries[] = [
                            'room_id' => $rid,
                            'sub_id' => $sub_id,
                            'teacher_id' => $teacher,
                            'time_s_id' => $slot,
                            'time_e_id' => $slot + 1,
                            'day_id' => $d,
                            'dept_id' => $dept_id,
                            'group_name' => $group,
                            'subject_type' => $sub['subject_type']
                        ];
                        $allocated++;
                        break 2;
                    }
                }
            }
        }
    }
}

// Intra-day Compaction Pass: ONLY within the SAME day ($d == old_d)!
// If on day d, slot s is empty, but there is a class at slot > s with a gap, try any available lecture room!
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
            // Find lowest empty slot before max_s
            for ($s = 1; $s < $max_s; $s++) {
                if (!isset($g_slots[$s])) {
                    // Try to move a class from a later slot on the SAME day into slot $s
                    for ($later = $max_s; $later > $s; $later--) {
                        if (isset($g_slots[$later])) {
                            $idx = $g_slots[$later];
                            $entry = $schedule_entries[$idx];
                            if (in_array($entry['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
                                $tid = $entry['teacher_id'];
                                $old_rid = $entry['room_id'];
                                
                                unset($teacher_busy[$tid][$d][$later]);
                                unset($room_busy[$old_rid][$d][$later]);
                                unset($group_busy[$grp][$d][$later]);
                                
                                // Check if teacher and any room is available at slot s
                                $placed_new = false;
                                if (isTeacherConsecutiveOk($tid, $d, $s, $teacher_busy) && !isset($teacher_busy[$tid][$d][$s]) && !isset($group_busy[$grp][$d][$s])) {
                                    foreach ($lecRooms as $lr) {
                                        $nrid = intval($lr['room_id']);
                                        if (!isset($room_busy[$nrid][$d][$s])) {
                                            markSlotBusy($tid, $nrid, $grp, $d, $s, $teacher_busy, $room_busy, $group_busy);
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
                                    $room_busy[$old_rid][$d][$later] = true;
                                    $group_busy[$grp][$d][$later] = true;
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

echo "Total entries: " . count($schedule_entries) . "\n";
// Inspect grid
$groups = ['91', '75', '85', '107', '117'];
foreach ($groups as $grp) {
    echo "\n=== GROUP $grp ===\n";
    $grid = [];
    foreach ($schedule_entries as $entry) {
        $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
        if ($g_clean === $grp) $grid[$entry['day_id']][$entry['time_s_id']][] = $entry['sub_id'];
    }
    $days_name = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
    foreach ($days_name as $d => $dname) {
        $row = [];
        for ($s = 1; $s <= 7; $s++) {
            $row[] = isset($grid[$d][$s]) ? "[X]" : "[ ]";
        }
        echo sprintf("%-4s: %s\n", $dname, implode(' ', $row));
    }
}
