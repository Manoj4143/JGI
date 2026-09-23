<?php
require_once __DIR__ . '/find_perfect_143.php';

$res = run_scheduler(2);

echo "\n================= VALIDATION RESULTS FOR SEED 2 =================\n";
echo "Total sessions scheduled: " . $res['scheduled'] . "\n";
echo "Missing sessions: " . $res['missing'] . "\n";

$sub_map = [];
$type_map = [];
foreach ($res['subjects'] as $s) {
    $sub_map[$s['sub_id']] = $s['sub_code'];
    $type_map[$s['sub_id']] = $s['subject_type'];
}

$dnames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];

// 1. Group 75 Timetable
echo "\n================ GROUP 75 (5th Sem AIML) ================\n";
$grid75 = [];
foreach ($res['entries'] as $entry) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
    if ($g_clean === '75') {
        $grid75[$entry['day_id']][$entry['time_s_id']] = $sub_map[$entry['sub_id']];
    }
}
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $code = isset($grid75[$d][$s]) ? $grid75[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-8s] ", $s, $code);
    }
    echo $line . "\n";
}

// 2. Teacher 34 Timetable
echo "\n================ TEACHER 34 SCHEDULE ================\n";
$t34_grid = [];
foreach ($res['entries'] as $entry) {
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

// 3. Teacher Theory Free Period Check
echo "\n================ TEACHER THEORY GAP VALIDATION (ALL TEACHERS) ================\n";
$t_theory_grid = [];
foreach ($res['entries'] as $entry) {
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
    echo "PERFECT: 100% of teachers have at least 1 free period between theory classes! Zero violations across all teachers.\n";
}

// 4. Student Back-to-Back Same Subject Check
echo "\n================ STUDENT BACK-TO-BACK SAME THEORY SUBJECT CHECK ================\n";
$grp_sub_grid = [];
foreach ($res['entries'] as $entry) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $entry['group_name']);
    $grp_sub_grid[$g_clean][$entry['day_id']][$entry['time_s_id']] = $entry['sub_id'];
}
$violating_groups = 0;
foreach ($grp_sub_grid as $grp => $d_map) {
    foreach ($d_map as $d => $s_map) {
        for ($s = 1; $s <= 6; $s++) {
            if (isset($s_map[$s]) && isset($s_map[$s+1]) && $s_map[$s] == $s_map[$s+1]) {
                $sid = $s_map[$s];
                if (in_array($type_map[$sid], ['THEORY', 'ONE_CREDIT'])) {
                    echo "VIOLATION: Group $grp has duplicate subject {$sub_map[$sid]} back-to-back on Day $d in slots $s and " . ($s+1) . "\n";
                    $violating_groups++;
                }
            }
        }
    }
}
if ($violating_groups === 0) {
    echo "PERFECT: Zero instances of the same theory subject scheduled back-to-back for any student group!\n";
}

// 5. Check gaps for all student cohorts
echo "\n================ STUDENT COHORT CONTIGUITY / GAP CHECK ================\n";
$internal_gaps = 0;
foreach ($grp_sub_grid as $grp => $d_map) {
    foreach ($d_map as $d => $s_map) {
        $slots = array_keys($s_map);
        sort($slots);
        if (count($slots) > 1) {
            for ($i = 0; $i < count($slots) - 1; $i++) {
                // If there's a missing slot between morning slots (1..4)
                if ($slots[$i+1] > $slots[$i] + 1) {
                    // Check if the gap is lunch (between 4 and 5)
                    if ($slots[$i] == 4 && $slots[$i+1] == 5) {
                        // normal
                    } elseif ($slots[$i] < 4 && $slots[$i+1] <= 4) {
                        echo "INTERNAL GAP: Group $grp on Day $d has gap between slot {$slots[$i]} and {$slots[$i+1]}\n";
                        $internal_gaps++;
                    }
                }
            }
        }
    }
}
echo "\n================ GROUP 107 SCHEDULE ================\n";
for ($d = 1; $d <= 6; $d++) {
    echo $dnames[$d] . ": ";
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($grp_sub_grid['107'][$d][$s]) ? $sub_map[$grp_sub_grid['107'][$d][$s]] : '---';
        echo sprintf("[S%d: %-8s] ", $s, $c);
    }
    echo "\n";
}

echo "\n================ GROUP 117 SCHEDULE ================\n";
for ($d = 1; $d <= 6; $d++) {
    echo $dnames[$d] . ": ";
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($grp_sub_grid['117'][$d][$s]) ? $sub_map[$grp_sub_grid['117'][$d][$s]] : '---';
        echo sprintf("[S%d: %-8s] ", $s, $c);
    }
    echo "\n";
}

if ($internal_gaps === 0) {
    echo "PERFECT: All student cohorts have 0 internal morning gaps! Clean compact schedules.\n";
}

