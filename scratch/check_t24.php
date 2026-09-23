<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT * FROM sched WHERE teacher_id = 24 ORDER BY day_id, time_s_id");
echo "Teacher 24 classes:\n";
while ($row = mysqli_fetch_assoc($r)) {
    echo "Day {$row['day_id']} Slot {$row['time_s_id']} Grp {$row['group_name']}\n";
}
