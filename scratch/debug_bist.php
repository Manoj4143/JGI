<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../includes/ensure_rooms.php';
require_once __DIR__ . '/../includes/cohort_helpers.php';

// Let's run the exact placement loop for BIST755B with debug prints!
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

// ... we will run Steps A, B, C exactly as in generate_tt ...
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
                    $days_assigned++;
                    break 2;
                }
            }
        }
    }
}
// Step C: NSS
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
                    break 3;
                }
            }
        }
    }
}

// Function
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
            
            if ($sub['sub_code'] === 'BIST755B' && $d == 4) {
                echo "DEBUG: BIST755B on Day 4, Attempt $attempt:\n";
                echo "  Group busy slots: " . implode(',', array_keys(isset($group_busy[$group][$d]) ? $group_busy[$group][$d] : [])) . "\n";
                echo "  Strictly contiguous slots order: " . implode(',', $slots) . "\n";
            }
            
            foreach ($slots as $slot) {
                if ($allocated >= $needed_hours) break;
                if ($attempt <= 3 && !isTeacherConsecutiveOk($teacher, $d, $slot, $teacher_busy)) {
                    if ($sub['sub_code'] === 'BIST755B' && $d == 4) {
                        echo "  Slot $slot rejected for Teacher $teacher: !isTeacherConsecutiveOk\n";
                    }
                    continue;
                }
                
                foreach ($lecRooms as $lr) {
                    $rid = intval($lr['room_id']);
                    if (isSlotAvailable($teacher, $rid, $group, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                        markSlotBusy($teacher, $rid, $group, $d, $slot, $teacher_busy, $room_busy, $group_busy);
                        $subject_days[$sub_id][$d] = $current_on_day + 1;
                        if ($sub['sub_code'] === 'BIST755B') {
                            echo "  ==> Placed BIST755B at Day $d Slot $slot in Room $rid\n";
                        }
                        $allocated++;
                        break 2;
                    }
                }
            }
        }
    }
}
