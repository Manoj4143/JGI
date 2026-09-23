<?php
require_once __DIR__ . '/../includes/DBConnection.php';

echo "=== USER TABLE SCHEMA ===\n";
$r = mysqli_query($conn, 'DESCRIBE user');
while ($row = mysqli_fetch_assoc($r)) {
    echo "{$row['Field']} - {$row['Type']} (Null: {$row['Null']}, Default: {$row['Default']})\n";
}

echo "\n=== PROFILE TABLE SCHEMA ===\n";
$r = mysqli_query($conn, 'DESCRIBE profile');
while ($row = mysqli_fetch_assoc($r)) {
    echo "{$row['Field']} - {$row['Type']} (Null: {$row['Null']}, Default: {$row['Default']})\n";
}

echo "\n=== SAMPLE USERS ===\n";
$r = mysqli_query($conn, 'SELECT * FROM user LIMIT 10');
while ($row = mysqli_fetch_assoc($r)) {
    print_r($row);
}
