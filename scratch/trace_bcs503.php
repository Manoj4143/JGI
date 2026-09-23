<?php
require_once __DIR__ . '/find_perfect_143.php';
$r = run_scheduler(2);
echo "In find_perfect_143 for sub_id 31:\n";
$cnt = 0;
foreach ($r['entries'] as $e) {
    if ($e['sub_id'] == 31) {
        echo "  day={$e['day_id']}, slot={$e['time_s_id']}, room={$e['room_id']}, grp={$e['group_name']}\n";
        $cnt++;
    }
}
echo "Total count for sub_id 31: $cnt\n";
