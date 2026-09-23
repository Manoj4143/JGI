<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT * FROM subjects WHERE instructor = 22");
while ($row = mysqli_fetch_assoc($r)) {
    echo "Teacher 22 teaches: {$row['sub_code']} - {$row['sub_name']} (Type: {$row['subject_type']}, Hrs: {$row['sub_lechrsprday']})\n";
}
