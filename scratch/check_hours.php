<?php
require_once __DIR__ . '/../includes/DBConnection.php';

$r = mysqli_query($conn, "SELECT group_id, SUM(
    CASE 
        WHEN subject_type = 'MAJOR_PROJECT' THEN 6
        WHEN subject_type = 'MINI_PROJECT' THEN 4
        ELSE sub_lechrsprday
    END
) AS total_hrs, count(*) as sub_count FROM subjects GROUP BY group_id");

while ($row = mysqli_fetch_assoc($r)) {
    echo "Group {$row['group_id']}: {$row['total_hrs']} hours/week across {$row['sub_count']} subjects\n";
}
