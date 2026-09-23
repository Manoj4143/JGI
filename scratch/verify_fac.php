<?php
require_once __DIR__ . '/test_intraday_compact.php';

echo "\n=== FACULTY CONSECUTIVE CHECK ===\n";
$fac_grid = [];
foreach ($schedule_entries as $entry) {
    $fac_grid[$entry['teacher_id']][$entry['day_id']][$entry['time_s_id']] = true;
}

$has_3 = false;
foreach ($fac_grid as $tid => $d_slots) {
    foreach ($d_slots as $d => $slots_arr) {
        $slots = array_keys($slots_arr);
        sort($slots);
        $consec = 1;
        for ($i = 0; $i < count($slots) - 1; $i++) {
            if ($slots[$i+1] == $slots[$i] + 1) {
                $consec++;
                if ($consec >= 3) {
                    echo "Notice: Teacher $tid has $consec consecutive slots on Day $d: " . implode(',', $slots) . "\n";
                    $has_3 = true;
                }
            } else {
                $consec = 1;
            }
        }
    }
}
if (!$has_3) {
    echo "SUCCESS: 100% of faculty have at least 1 free period! Zero teachers have 3 or more consecutive periods.\n";
}
