<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../includes/ensure_rooms.php';
require_once __DIR__ . '/../includes/cohort_helpers.php';

// 1. Fetch Rooms
$labRooms = [];
$lecRooms = [];
$res = mysqli_query($conn, "SELECT * FROM room ORDER BY room_id ASC");
while ($r = mysqli_fetch_assoc($res)) {
    if (intval($r['room_isNKN']) === 1) {
        $labRooms[] = $r;
    } else {
        $lecRooms[] = $r;
    }
}

// 2. Fetch Subjects
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

$days = [1, 2, 3, 4, 5, 6]; // Monday to Saturday
$morning_blocks = [[1, 2], [3, 4]]; 
$afternoon_blocks = [[5, 6]];        
$all_2hr_blocks = array_merge($morning_blocks, $afternoon_blocks);

$teacher_busy = []; // [teacher_id][day][slot]
$room_busy = [];    // [room_id][day][slot]
$group_busy = [];   // [group_id][day][slot]
$subject_days = []; // [sub_id][day] count

// Load faculty unavailability from faculty_timing
$res_ft = mysqli_query($conn, "SELECT teacher_id, day_id, slot_id, is_available FROM faculty_timing WHERE is_available = 0");
if ($res_ft) {
    while ($ft = mysqli_fetch_assoc($res_ft)) {
        $teacher_busy[intval($ft['teacher_id'])][intval($ft['day_id'])][intval($ft['slot_id'])] = true;
    }
}

// Teacher constraint: max 2 consecutive teaching periods, ensuring free periods
function isTeacherConsecutiveOk($teacher, $day, $slot, &$teacher_busy) {
    if ($slot > 2 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot - 2])) {
        return false;
    }
    if ($slot > 1 && $slot < 7 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot + 1])) {
        return false;
    }
    if ($slot < 6 && isset($teacher_busy[$teacher][$day][$slot + 1]) && isset($teacher_busy[$teacher][$day][$slot + 2])) {
        return false;
    }
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

$schedule_entries = [];

// =========================================================================
// Step A: Schedule LAB subjects (2 continuous hours)
// Batch 1 and Batch 2 in 2 distinct lab rooms simultaneously
// =========================================================================
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'LAB') continue;
    
    $group = strval($sub['group_id']);
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    $dept_id = intval($sub['dept_id']);
    
    $placed = false;
    foreach ($all_2hr_blocks as $blk) {
        $s1 = $blk[0];
        $s2 = $blk[1];
        foreach ($days as $d) {
            if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
            if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy)) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;

            $available_labs = [];
            foreach ($labRooms as $lr) {
                $rid = intval($lr['room_id']);
                if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                    $available_labs[] = $lr;
                }
            }
            
            if (count($available_labs) >= 2) {
                $lab1 = $available_labs[0];
                $lab2 = $available_labs[1];
                
                markSlotBusy($teacher, $lab1['room_id'], $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                markSlotBusy($teacher, $lab1['room_id'], $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
                $room_busy[$lab2['room_id']][$d][$s1] = true;
                $room_busy[$lab2['room_id']][$d][$s2] = true;
                
                $schedule_entries[] = [
                    'room_id' => $lab1['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s1,
                    'time_e_id' => $s1 + 1,
                    'day_id' => $d,
                    'dept_id' => $dept_id,
                    'group_name' => "{$group} (Batch 1)",
                    'subject_type' => 'LAB'
                ];
                $schedule_entries[] = [
                    'room_id' => $lab2['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s1,
                    'time_e_id' => $s1 + 1,
                    'day_id' => $d,
                    'dept_id' => $dept_id,
                    'group_name' => "{$group} (Batch 2)",
                    'subject_type' => 'LAB'
                ];
                $schedule_entries[] = [
                    'room_id' => $lab1['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s2,
                    'time_e_id' => $s2 + 1,
                    'day_id' => $d,
                    'dept_id' => $dept_id,
                    'group_name' => "{$group} (Batch 1)",
                    'subject_type' => 'LAB'
                ];
                $schedule_entries[] = [
                    'room_id' => $lab2['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s2,
                    'time_e_id' => $s2 + 1,
                    'day_id' => $d,
                    'dept_id' => $dept_id,
                    'group_name' => "{$group} (Batch 2)",
                    'subject_type' => 'LAB'
                ];
                
                $placed = true;
                break 2;
            }
        }
    }
}

// =========================================================================
// Step B1: Schedule MAJOR_PROJECT subjects (6 hrs/wk: 2 hrs/day x 3 days, afternoon [5,6])
// =========================================================================
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'MAJOR_PROJECT') continue;
    
    $group = strval($sub['group_id']);
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    $dept_id = intval($sub['dept_id']);
    
    $days_assigned = 0;
    $target_days = 3;
    
    foreach ($days as $d) {
        if ($days_assigned >= $target_days) break;
        
        foreach ($afternoon_blocks as $blk) {
            $s1 = $blk[0];
            $s2 = $blk[1];
            
            if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
            if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy)) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
            
            foreach ($lecRooms as $lr) {
                $rid = intval($lr['room_id']);
                if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                    markSlotBusy($teacher, $rid, $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                    markSlotBusy($teacher, $rid, $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
                    
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $s1,
                        'time_e_id' => $s1 + 1,
                        'day_id' => $d,
                        'dept_id' => $dept_id,
                        'group_name' => $group,
                        'subject_type' => 'MAJOR_PROJECT'
                    ];
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $s2,
                        'time_e_id' => $s2 + 1,
                        'day_id' => $d,
                        'dept_id' => $dept_id,
                        'group_name' => $group,
                        'subject_type' => 'MAJOR_PROJECT'
                    ];
                    
                    $days_assigned++;
                    break 2;
                }
            }
        }
    }
}

// =========================================================================
// Step B2: Schedule MINI_PROJECT subjects (4 hrs/wk: 2 hrs/day x 2 days, morning [1,2] or [3,4])
// =========================================================================
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'MINI_PROJECT') continue;
    
    $group = strval($sub['group_id']);
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    $dept_id = intval($sub['dept_id']);
    
    $days_assigned = 0;
    $target_days = 2;
    
    foreach ($days as $d) {
        if ($days_assigned >= $target_days) break;
        
        foreach ($morning_blocks as $blk) {
            $s1 = $blk[0];
            $s2 = $blk[1];
            
            if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
            if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy)) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
            
            foreach ($lecRooms as $lr) {
                $rid = intval($lr['room_id']);
                if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                    markSlotBusy($teacher, $rid, $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                    markSlotBusy($teacher, $rid, $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
                    
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $s1,
                        'time_e_id' => $s1 + 1,
                        'day_id' => $d,
                        'dept_id' => $dept_id,
                        'group_name' => $group,
                        'subject_type' => 'MINI_PROJECT'
                    ];
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $s2,
                        'time_e_id' => $s2 + 1,
                        'day_id' => $d,
                        'dept_id' => $dept_id,
                        'group_name' => $group,
                        'subject_type' => 'MINI_PROJECT'
                    ];
                    
                    $days_assigned++;
                    break 2;
                }
            }
        }
    }
}

// =========================================================================
// Step C: Schedule NSS and COMMUNITY_PROJECT (2 continuous hours)
// NSS: strictly afternoon [5,6]. Community project: [3,4], [1,2], or [5,6]
// =========================================================================
foreach ($subjects as $sub) {
    if (!in_array($sub['subject_type'], ['NSS', 'COMMUNITY_PROJECT'])) continue;
    
    $group = strval($sub['group_id']);
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    $dept_id = intval($sub['dept_id']);
    
    $blocks_to_try = ($sub['subject_type'] === 'NSS') ? $afternoon_blocks : [[3, 4], [1, 2], [5, 6]];
    
    $placed = false;
    foreach ($blocks_to_try as $blk) {
        $s1 = $blk[0];
        $s2 = $blk[1];
        foreach ($days as $d) {
            if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
            if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy)) continue;
            if (!isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy)) continue;
            
            foreach ($lecRooms as $lr) {
                $rid = intval($lr['room_id']);
                if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) {
                    markSlotBusy($teacher, $rid, $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                    markSlotBusy($teacher, $rid, $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
                    
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $s1,
                        'time_e_id' => $s1 + 1,
                        'day_id' => $d,
                        'dept_id' => $dept_id,
                        'group_name' => $group,
                        'subject_type' => $sub['subject_type']
                    ];
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $s2,
                        'time_e_id' => $s2 + 1,
                        'day_id' => $d,
                        'dept_id' => $dept_id,
                        'group_name' => $group,
                        'subject_type' => $sub['subject_type']
                    ];
                    
                    $placed = true;
                    break 3;
                }
            }
        }
    }
}

// =========================================================================
// Contiguity Slot Selector for Gap Elimination
// =========================================================================
function getGapFreeSlotsForGroup($group, $day, &$group_busy) {
    $busy = isset($group_busy[$group][$day]) ? $group_busy[$group][$day] : [];
    
    $candidates = [];
    for ($s = 1; $s <= 7; $s++) {
        if (!isset($busy[$s])) {
            $score = 0;
            
            $has_afternoon = (isset($busy[5]) || isset($busy[6]));
            $has_morning = (isset($busy[1]) || isset($busy[2]) || isset($busy[3]) || isset($busy[4]));
            
            if (!$has_afternoon && !$has_morning) {
                // If completely empty day, start at 1, 2, 3, 4
                $score = 300 - ($s * 25);
            } elseif ($has_afternoon && !$has_morning) {
                // If afternoon has classes (e.g. Major Project 5&6), fill morning from 4 backwards to lunch!
                // 4 (adjacent to lunch) > 3 > 2 > 1
                if ($s === 4) $score = 500;
                elseif ($s === 3) $score = 400;
                elseif ($s === 2) $score = 300;
                elseif ($s === 1) $score = 200;
                else $score = -500; // Never pick slot 7
            } else {
                // Morning already has some classes: extend contiguously!
                if ($s > 1 && isset($busy[$s - 1])) $score += 350; // Contiguous forward
                if ($s < 7 && isset($busy[$s + 1])) $score += 300; // Contiguous backward
                if ($s > 1 && $s < 7 && isset($busy[$s - 1]) && isset($busy[$s + 1])) $score += 800; // Bridge gap
                
                // Penalize non-contiguous jumping
                if (isset($busy[1]) && !isset($busy[2]) && $s > 2) $score -= 400;
                if (isset($busy[2]) && !isset($busy[3]) && $s > 3) $score -= 400;
                if (isset($busy[3]) && !isset($busy[4]) && $s > 4) $score -= 400;
                if ($s == 7 && (!isset($busy[1]) || !isset($busy[2]) || !isset($busy[3]) || !isset($busy[4]))) $score -= 1000;
            }
            
            $candidates[] = ['slot' => $s, 'score' => $score];
        }
    }
    
    usort($candidates, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    return array_column($candidates, 'slot');
}

// =========================================================================
// Step D: Schedule 1-Credit Subject (1 hr theory, 1 hr/week)
// =========================================================================
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'ONE_CREDIT') continue;
    
    $group = strval($sub['group_id']);
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    $dept_id = intval($sub['dept_id']);
    
    $sorted_days = $days;
    usort($sorted_days, function($a, $b) use ($group, $group_busy) {
        $ca = isset($group_busy[$group][$a]) ? count($group_busy[$group][$a]) : 0;
        $cb = isset($group_busy[$group][$b]) ? count($group_busy[$group][$b]) : 0;
        return $ca - $cb;
    });
    
    $placed = false;
    foreach ($sorted_days as $d) {
        if ($placed) break;
        $slots = getGapFreeSlotsForGroup($group, $d, $group_busy);
        foreach ($slots as $slot) {
            if (!isTeacherConsecutiveOk($teacher, $d, $slot, $teacher_busy)) continue;
            foreach ($lecRooms as $lr) {
                $rid = intval($lr['room_id']);
                if (isSlotAvailable($teacher, $rid, $group, $d, $slot, $teacher_busy, $room_busy, $group_busy)) {
                    markSlotBusy($teacher, $rid, $group, $d, $slot, $teacher_busy, $room_busy, $group_busy);
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $slot,
                        'time_e_id' => $slot + 1,
                        'day_id' => $d,
                        'dept_id' => $dept_id,
                        'group_name' => $group,
                        'subject_type' => 'ONE_CREDIT'
                    ];
                    $placed = true;
                    break 2;
                }
            }
        }
    }
}

// =========================================================================
// Step E: Schedule THEORY subjects
// =========================================================================
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'THEORY') continue;
    
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
            if ($attempt == 3 && $current_on_day >= 3) continue;
            
            $slots = getGapFreeSlotsForGroup($group, $d, $group_busy);
            
            foreach ($slots as $slot) {
                if ($allocated >= $needed_hours) break;
                
                if ($attempt <= 3 && !isTeacherConsecutiveOk($teacher, $d, $slot, $teacher_busy)) {
                    continue;
                }
                
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
                            'subject_type' => 'THEORY'
                        ];
                        $allocated++;
                        break 2;
                    }
                }
            }
        }
    }
}

// =========================================================================
// Step F: Post-Processing Gap-Filling Optimization
// =========================================================================
$changed = true;
$passes = 0;
while ($changed && $passes < 5) {
    $changed = false;
    $passes++;
    
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
            
            // If morning has an empty slot (1, 2, 3, or 4) while a later slot (e.g. 7, 6, 5, 4, 3) is busy
            for ($s = 1; $s <= 4; $s++) {
                if (!isset($g_slots[$s])) {
                    // Check if there is an isolated later theory slot on this day that can move to s
                    for ($target = 7; $target > $s; $target--) {
                        if (isset($g_slots[$target])) {
                            $idx = $g_slots[$target];
                            $entry = $schedule_entries[$idx];
                            
                            if (in_array($entry['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
                                $tid = $entry['teacher_id'];
                                $rid = $entry['room_id'];
                                
                                unset($teacher_busy[$tid][$d][$target]);
                                unset($room_busy[$rid][$d][$target]);
                                unset($group_busy[$grp][$d][$target]);
                                
                                if (isSlotAvailable($tid, $rid, $grp, $d, $s, $teacher_busy, $room_busy, $group_busy) &&
                                    isTeacherConsecutiveOk($tid, $d, $s, $teacher_busy)) {
                                    
                                    markSlotBusy($tid, $rid, $grp, $d, $s, $teacher_busy, $room_busy, $group_busy);
                                    $schedule_entries[$idx]['time_s_id'] = $s;
                                    $schedule_entries[$idx]['time_e_id'] = $s + 1;
                                    $changed = true;
                                    break 2;
                                } else {
                                    $teacher_busy[$tid][$d][$target] = true;
                                    $room_busy[$rid][$d][$target] = true;
                                    $group_busy[$grp][$d][$target] = true;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

echo "Total sessions scheduled: " . count($schedule_entries) . "\n";

// Inspect grid for all groups
$groups = ['91', '75', '85', '107', '117'];
foreach ($groups as $grp) {
    echo "\n=== GROUP $grp ===\n";
    $grid = [];
    foreach ($schedule_entries as $entry) {
        $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
        if ($g_clean === $grp) {
            $grid[$entry['day_id']][$entry['time_s_id']][] = $entry['sub_id'];
        }
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

// Faculty matrix check
echo "\n=== FACULTY TEACHING MATRIX (Checking free periods & max consecutive) ===\n";
$fac_grid = [];
foreach ($schedule_entries as $entry) {
    $fac_grid[$entry['teacher_id']][$entry['day_id']][$entry['time_s_id']] = true;
}

$has_3_consec = false;
foreach ($fac_grid as $tid => $d_slots) {
    foreach ($d_slots as $d => $slots_arr) {
        $slots = array_keys($slots_arr);
        sort($slots);
        $consec = 1;
        for ($i = 0; $i < count($slots) - 1; $i++) {
            if ($slots[$i+1] == $slots[$i] + 1) {
                $consec++;
                if ($consec >= 3) {
                    echo "Notice: Teacher $tid has $consec consecutive slots on Day $d: " . implode(',', $slots) . "\n";
                    $has_3_consec = true;
                }
            } else {
                $consec = 1;
            }
        }
    }
}
if (!$has_3_consec) {
    echo "SUCCESS: No teacher has 3 or more consecutive slots! Every teacher has free periods.\n";
}
