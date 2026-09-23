<?php
require_once __DIR__ . '/../includes/DBConnection.php';

$res = mysqli_query($conn, "SELECT s.*, sub.sub_code, sub.subject_type FROM sched s JOIN subjects sub ON s.sub_id = sub.sub_id WHERE s.day_id = 4 AND (s.group_name LIKE '107%' OR s.teacher_id = 33)");
while ($row = mysqli_fetch_assoc($res)) {
    echo "Slot {$row['time_s_id']} | Sub {$row['sub_code']} ({$row['subject_type']}) | Grp {$row['group_name']} | Teacher {$row['teacher_id']} | Room {$row['room_id']}\n";
}
