<?php
require_once __DIR__ . '/find_perfect_143.php';
$res = run_scheduler(0);
echo "Seed 0 with faculty_timing:\n";
echo "Total scheduled: " . $res['scheduled'] . "\n";
echo "Total missing: " . $res['missing'] . "\n";

foreach ($res['subjects'] as $s) {
    $sid = $s['sub_id'];
    $cnt = 0;
    foreach ($res['entries'] as $e) {
        if ($e['sub_id'] == $sid) $cnt++;
    }
    if ($s['subject_type'] === 'LAB') $cnt = $cnt / 2;
    $req = intval($s['sub_lechrsprday']);
    if ($s['subject_type'] === 'MAJOR_PROJECT') $req = 6;
    if ($s['subject_type'] === 'MINI_PROJECT') $req = 4;
    if ($cnt != $req) {
        echo "MISSING: {$s['sub_code']} ({$s['subject_type']}, Grp {$s['group_id']}): req $req, scheduled $cnt, teacher {$s['instructor']}\n";
    }
}
