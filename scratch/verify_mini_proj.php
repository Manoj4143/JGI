<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT sc.*, s.sub_code, s.sub_name, s.subject_type, p.teacher_name, r.room_name 
                          FROM sched sc 
                          JOIN subjects s ON sc.sub_id=s.sub_id 
                          JOIN profile p ON sc.teacher_id=p.teacher_id 
                          JOIN room r ON sc.room_id=r.room_id 
                          WHERE s.subject_type = 'MINI_PROJECT' 
                          ORDER BY sc.day_id, sc.time_s_id");
echo "MINI PROJECT SESSIONS:\n";
while ($row = mysqli_fetch_assoc($r)) {
    echo "Day: {$row['day_id']} | Slots: {$row['time_s_id']} - {$row['time_e_id']} | Sub: {$row['sub_code']} ({$row['sub_name']}) | Group: {$row['group_name']} | Room: {$row['room_name']}\n";
}
