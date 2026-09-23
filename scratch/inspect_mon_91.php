<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../scratch/test_gap_free_scheduler.php';

echo "\n--- MONDAY FOR GROUP 91 ---\n";
foreach ($schedule_entries as $e) {
    if (preg_replace('/\s*\(Batch\s*\d+\)$/', '', $e['group_name']) === '91' && $e['day_id'] == 1) {
        $sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT sub_code, sub_name FROM subjects WHERE sub_id = {$e['sub_id']}"));
        $teach = mysqli_fetch_assoc(mysqli_query($conn, "SELECT teacher_name FROM profile WHERE teacher_id = {$e['teacher_id']}"));
        echo "Slot {$e['time_s_id']}: {$sub['sub_code']} ({$sub['sub_name']}) - Teacher: {$e['teacher_id']} ({$teach['teacher_name']}) in Room {$e['room_id']}\n";
    }
}
