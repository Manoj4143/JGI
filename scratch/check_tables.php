<?php
require_once __DIR__ . '/../includes/DBConnection.php';

echo "=== TIMESTART TABLE ===\n";
$r = mysqli_query($conn, 'SELECT * FROM timestart');
while($row = mysqli_fetch_assoc($r)) {
    print_r($row);
}

echo "=== DAY TABLE ===\n";
$r2 = mysqli_query($conn, 'SELECT * FROM day');
while($row = mysqli_fetch_assoc($r2)) {
    print_r($row);
}

echo "=== TIMEEND TABLE (if exists) ===\n";
$r3 = mysqli_query($conn, 'SHOW TABLES LIKE "%time%"');
while($row = mysqli_fetch_row($r3)) {
    echo $row[0] . "\n";
}
