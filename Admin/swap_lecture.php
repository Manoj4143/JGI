<?php
session_start();
require_once __DIR__ . '/../includes/DBConnection.php';

header('Content-Type: application/json');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// 1. Get free teachers for a specific day and slot
if ($action === 'get_free_teachers') {
    $day_id = isset($_GET['day_id']) ? intval($_GET['day_id']) : 0;
    $slot_id = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : 0;
    $current_teacher = isset($_GET['current_teacher']) ? intval($_GET['current_teacher']) : 0;

    if ($day_id <= 0 || $slot_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }

    // Teachers busy in sched
    $busy_query = "SELECT DISTINCT teacher_id FROM sched WHERE day_id = $day_id AND time_s_id = $slot_id";
    
    // Teachers marked unavailable in faculty_timing
    $timing_query = "SELECT DISTINCT teacher_id FROM faculty_timing WHERE day_id = $day_id AND slot_id = $slot_id AND is_available = 0";

    $query = "SELECT teacher_id, teacher_name, acad_rank 
              FROM profile 
              WHERE teacher_id NOT IN ($busy_query) 
                AND teacher_id NOT IN ($timing_query)
              ORDER BY teacher_name ASC";
    
    $res = mysqli_query($conn, $query);
    $free_teachers = [];
    if ($res) {
        while ($t = mysqli_fetch_assoc($res)) {
            $free_teachers[] = [
                'teacher_id' => $t['teacher_id'],
                'teacher_name' => $t['teacher_name'],
                'academic_rank' => $t['acad_rank']
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'free_teachers' => $free_teachers,
        'count' => count($free_teachers)
    ]);
    exit;
}

// 2. Perform Swap or Substitution
if ($action === 'swap') {
    $sched_id = isset($_POST['sched_id']) ? intval($_POST['sched_id']) : 0;
    $new_teacher_id = isset($_POST['new_teacher_id']) ? intval($_POST['new_teacher_id']) : 0;
    $new_room_id = isset($_POST['new_room_id']) ? intval($_POST['new_room_id']) : 0;

    if ($sched_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid schedule ID']);
        exit;
    }

    // Fetch existing schedule entry
    $cur = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM sched WHERE sched_id = $sched_id"));
    if (!$cur) {
        echo json_encode(['status' => 'error', 'message' => 'Lecture session not found']);
        exit;
    }

    $day_id = intval($cur['day_id']);
    $slot_id = intval($cur['time_s_id']);
    $group_name = $cur['group_name'];
    $sub_id = intval($cur['sub_id']);

    // Check if this subject is a 2-hour block (e.g. Lab, NSS, Project)
    $sub_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT subject_type FROM subjects WHERE sub_id = $sub_id"));
    $stype = $sub_info ? $sub_info['subject_type'] : '';

    $is_2hr = in_array($stype, ['LAB', 'NSS', 'MINI_PROJECT', 'MAJOR_PROJECT', 'COMMUNITY_PROJECT']);

    // Find paired slot if 2hr block
    $paired_slot = 0;
    if ($is_2hr) {
        $blocks = [[1, 2], [3, 4], [5, 6]];
        foreach ($blocks as $blk) {
            if ($slot_id == $blk[0]) $paired_slot = $blk[1];
            elseif ($slot_id == $blk[1]) $paired_slot = $blk[0];
        }
    }

    // Verify teacher availability
    if ($new_teacher_id > 0) {
        $chk_busy = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM sched WHERE teacher_id = $new_teacher_id AND day_id = $day_id AND time_s_id = $slot_id AND sched_id != $sched_id"));
        if ($chk_busy && $chk_busy['cnt'] > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Selected teacher is already teaching another class in this period!']);
            exit;
        }

        if ($paired_slot > 0) {
            $chk_busy_paired = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM sched WHERE teacher_id = $new_teacher_id AND day_id = $day_id AND time_s_id = $paired_slot AND sub_id != $sub_id"));
            if ($chk_busy_paired && $chk_busy_paired['cnt'] > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Selected teacher is occupied in the paired hour of this 2-hour block!']);
                exit;
            }
        }
    }

    // Execute update
    $updates = [];
    if ($new_teacher_id > 0) $updates[] = "teacher_id = $new_teacher_id";
    if ($new_room_id > 0) $updates[] = "room_id = $new_room_id";

    if (empty($updates)) {
        echo json_encode(['status' => 'error', 'message' => 'No changes requested']);
        exit;
    }

    $update_sql = implode(', ', $updates);

    // Update primary entry (if it's a lab batch or regular)
    if ($is_2hr) {
        // Update both periods for this subject & group
        $clean_grp = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $group_name);
        $res_up = mysqli_query($conn, "UPDATE sched SET $update_sql WHERE sub_id = $sub_id AND day_id = $day_id AND (group_name = '$clean_grp' OR group_name LIKE '{$clean_grp} (%')");
    } else {
        $res_up = mysqli_query($conn, "UPDATE sched SET $update_sql WHERE sched_id = $sched_id");
    }

    if ($res_up) {
        echo json_encode(['status' => 'success', 'message' => 'Lecture successfully updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update error: ' . mysqli_error($conn)]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
