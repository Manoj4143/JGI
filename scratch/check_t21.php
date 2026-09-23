<?php
require_once __DIR__ . '/test_isolated_theory.php';

echo "Teacher 21 schedule in test_isolated_theory:\n";
foreach ($schedule_entries as $e) {
    if ($e['teacher_id'] == 21) {
        echo "Day {$e['day_id']} Slot {$e['time_s_id']} Grp {$e['group_name']} Sub {$e['sub_id']}\n";
    }
}
