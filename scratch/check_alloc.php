<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, 'SELECT s.sub_id, s.sub_name, s.sub_code, s.subject_type, s.group_id, COUNT(sc.sub_id) as scheduled, s.sub_lechrsprday as target FROM subjects s LEFT JOIN sched sc ON s.sub_id=sc.sub_id GROUP BY s.sub_id ORDER BY s.group_id, s.sub_id');
if (!$r) die(mysqli_error($conn));
$tot_sched = 0;
$tot_target = 0;
while($row = mysqli_fetch_assoc($r)) {
    echo sprintf("%-6s | %-10s | %-16s | Grp: %-4s | %d/%d\n", $row['sub_code'], substr($row['sub_name'], 0, 10), $row['subject_type'], $row['group_id'], $row['scheduled'], $row['target']);
    $tot_sched += $row['scheduled'];
    $tot_target += $row['target'];
}
echo "Total Scheduled: $tot_sched / $tot_target\n";
