<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$res = mysqli_query($conn, "SELECT * FROM subjects WHERE sub_lechrsprday > 0");
$sched_res = mysqli_query($conn, "SELECT sub_id, COUNT(*) as c FROM sched GROUP BY sub_id");
$sched_counts = [];
while ($row = mysqli_fetch_assoc($sched_res)) {
    $sched_counts[$row['sub_id']] = intval($row['c']);
}

while ($s = mysqli_fetch_assoc($res)) {
    $sid = $s['sub_id'];
    $cnt = isset($sched_counts[$sid]) ? $sched_counts[$sid] : 0;
    if ($s['subject_type'] === 'LAB') $cnt = $cnt / 2;
    $req = intval($s['sub_lechrsprday']);
    if ($s['subject_type'] === 'MAJOR_PROJECT') $req = 6;
    if ($s['subject_type'] === 'MINI_PROJECT') $req = 4;
    if ($cnt != $req) {
        echo "MISSING in sched: {$s['sub_code']} ({$s['subject_type']}, Grp {$s['group_id']}): req $req, in sched $cnt\n";
    }
}
