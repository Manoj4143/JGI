<?php
require_once __DIR__ . '/../includes/DBConnection.php';

// Fetch rooms
$labRooms = [];
$lecRooms = [];
$res = mysqli_query($conn, "SELECT * FROM room");
while ($r = mysqli_fetch_assoc($res)) {
    if ($r['room_isNKN'] == 1) {
        $labRooms[] = $r;
    } else {
        $lecRooms[] = $r;
    }
}

// Fetch subjects
$subjects = [];
$res = mysqli_query($conn, "SELECT * FROM subjects WHERE sub_lechrsprday > 0 ORDER BY 
    CASE subject_type 
        WHEN 'LAB' THEN 1 
        WHEN 'NSS' THEN 2 
        WHEN 'THEORY' THEN 3 
        WHEN 'ONE_CREDIT' THEN 4 
        ELSE 5 
    END, 
    sub_lechrsprday DESC");
while ($s = mysqli_fetch_assoc($res)) {
    $subjects[] = $s;
}

$days = [1, 2, 3, 4, 5]; // Mon, Tue, Wed, Thu, Fri
$morning_blocks = [[1, 2], [3, 4]];
$afternoon_blocks = [[6, 7], [7, 8], [8, 9]];
$all_2hr_blocks = array_merge($morning_blocks, $afternoon_blocks);
$all_slots = [1, 2, 3, 4, 6, 7, 8, 9];

$teacher_busy = []; // [teacher_id][day][slot]
$room_busy = [];    // [room_id][day][slot]
$group_busy = [];   // [group_id][day][slot]
$subject_days = []; // [sub_id][day] count

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

// 1. Schedule LAB subjects
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'LAB') continue;
    
    $group = !empty($sub['group_id']) ? $sub['group_id'] : '75';
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    
    // We need 2 distinct lab rooms
    if (count($labRooms) < 2) {
        die("Error: At least 2 lab rooms are required for LAB batch splitting.\n");
    }
    
    $placed = false;
    // Prefer morning or afternoon block
    foreach ($all_2hr_blocks as $blk) {
        $s1 = $blk[0];
        $s2 = $blk[1];
        foreach ($days as $d) {
            // Check if group and teacher are free for both slots
            if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
            if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
            
            // Find 2 free lab rooms
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
                
                // Mark busy
                markSlotBusy($teacher, $lab1['room_id'], $group, $d, $s1, $teacher_busy, $room_busy, $group_busy);
                markSlotBusy($teacher, $lab1['room_id'], $group, $d, $s2, $teacher_busy, $room_busy, $group_busy);
                // Also mark lab2 as busy for both slots
                $room_busy[$lab2['room_id']][$d][$s1] = true;
                $room_busy[$lab2['room_id']][$d][$s2] = true;
                
                // Add entries for Batch 1 and Batch 2 for both consecutive hours
                $schedule_entries[] = [
                    'room_id' => $lab1['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s1,
                    'time_e_id' => $s1 + 1,
                    'day_id' => $d,
                    'group_name' => "{$group} (Batch 1)"
                ];
                $schedule_entries[] = [
                    'room_id' => $lab2['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s1,
                    'time_e_id' => $s1 + 1,
                    'day_id' => $d,
                    'group_name' => "{$group} (Batch 2)"
                ];
                $schedule_entries[] = [
                    'room_id' => $lab1['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s2,
                    'time_e_id' => $s2 + 1,
                    'day_id' => $d,
                    'group_name' => "{$group} (Batch 1)"
                ];
                $schedule_entries[] = [
                    'room_id' => $lab2['room_id'],
                    'sub_id' => $sub_id,
                    'teacher_id' => $teacher,
                    'time_s_id' => $s2,
                    'time_e_id' => $s2 + 1,
                    'day_id' => $d,
                    'group_name' => "{$group} (Batch 2)"
                ];
                
                $placed = true;
                echo "Scheduled LAB {$sub['sub_code']} on Day $d at Slots $s1-$s2: Batch 1 in {$lab1['room_name']}, Batch 2 in {$lab2['room_name']}\n";
                break 2;
            }
        }
    }
    if (!$placed) {
        echo "Warning: Could not place LAB {$sub['sub_code']}\n";
    }
}

// 2. Schedule NSS subjects (Strictly 2 continuous hours in Afternoon)
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'NSS') continue;
    
    $group = !empty($sub['group_id']) ? $sub['group_id'] : '75';
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    
    $placed = false;
    foreach ($afternoon_blocks as $blk) {
        $s1 = $blk[0];
        $s2 = $blk[1];
        foreach ($days as $d) {
            if (isset($group_busy[$group][$d][$s1]) || isset($group_busy[$group][$d][$s2])) continue;
            if (isset($teacher_busy[$teacher][$d][$s1]) || isset($teacher_busy[$teacher][$d][$s2])) continue;
            
            // Find a free lecture room
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
                        'group_name' => $group
                    ];
                    $schedule_entries[] = [
                        'room_id' => $rid,
                        'sub_id' => $sub_id,
                        'teacher_id' => $teacher,
                        'time_s_id' => $s2,
                        'time_e_id' => $s2 + 1,
                        'day_id' => $d,
                        'group_name' => $group
                    ];
                    
                    $placed = true;
                    echo "Scheduled NSS {$sub['sub_code']} on Day $d at Afternoon Slots $s1-$s2 in {$lr['room_name']}\n";
                    break 3;
                }
            }
        }
    }
    if (!$placed) {
        echo "Warning: Could not place NSS {$sub['sub_code']}\n";
    }
}

// 3. Schedule 1-Credit Subject (1 hr theory, 1 hr/week)
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'ONE_CREDIT') continue;
    
    $group = !empty($sub['group_id']) ? $sub['group_id'] : '75';
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    
    $placed = false;
    foreach ($days as $d) {
        foreach ($all_slots as $slot) {
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
                        'group_name' => $group
                    ];
                    $placed = true;
                    echo "Scheduled ONE_CREDIT {$sub['sub_code']} on Day $d at Slot $slot in {$lr['room_name']}\n";
                    break 3;
                }
            }
        }
    }
    if (!$placed) {
        echo "Warning: Could not place ONE_CREDIT {$sub['sub_code']}\n";
    }
}

// 4. Schedule THEORY subjects (1 hr per session, distributed across days)
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'THEORY') continue;
    
    $group = !empty($sub['group_id']) ? $sub['group_id'] : '75';
    $teacher = intval($sub['instructor']);
    $sub_id = intval($sub['sub_id']);
    $needed_hours = intval($sub['sub_lechrsprday']);
    
    $allocated = 0;
    // Round 1: Try to allocate at most 1 hour per day
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        foreach ($days as $d) {
            if ($allocated >= $needed_hours) break;
            // In attempt 1, do not exceed 1 session per day
            $current_on_day = isset($subject_days[$sub_id][$d]) ? $subject_days[$sub_id][$d] : 0;
            if ($attempt == 1 && $current_on_day >= 1) continue;
            if ($attempt == 2 && $current_on_day >= 2) continue;
            
            foreach ($all_slots as $slot) {
                if ($allocated >= $needed_hours) break;
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
                            'group_name' => $group
                        ];
                        $allocated++;
                        break 2;
                    }
                }
            }
        }
    }
    echo "Scheduled THEORY {$sub['sub_code']} ({$sub['sub_name']}): $allocated / $needed_hours hours placed\n";
}

echo "\nTotal schedule entries generated: " . count($schedule_entries) . "\n";
