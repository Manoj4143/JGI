<?php
require_once __DIR__ . '/../includes/DBConnection.php';

// Check teacher 33 schedule
$r = mysqli_query($conn, "SELECT * FROM sched WHERE teacher_id = 33");
echo "Teacher 33 classes:\n";
while ($row = mysqli_fetch_assoc($r)) {
    echo "Day {$row['day_id']} Slot {$row['time_s_id']} Grp {$row['group_name']} Sub {$row['sub_id']}\n";
}

// Check Group 107 classes on Thursday (day 4)
$r2 = mysqli_query($conn, "SELECT s.*, sub.sub_code, p.teacher_name, r.room_name 
    FROM sched s 
    JOIN subjects sub ON s.sub_id = sub.sub_id 
    JOIN profile p ON s.teacher_id = p.teacher_id 
    JOIN room r ON s.room_id = r.room_id 
    WHERE s.group_name LIKE '107%' AND s.day_id = 4 ORDER BY s.time_s_id");
echo "\nGroup 107 on Thursday:\n";
while ($row = mysqli_fetch_assoc($r2)) {
    echo "Slot {$row['time_s_id']}: {$row['sub_code']} | Teacher {$row['teacher_id']} ({$row['teacher_name']}) | Room {$row['room_name']}\n";
}
