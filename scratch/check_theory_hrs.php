<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT sub_id, sub_code, sub_name, group_id, subject_type, sub_lechrsprday, instructor FROM subjects WHERE subject_type IN ('THEORY', 'ONE_CREDIT') ORDER BY group_id, sub_id");
while ($row = mysqli_fetch_assoc($r)) {
    echo "Grp {$row['group_id']} | Sub {$row['sub_code']} | Type {$row['subject_type']} | Hrs/Wk: {$row['sub_lechrsprday']} | Teacher: {$row['instructor']}\n";
}
