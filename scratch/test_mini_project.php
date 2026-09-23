<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_type = 'MINI_PROJECT'");
while ($row = mysqli_fetch_assoc($r)) {
    echo "Sub: {$row['sub_code']} | Grp: {$row['group_id']} | Teacher: {$row['instructor']} | Type: {$row['subject_type']} | Target: {$row['sub_lechrsprday']}\n";
}
