<?php
require_once __DIR__ . '/test_shuffled_scheduler.php';

echo "\n================ TEACHER 22 SCHEDULE ================\n";
$t22_grid = [];
foreach ($schedule_entries as $entry) {
    if ($entry['teacher_id'] == 22) {
        $t22_grid[$entry['day_id']][$entry['time_s_id']] = $sub_map[$entry['sub_id']] . " (" . $entry['subject_type'] . ")";
    }
}
$dnames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($t22_grid[$d][$s]) ? $t22_grid[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-18s] ", $s, $c);
    }
    echo $line . "\n";
}
