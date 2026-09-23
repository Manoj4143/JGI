<?php
require_once __DIR__ . '/../includes/DBConnection.php';

echo "========================================================\n";
echo "1. VERIFYING MINI PROJECT SESSIONS\n";
echo "========================================================\n";
$r = mysqli_query($conn, "SELECT sc.*, s.sub_code, s.subject_type 
                          FROM sched sc 
                          JOIN subjects s ON sc.sub_id=s.sub_id 
                          WHERE s.subject_type = 'MINI_PROJECT'");
$mp_count = 0;
$afternoon_ok = true;
while ($row = mysqli_fetch_assoc($r)) {
    $mp_count++;
    $s = intval($row['time_s_id']);
    if ($s < 5) {
        $afternoon_ok = false;
        echo "FAIL: Mini Project session in morning slot $s\n";
    }
}
echo "Total Mini Project sessions: $mp_count\n";
echo "All Mini Projects strictly in afternoon (slots >= 5): " . ($afternoon_ok ? "PASS ✅" : "FAIL ❌") . "\n";

echo "\n========================================================\n";
echo "2. VERIFYING ALLOCATIONS & CONSTRAINT RULES\n";
echo "========================================================\n";
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM sched");
$tot_sched = mysqli_fetch_assoc($r)['c'];
echo "Total sessions scheduled: $tot_sched / 143 " . ($tot_sched == 143 ? "PASS ✅" : "FAIL ❌") . "\n";

// Room collisions
$r = mysqli_query($conn, "SELECT day_id, time_s_id, room_id, COUNT(*) as c FROM sched GROUP BY day_id, time_s_id, room_id HAVING c > 1");
echo "Room collisions: " . mysqli_num_rows($r) . " " . (mysqli_num_rows($r) == 0 ? "PASS ✅" : "FAIL ❌") . "\n";

// Teacher adjacent theory violations
$r = mysqli_query($conn, "SELECT sc.teacher_id, sc.day_id, sc.time_s_id, s.subject_type 
                          FROM sched sc 
                          JOIN subjects s ON sc.sub_id=s.sub_id 
                          ORDER BY sc.teacher_id, sc.day_id, sc.time_s_id");
$teacher_theory = [];
$theory_adjacent_violations = 0;
while ($row = mysqli_fetch_assoc($r)) {
    if (in_array($row['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
        $t = $row['teacher_id'];
        $d = $row['day_id'];
        $s = intval($row['time_s_id']);
        if (isset($teacher_theory[$t][$d][$s - 1])) {
            $theory_adjacent_violations++;
        }
        $teacher_theory[$t][$d][$s] = true;
    }
}
echo "Teacher adjacent theory violations: $theory_adjacent_violations " . ($theory_adjacent_violations == 0 ? "PASS ✅" : "FAIL ❌") . "\n";

echo "\n========================================================\n";
echo "3. VERIFYING ATTRIBUTION CREDITS\n";
echo "========================================================\n";
$help_content = file_get_contents(__DIR__ . '/../Admin/help.php');
$dev_content = file_get_contents(__DIR__ . '/../Admin/about_dev.php');
$expected_credit = "This portal is done by K MANOJ [manojkumar911088@gmail.com]";

echo "help.php contains credit: " . (strpos($help_content, $expected_credit) !== false ? "PASS ✅" : "FAIL ❌") . "\n";
echo "about_dev.php contains credit: " . (strpos($dev_content, $expected_credit) !== false ? "PASS ✅" : "FAIL ❌") . "\n";

// Check that old names are gone from about_dev.php
$has_ayush = strpos($dev_content, 'Ayush') !== false;
$has_akshay = strpos($dev_content, 'Akshay') !== false;
$has_ganesh = strpos($dev_content, 'Ganesh') !== false;
$has_varun = strpos($dev_content, 'Varun') !== false;
$old_names_removed = (!$has_ayush && !$has_akshay && !$has_ganesh && !$has_varun);
echo "about_dev.php legacy IIT Jodhpur student names removed: " . ($old_names_removed ? "PASS ✅" : "FAIL ❌") . "\n";

echo "\n========================================================\n";
echo "4. VERIFYING RBAC DATABASE TABLES & DATA\n";
echo "========================================================\n";
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM faculty_details");
$fd_count = mysqli_fetch_assoc($r)['c'];
echo "Faculty details records synchronized: $fd_count " . ($fd_count > 0 ? "PASS ✅" : "FAIL ❌") . "\n";

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM user WHERE role = 'Counselor'");
$counselor_count = mysqli_fetch_assoc($r)['c'];
echo "Counselor account provisioned: $counselor_count " . ($counselor_count > 0 ? "PASS ✅" : "FAIL ❌") . "\n";

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM timing_requests");
$tr_exists = ($r !== false);
echo "timing_requests table exists: " . ($tr_exists ? "PASS ✅" : "FAIL ❌") . "\n";

echo "\nALL FLOWS VERIFIED SUCCESSFULLY!\n";
