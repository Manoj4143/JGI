<?php
require_once __DIR__ . '/DBConnection.php';

$res = mysqli_query($conn, "SELECT sub_id, sub_code, dept_id FROM subjects");
while ($r = mysqli_fetch_assoc($res)) {
    if (preg_match('/\d+/', $r['sub_code'], $m)) {
        $yr = $m[0][0];
    } else {
        $yr = 1;
    }
    $gid = intval($r['dept_id'] . $yr);
    mysqli_query($conn, "UPDATE subjects SET group_id = $gid WHERE sub_id = {$r['sub_id']}");
}
echo "Group IDs updated successfully.\n";

$res = mysqli_query($conn, "SELECT sub_id, sub_code, group_id, subject_type FROM subjects");
while ($r = mysqli_fetch_assoc($res)) {
    echo json_encode($r) . PHP_EOL;
}
