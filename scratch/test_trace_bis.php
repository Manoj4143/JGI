<?php
require_once __DIR__ . '/../includes/DBConnection.php';
// Let's simulate up to Step B2
$days = [1, 2, 3, 4, 5, 6];
$morning_blocks = [[1, 2], [3, 4]];
$afternoon_blocks = [[5, 6]];
$all_2hr_blocks = array_merge($morning_blocks, $afternoon_blocks);

$sub_query = "SELECT * FROM subjects WHERE sub_lechrsprday > 0 ORDER BY sub_id";
$res = mysqli_query($conn, $sub_query);
$subjects = [];
while ($row = mysqli_fetch_assoc($res)) {
    $subjects[] = $row;
}

$room_query = "SELECT * FROM room";
$res_rooms = mysqli_query($conn, $room_query);
$rooms = []; $labRooms = []; $lecRooms = [];
while ($r = mysqli_fetch_assoc($res_rooms)) {
    $rooms[] = $r;
    if ($r['room_desc'] === 'COMPUTER lAB' || $r['room_desc'] === 'Computer Lab 1' || $r['room_desc'] === 'Computer Lab 2') {
        $labRooms[] = $r;
    } else {
        $lecRooms[] = $r;
    }
}

$teacher_busy = [];
$room_busy = [];
$group_busy = [];
$group_day_subs = [];

$res_ft = mysqli_query($conn, "SELECT teacher_id, day_id, slot_id, is_available FROM faculty_timing WHERE is_available = 0");
if ($res_ft) {
    while ($ft = mysqli_fetch_assoc($res_ft)) {
        $teacher_busy[intval($ft['teacher_id'])][intval($ft['day_id'])][intval($ft['slot_id'])] = true;
    }
}

function isTeacherConsecutiveOk($teacher, $day, $slot, &$teacher_busy) {
    if ($slot > 2 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot - 2])) return false;
    if ($slot > 1 && $slot < 7 && isset($teacher_busy[$teacher][$day][$slot - 1]) && isset($teacher_busy[$teacher][$day][$slot + 1])) return false;
    if ($slot < 6 && isset($teacher_busy[$teacher][$day][$slot + 1]) && isset($teacher_busy[$teacher][$day][$slot + 2])) return false;
    return true;
}

// Step A: LAB
foreach ($subjects as $sub) {
    if ($sub['subject_type'] !== 'LAB') continue;
    $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']);
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
                $teacher_busy[$teacher][$d][$s1] = true; $teacher_busy[$teacher][$d][$s2] = true;
                $group_busy[$group][$d][$s1] = $sub_id; $group_busy[$group][$d][$s2] = $sub_id;
                $room_busy[$available_labs[0]['room_id']][$d][$s1] = true; $room_busy[$available_labs[0]['room_id']][$d][$s2] = true;
                $room_busy[$available_labs[1]['room_id']][$d][$s1] = true; $room_busy[$available_labs[1]['room_id']][$d][$s2] = true;
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
                    $teacher_busy[$teacher][$d][$s1] = true; $teacher_busy[$teacher][$d][$s2] = true;
                    $group_busy[$group][$d][$s1] = $sub_id; $group_busy[$group][$d][$s2] = $sub_id;
                    $room_busy[$rid][$d][$s1] = true; $room_busy[$rid][$d][$s2] = true;
                    $days_assigned++;
                    break 2;
                }
            }
        }
    }
}

// Check BIS586
foreach ($subjects as $sub) {
    if ($sub['sub_code'] !== 'BIS586') continue;
    $group = strval($sub['group_id']); $teacher = intval($sub['instructor']); $sub_id = intval($sub['sub_id']);
    echo "Checking BIS586: Grp=$group, Teacher=$teacher\n";
    foreach ($days as $d) {
        $s1 = 5; $s2 = 6;
        $gb1 = isset($group_busy[$group][$d][$s1]);
        $gb2 = isset($group_busy[$group][$d][$s2]);
        $tb1 = isset($teacher_busy[$teacher][$d][$s1]);
        $tb2 = isset($teacher_busy[$teacher][$d][$s2]);
        $avail_rooms = 0;
        foreach ($lecRooms as $lr) {
            $rid = intval($lr['room_id']);
            if (!isset($room_busy[$rid][$d][$s1]) && !isset($room_busy[$rid][$d][$s2])) $avail_rooms++;
        }
        echo "Day $d: group_busy=[$gb1,$gb2], teacher_busy=[$tb1,$tb2], avail_rooms=$avail_rooms\n";
    }
}
