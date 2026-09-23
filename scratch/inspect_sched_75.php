<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$res = mysqli_query($conn, "SELECT s.*, sub.sub_code, sub.subject_type FROM sched s JOIN subjects sub ON s.sub_id = sub.sub_id WHERE s.group_name LIKE '75%' ORDER BY s.day_id, s.time_s_id");
while ($row = mysqli_fetch_assoc($res)) {
    echo "Day {$row['day_id']} Slot {$row['time_s_id']}: {$row['sub_code']} ({$row['subject_type']}), teacher {$row['teacher_id']}, room {$row['room_id']}\n";
}
