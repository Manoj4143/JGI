<?php
require_once __DIR__ . '/../includes/DBConnection.php';

echo "=== Day 4, Slot 3 sched entries ===\n";
$r = mysqli_query($conn, "SELECT s.*, r.room_name FROM sched s JOIN room r ON s.room_id = r.room_id WHERE s.day_id = 4 AND s.time_s_id = 3");
while ($row = mysqli_fetch_assoc($r)) {
    echo "Room {$row['room_name']} | Grp {$row['group_name']} | Teacher {$row['teacher_id']}\n";
}

echo "\n=== All Lecture Rooms ===\n";
$r2 = mysqli_query($conn, "SELECT * FROM room WHERE room_isNKN = 0");
while ($row = mysqli_fetch_assoc($r2)) {
    echo "Room ID {$row['room_id']}: {$row['room_name']}\n";
}
