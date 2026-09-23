<?php
require_once __DIR__ . '/test_shuffled_scheduler.php';

echo "\n================ GROUP 85 SCHEDULE ================\n";
$grid85 = [];
foreach ($schedule_entries as $entry) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
    if ($g_clean === '85') {
        $grid85[$entry['day_id']][$entry['time_s_id']] = $sub_map[$entry['sub_id']];
    }
}
$dnames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $code = isset($grid85[$d][$s]) ? $grid85[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-8s] ", $s, $code);
    }
    echo $line . "\n";
}
