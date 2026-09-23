<?php
require_once __DIR__ . '/test_shuffled_scheduler.php';

$sub_map = [];
foreach ($subjects as $s) {
    $sub_map[$s['sub_id']] = $s['sub_code'];
}

// 1. Check Group 75 grid
echo "\n================ GROUP 75 (5th Sem AIML) ================\n";
$grid75 = [];
foreach ($schedule_entries as $entry) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
    if ($g_clean === '75') {
        $grid75[$entry['day_id']][$entry['time_s_id']] = $sub_map[$entry['sub_id']];
    }
}
$dnames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $code = isset($grid75[$d][$s]) ? $grid75[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-8s] ", $s, $code);
    }
    echo $line . "\n";
}

// 2. Check Teacher 34 schedule (from user's screenshot)
echo "\n================ TEACHER 34 SCHEDULE ================\n";
$t34_grid = [];
foreach ($schedule_entries as $entry) {
    if ($entry['teacher_id'] == 34) {
        $t34_grid[$entry['day_id']][$entry['time_s_id']] = $sub_map[$entry['sub_id']] . " (" . $entry['subject_type'] . ")";
    }
}
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($t34_grid[$d][$s]) ? $t34_grid[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-18s] ", $s, $c);
    }
    echo $line . "\n";
}

// 3. Verify: Check if ANY teacher has 2 consecutive theory classes!
echo "\n================ TEACHER THEORY GAP VALIDATION ================\n";
$t_theory_grid = [];
foreach ($schedule_entries as $entry) {
    if ($entry['subject_type'] === 'THEORY') {
        $t_theory_grid[$entry['teacher_id']][$entry['day_id']][$entry['time_s_id']] = true;
    }
}
$violating_teachers = 0;
foreach ($t_theory_grid as $tid => $d_map) {
    foreach ($d_map as $d => $slots_map) {
        $slots = array_keys($slots_map);
        sort($slots);
        for ($i = 0; $i < count($slots) - 1; $i++) {
            if ($slots[$i+1] == $slots[$i] + 1) {
                echo "VIOLATION: Teacher $tid has back-to-back theory classes on Day $d: slots {$slots[$i]} and {$slots[$i+1]}\n";
                $violating_teachers++;
            }
        }
    }
}
if ($violating_teachers === 0) {
    echo "SUCCESS: 100% of teachers have at least 1 free period between theory classes! Zero back-to-back theory classes.\n";
}

// 4. Verify: Check if ANY cohort has 2 consecutive hours of the SAME theory subject on the same day!
echo "\n================ STUDENT BACK-TO-BACK SAME SUBJECT CHECK ================\n";
$grp_sub_grid = [];
foreach ($schedule_entries as $entry) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
    $grp_sub_grid[$g_clean][$entry['day_id']][$entry['time_s_id']] = $entry['sub_id'];
}
$violating_groups = 0;
foreach ($grp_sub_grid as $grp => $d_map) {
    foreach ($d_map as $d => $s_map) {
        for ($s = 1; $s <= 6; $s++) {
            if (isset($s_map[$s]) && isset($s_map[$s+1]) && $s_map[$s] == $s_map[$s+1]) {
                $sid = $s_map[$s];
                foreach ($subjects as $sub) {
                    if ($sub['sub_id'] == $sid && in_array($sub['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
                        echo "VIOLATION: Group $grp has duplicate subject {$sub['sub_code']} back-to-back on Day $d in slots $s and " . ($s+1) . "\n";
                        $violating_groups++;
                    }
                }
            }
        }
    }
}
if ($violating_groups === 0) {
    echo "SUCCESS: Zero instances of the same theory subject scheduled back-to-back! Every day is diversified.\n";
}
