<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT * FROM faculty_timing WHERE teacher_id = 22");
while ($row = mysqli_fetch_assoc($r)) {
    echo "Teacher {$row['teacher_id']} | Day {$row['day_id']} | Slot {$row['slot_id']} | Avail {$row['is_available']}\n";
}
