<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT ft.*, p.teacher_name FROM faculty_timing ft JOIN profile p ON ft.teacher_id=p.teacher_id WHERE is_available=0");
while ($row = mysqli_fetch_assoc($r)) {
    echo "Teacher {$row['teacher_id']} ({$row['teacher_name']}) | Day {$row['day_id']} | Slot {$row['slot_id']}\n";
}
