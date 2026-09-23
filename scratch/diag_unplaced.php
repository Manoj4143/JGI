<?php
require_once __DIR__ . '/test_perfect_scheduler.php';

echo "\n--- DIAGNOSTIC FOR UNPLACED SESSIONS ---\n";
// Check BCS503 (Instructor and constraints)
$s503 = null; $s501 = null;
foreach ($subjects as $s) {
    if ($s['sub_code'] === 'BCS503' && $s['group_id'] == 75) $s503 = $s;
    if ($s['sub_code'] === 'BCS501' && $s['group_id'] == 85) $s501 = $s;
}

echo "BCS503: instructor=" . $s503['instructor'] . "\n";
echo "BCS501: instructor=" . $s501['instructor'] . "\n";

echo "Teacher 22 entries across all days:\n";
foreach ($schedule_entries as $e) {
    if ($e['teacher_id'] == 22) {
        echo "Day " . $e['day_id'] . " S" . $e['time_s_id'] . ": Grp " . $e['group_name'] . ", Sub " . $sub_map[$e['sub_id']] . " (" . $e['subject_type'] . ")\n";
    }
}


// For BCS503, check Tue slot 3 and Sat slot 5
foreach ([[2, 3], [6, 5]] as $pair) {
    $d = $pair[0]; $slot = $pair[1];
    $tid = $s503['instructor'];
    $grp = '75';
    $t_busy = isset($teacher_busy[$tid][$d][$slot]) ? 'busy' : 'free';
    $t_gap = isTeacherTheoryGapOk($tid, $d, $slot, $teacher_theory, $teacher_busy) ? 'gap_ok' : 'gap_FAIL';
    $t_consec = isTeacherConsecutiveOk($tid, $d, $slot, $teacher_busy) ? 'consec_ok' : 'consec_FAIL';
    $s_b2b = isStudentBackToBackSubject($grp, $d, $slot, $s503['sub_id'], $group_busy) ? 'b2b_FAIL' : 'b2b_ok';
    echo "BCS503 on Day $d Slot $slot: t_busy=$t_busy, t_gap=$t_gap, t_consec=$t_consec, s_b2b=$s_b2b\n";
    // Check rooms
    $avail_rooms = 0;
    foreach ($lecRooms as $lr) {
        if (!isset($room_busy[$lr['room_id']][$d][$slot])) $avail_rooms++;
    }
    echo "  avail lec rooms: $avail_rooms\n";
}

// For BCS501 (teacher 34), check Grp 85 slots
echo "\nTeacher 22 schedule:\n";
foreach ($days as $d) {
    echo "Day $d: ";
    for ($s = 1; $s <= 6; $s++) {
        $c = isset($teacher_busy[22][$d][$s]) ? 'X' : '.';
        $th = isset($teacher_theory[22][$d][$s]) ? '(T)' : '';
        echo "S$s:$c$th ";
    }
    echo "\n";
}

echo "\nGroup 85 on Friday slots 5 and 6:\n";
foreach ([5, 6] as $s) {
    $tid = 34; // BCS501 teacher
    $d = 5;
    $tb = isset($teacher_busy[$tid][$d][$s]) ? 'busy' : 'free';
    $tg = isTeacherTheoryGapOk($tid, $d, $s, $teacher_theory, $teacher_busy) ? 'gap_ok' : 'gap_FAIL';
    $tc = isTeacherConsecutiveOk($tid, $d, $s, $teacher_busy) ? 'consec_ok' : 'consec_FAIL';
    echo "Slot $s: tb=$tb, tg=$tg, tc=$tc\n";
}

