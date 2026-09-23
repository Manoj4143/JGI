<?php
require_once __DIR__ . '/../includes/DBConnection.php';

$groups = ['91', '75', '85', '107', '117'];

foreach ($groups as $grp) {
    echo "\n=======================================================\n";
    echo "TIMETABLE GRID FOR GROUP $grp\n";
    echo "=======================================================\n";
    $grid = [];
    $q = mysqli_query($conn, "SELECT s.*, sub.sub_code, sub.subject_type, p.teacher_name 
                             FROM sched s 
                             JOIN subjects sub ON s.sub_id = sub.sub_id 
                             JOIN profile p ON s.teacher_id = p.teacher_id 
                             WHERE s.group_name = '$grp' OR s.group_name LIKE '$grp (%'
                             ORDER BY s.day_id, s.time_s_id");
    while ($r = mysqli_fetch_assoc($q)) {
        $grid[$r['day_id']][$r['time_s_id']][] = $r['sub_code'] . '(' . $r['subject_type'] . ' - ' . $r['group_name'] . ')';
    }

    $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
    $slots = [1 => '09-10', 2 => '10-11', 3 => '11:15-12:15', 4 => '12:15-01:15', 5 => '02-03', 6 => '03-04', 7 => '04-05'];

    printf("%-5s | %-15s | %-15s | %-15s | %-15s | %-15s | %-15s | %-15s\n", "Day", "Slot 1", "Slot 2", "Slot 3", "Slot 4", "Slot 5", "Slot 6", "Slot 7");
    echo str_repeat("-", 125) . "\n";
    foreach ($days as $d => $dname) {
        $rowStr = sprintf("%-5s | ", $dname);
        foreach ($slots as $s => $sname) {
            $val = isset($grid[$d][$s]) ? implode(',', $grid[$d][$s]) : '--- GAP ---';
            // truncate for readability
            if (strlen($val) > 15) $val = substr($val, 0, 15);
            $rowStr .= sprintf("%-15s | ", $val);
        }
        echo $rowStr . "\n";
    }
}
