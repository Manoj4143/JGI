<?php
require_once __DIR__ . '/../includes/DBConnection.php';

// Check room collisions
$r = mysqli_query($conn, "SELECT day_id, time_s_id, room_id, COUNT(*) as c FROM sched GROUP BY day_id, time_s_id, room_id HAVING c > 1");
$room_collisions = mysqli_num_rows($r);

// Check teacher collisions
$r = mysqli_query($conn, "SELECT day_id, time_s_id, teacher_id, COUNT(*) as c FROM sched GROUP BY day_id, time_s_id, teacher_id HAVING c > 1");
$teacher_collisions = mysqli_num_rows($r);

// Check group collisions (excluding batch labs)
$r = mysqli_query($conn, "SELECT day_id, time_s_id, group_name, COUNT(*) as c FROM sched WHERE group_name NOT LIKE '%Batch%' GROUP BY day_id, time_s_id, group_name HAVING c > 1");
$group_collisions = mysqli_num_rows($r);

// Check teacher theory gap (no two adjacent theory periods for any teacher)
$r = mysqli_query($conn, "SELECT sc.teacher_id, sc.day_id, sc.time_s_id, s.subject_type 
                          FROM sched sc 
                          JOIN subjects s ON sc.sub_id=s.sub_id 
                          ORDER BY sc.teacher_id, sc.day_id, sc.time_s_id");
$teacher_theory_slots = [];
$theory_adjacent_violations = 0;
while ($row = mysqli_fetch_assoc($r)) {
    if (in_array($row['subject_type'], ['THEORY', 'ONE_CREDIT'])) {
        $t = $row['teacher_id'];
        $d = $row['day_id'];
        $s = intval($row['time_s_id']);
        if (isset($teacher_theory_slots[$t][$d][$s - 1])) {
            $theory_adjacent_violations++;
            echo "Theory violation for Teacher $t on Day $d: slots " . ($s-1) . " and $s\n";
        }
        $teacher_theory_slots[$t][$d][$s] = true;
    }
}

echo "VERIFICATION RESULTS:\n";
echo "Room collisions: $room_collisions\n";
echo "Teacher collisions: $teacher_collisions\n";
echo "Group collisions: $group_collisions\n";
echo "Teacher adjacent theory violations: $theory_adjacent_violations\n";
