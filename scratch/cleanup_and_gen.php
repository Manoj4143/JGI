<?php
require_once __DIR__ . '/../includes/DBConnection.php';
mysqli_query($conn, "DELETE FROM subjects WHERE sub_code = 'TEST101'");
echo "TEST101 deleted.\n";
require_once __DIR__ . '/../Admin/generate_tt.php';
echo "Timetable re-generated successfully.\n";
