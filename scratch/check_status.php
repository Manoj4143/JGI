<?php
require_once __DIR__ . '/../includes/DBConnection.php';

echo "=== DISTINCT GROUPS IN SCHED ===\n";
$r = mysqli_query($conn, 'SELECT DISTINCT group_name FROM sched ORDER BY group_name ASC');
if ($r) {
    while($row = mysqli_fetch_assoc($r)) {
        echo $row['group_name'] . "\n";
    }
} else {
    echo "Query error: " . mysqli_error($conn) . "\n";
}

echo "\n=== SUBJECTS IN DATABASE ===\n";
$r2 = mysqli_query($conn, 'SELECT sub_id, sub_code, sub_name, subject_type, sub_lechrsprday, group_id, dept_id, instructor FROM subjects');
if ($r2) {
    while($row = mysqli_fetch_assoc($r2)) {
        echo "{$row['sub_id']} | {$row['sub_code']} | {$row['sub_name']} | Type: {$row['subject_type']} | Hrs: {$row['sub_lechrsprday']} | Grp: {$row['group_id']} | Dept: {$row['dept_id']} | Teacher: {$row['instructor']}\n";
    }
}

echo "\n=== SCHED COUNT PER DAY & TIME SLOT ===\n";
$r3 = mysqli_query($conn, 'SELECT day_id, time_s_id, count(*) as cnt FROM sched GROUP BY day_id, time_s_id ORDER BY day_id, time_s_id');
if ($r3) {
    while($row = mysqli_fetch_assoc($r3)) {
        echo "Day: {$row['day_id']} | Slot: {$row['time_s_id']} | Count: {$row['cnt']}\n";
    }
}
