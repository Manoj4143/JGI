<?php
require_once __DIR__ . '/../includes/DBConnection.php';

$r = mysqli_query($conn, "SELECT p.teacher_id, p.teacher_name, SUM(
    CASE 
        WHEN s.subject_type = 'MAJOR_PROJECT' THEN 6
        WHEN s.subject_type = 'MINI_PROJECT' THEN 4
        ELSE s.sub_lechrsprday
    END
) AS total_hrs, count(*) as sub_count 
FROM subjects s 
JOIN profile p ON s.instructor = p.teacher_id 
GROUP BY p.teacher_id, p.teacher_name 
ORDER BY total_hrs DESC");

while ($row = mysqli_fetch_assoc($r)) {
    echo "Teacher {$row['teacher_id']} ({$row['teacher_name']}): {$row['total_hrs']} hrs/week across {$row['sub_count']} subjects\n";
}
