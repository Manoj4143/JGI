<?php
require_once __DIR__ . '/DBConnection.php';

// 1. Add Saturday to day table
mysqli_query($conn, "INSERT INTO day (day_id, day_name) VALUES (6, 'Saturday') ON DUPLICATE KEY UPDATE day_name = 'Saturday'");
echo "Day table updated with Saturday.\n";

// 2. Update timestart table with new timings
mysqli_query($conn, "TRUNCATE TABLE timestart");
$slots = [
    [1, '09:00 am'],
    [2, '10:00 am'],
    [3, '11:15 am'],
    [4, '12:15 pm'],
    [5, '02:00 pm'],
    [6, '03:00 pm'],
    [7, '04:00 pm'],
    [8, '05:00 pm']
];
foreach ($slots as $s) {
    mysqli_query($conn, "INSERT INTO timestart (time_s_id, time_s) VALUES ({$s[0]}, '{$s[1]}')");
}
echo "Timestart table updated with 09:00 - 17:00 periods.\n";

// 3. Create faculty_timing table if not exists
$create_ft = "CREATE TABLE IF NOT EXISTS faculty_timing (
    timing_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    day_id INT NOT NULL,
    slot_id INT NOT NULL,
    is_available TINYINT DEFAULT 1,
    notes VARCHAR(255) DEFAULT '',
    UNIQUE KEY uq_teacher_slot (teacher_id, day_id, slot_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
mysqli_query($conn, $create_ft);
echo "faculty_timing table verified/created.\n";

// 4. Update BAI586 to MINI_PROJECT
mysqli_query($conn, "UPDATE subjects SET subject_type = 'MINI_PROJECT' WHERE sub_code = 'BAI586'");
echo "BAI586 updated to MINI_PROJECT.\n";
