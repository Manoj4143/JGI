<?php
require_once __DIR__ . '/../includes/DBConnection.php';

echo "=== COURSE TABLE ===\n";
$r = mysqli_query($conn, 'SELECT * FROM course');
while ($row = mysqli_fetch_assoc($r)) {
    echo "course_id: {$row['course_id']} | course_yrSec: {$row['course_yrSec']} | dept_id: {$row['dept_id']} | major: {$row['major']}\n";
}

echo "\n=== SCHOOL_YR TABLE ===\n";
$r = mysqli_query($conn, 'SELECT * FROM school_yr');
while ($row = mysqli_fetch_assoc($r)) {
    echo "year_id: {$row['year_id']} | school_year: {$row['school_year']}\n";
}

echo "\n=== SEM TABLE ===\n";
$r = mysqli_query($conn, 'SELECT * FROM sem');
while ($row = mysqli_fetch_assoc($r)) {
    echo "sem_id: {$row['sem_id']} | semester: {$row['semester']}\n";
}

echo "\n=== SUBJECTS COURSE_ID & SEM_ID ===\n";
$r = mysqli_query($conn, 'SELECT sub_id, sub_code, course_id, dept_id, sem_id, group_id FROM subjects LIMIT 10');
while ($row = mysqli_fetch_assoc($r)) {
    print_r($row);
}
