<?php
require_once __DIR__ . '/test_shuffled_scheduler.php';

echo "\n================ SUBJECT HOURS CHECK ================\n";
$missing = 0;
foreach ($subjects as $s) {
    $sid = $s['sub_id'];
    $cnt = 0;
    foreach ($schedule_entries as $e) {
        if ($e['sub_id'] == $sid) $cnt++;
    }
    // For lab, each hour has 2 batches, so divide by 2
    if ($s['subject_type'] === 'LAB') $cnt = $cnt / 2;
    
    $req = intval($s['sub_lechrsprday']);
    if ($s['subject_type'] === 'MAJOR_PROJECT') $req = 6;
    if ($s['subject_type'] === 'MINI_PROJECT') $req = 4;
    
    if ($cnt != $req) {
        echo "MISMATCH: Subject {$s['sub_code']} ({$s['subject_type']}, Grp {$s['group_id']}) req $req, scheduled $cnt\n";
        $missing++;
    }
}
if ($missing == 0) {
    echo "SUCCESS: 100% of required subject hours are scheduled perfectly!\n";
}
