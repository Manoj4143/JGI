<?php
require_once __DIR__ . '/../includes/DBConnection.php';
// Check Group 117 classes
$r = mysqli_query($conn, "SELECT s.*, sub.sub_code, sub.subject_type, p.teacher_name, r.room_name 
    FROM sched s 
    JOIN subjects sub ON s.sub_id = sub.sub_id 
    JOIN profile p ON s.teacher_id = p.teacher_id 
    JOIN room r ON s.room_id = r.room_id 
    WHERE s.group_name LIKE '117%' ORDER BY s.day_id, s.time_s_id");
while ($row = mysqli_fetch_assoc($r)) {
    echo "Day {$row['day_id']} Slot {$row['time_s_id']}: {$row['sub_code']} ({$row['subject_type']}) | T: {$row['teacher_name']} (ID {$row['teacher_id']})\n";
}
