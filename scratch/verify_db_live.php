<?php
require_once __DIR__ . '/../includes/DBConnection.php';

echo "================ DATABASE 'sched' TABLE VERIFICATION ================\n";
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM sched");
$total = mysqli_fetch_assoc($res)['c'];
echo "Total rows in sched table: $total\n";

// Fetch subjects mapping
$sub_map = [];
$type_map = [];
$r_sub = mysqli_query($conn, "SELECT * FROM subjects");
while ($s = mysqli_fetch_assoc($r_sub)) {
    $sub_map[$s['sub_id']] = $s['sub_code'];
    $type_map[$s['sub_id']] = $s['subject_type'];
}

// 1. Group 75 schedule from DB
echo "\n--- Group 75 (5th Sem AIML) from MySQL ---\n";
$grid75 = [];
$r75 = mysqli_query($conn, "SELECT * FROM sched WHERE group_name LIKE '75%' ORDER BY day_id, time_s_id");
while ($row = mysqli_fetch_assoc($r75)) {
    $grid75[$row['day_id']][$row['time_s_id']] = $sub_map[$row['sub_id']];
}
$dnames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($grid75[$d][$s]) ? $grid75[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-8s] ", $s, $c);
    }
    echo $line . "\n";
}

// 2. Teacher 34 schedule from DB
echo "\n--- Teacher 34 Schedule from MySQL ---\n";
$grid34 = [];
$r34 = mysqli_query($conn, "SELECT * FROM sched WHERE teacher_id = 34 ORDER BY day_id, time_s_id");
while ($row = mysqli_fetch_assoc($r34)) {
    $grid34[$row['day_id']][$row['time_s_id']] = $sub_map[$row['sub_id']] . " (" . $type_map[$row['sub_id']] . ")";
}
foreach ($dnames as $d => $dn) {
    $line = sprintf("%-4s: ", $dn);
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($grid34[$d][$s]) ? $grid34[$d][$s] : '---';
        $line .= sprintf("[Slot %d: %-22s] ", $s, $c);
    }
    echo $line . "\n";
}

// 3. Teacher theory gap validation across ALL teachers from DB
echo "\n--- Teacher Theory Gap Validation (All Faculty) ---\n";
$t_theory = [];
$all_sched = mysqli_query($conn, "SELECT * FROM sched");
while ($row = mysqli_fetch_assoc($all_sched)) {
    if (isset($type_map[$row['sub_id']]) && $type_map[$row['sub_id']] === 'THEORY') {
        $t_theory[$row['teacher_id']][$row['day_id']][$row['time_s_id']] = true;
    }
}
$violating_teachers = 0;
foreach ($t_theory as $tid => $d_map) {
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
    echo "SUCCESS: 100% of teachers have at least 1 free period between theory classes! Zero violations in DB.\n";
}

// 4. Student back-to-back same theory subject check from DB
echo "\n--- Student Back-to-Back Same Theory Subject Check ---\n";
$grp_sub = [];
$all_sched2 = mysqli_query($conn, "SELECT * FROM sched");
while ($row = mysqli_fetch_assoc($all_sched2)) {
    $g_clean = preg_replace('/\s*\(Batch\s*\d+\)$/', '', $row['group_name']);
    $grp_sub[$g_clean][$row['day_id']][$row['time_s_id']] = [
        'sub_id' => $row['sub_id'],
        'type' => $type_map[$row['sub_id']]
    ];
}
$violating_groups = 0;
foreach ($grp_sub as $grp => $d_map) {
    foreach ($d_map as $d => $s_map) {
        for ($s = 1; $s <= 6; $s++) {
            if (isset($s_map[$s]) && isset($s_map[$s+1])) {
                if ($s_map[$s]['sub_id'] == $s_map[$s+1]['sub_id'] && in_array($s_map[$s]['type'], ['THEORY', 'ONE_CREDIT'])) {
                    echo "VIOLATION: Group $grp has duplicate theory subject in slots $s and " . ($s+1) . " on Day $d\n";
                    $violating_groups++;
                }
            }
        }
    }
}
if ($violating_groups === 0) {
    echo "SUCCESS: Zero duplicate same theory subject scheduled back-to-back for any cohort!\n";
}
