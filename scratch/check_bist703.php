<?php
require_once __DIR__ . '/find_perfect_143.php';
$res = run_scheduler(2);

// Check BIST703
foreach ($res['subjects'] as $s) {
    if ($s['sub_code'] === 'BIST703') {
        echo "BIST703: teacher " . $s['instructor'] . "\n";
        $tid = $s['instructor'];
        for ($d = 1; $d <= 6; $d++) {
            echo "Day $d for Teacher $tid: ";
            for ($slot = 1; $slot <= 6; $slot++) {
                $c = isset($res['entries']) ? '.' : '';
                foreach ($res['entries'] as $e) {
                    if ($e['teacher_id'] == $tid && $e['day_id'] == $d && $e['time_s_id'] == $slot) {
                        $c = $e['sub_id'] . '(' . $e['subject_type'] . ')';
                    }
                }
                echo "S$slot:$c ";
            }
            echo "\n";
        }
    }
}
