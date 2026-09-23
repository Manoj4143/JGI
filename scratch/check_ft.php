<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$res = mysqli_query($conn, "SELECT ft.*, p.teacher_name FROM faculty_timing ft JOIN profile p ON ft.teacher_id = p.teacher_id WHERE ft.is_available = 0");
echo "Rows with is_available = 0: " . mysqli_num_rows($res) . "\n";
while ($row = mysqli_fetch_assoc($res)) {
    echo "Teacher {$row['teacher_id']} ({$row['teacher_name']}), Day: {$row['day_id']}, Slot: {$row['slot_id']}, Notes: {$row['notes']}\n";
}

