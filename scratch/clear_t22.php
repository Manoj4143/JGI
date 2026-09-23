<?php
require_once __DIR__ . '/../includes/DBConnection.php';
mysqli_query($conn, "DELETE FROM faculty_timing WHERE teacher_id = 22");
echo "Deleted dummy timings for Teacher 22\n";
