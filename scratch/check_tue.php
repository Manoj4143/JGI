<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../scratch/test_gap_free_scheduler.php';

echo "\n--- TUESDAY FOR ALL GROUPS ---\n";
foreach ($schedule_entries as $e) {
    if ($e['day_id'] == 2) {
        $sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT sub_code, sub_name FROM subjects WHERE sub_id = {$e['sub_id']}"));
        $teach = mysqli_fetch_assoc(mysqli_query($conn, "SELECT teacher_name FROM profile WHERE teacher_id = {$e['teacher_id']}"));
        echo "Group {$e['group_name']} | Slot {$e['time_s_id']}: {$sub['sub_code']} - Teacher {$e['teacher_id']} ({$teach['teacher_name']}) Room {$e['room_id']}\n";
    }
}
