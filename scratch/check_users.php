<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, 'SHOW COLUMNS FROM user');
if (!$r) die(mysqli_error($conn));
while($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
$r2 = mysqli_query($conn, 'SELECT * FROM user LIMIT 5');
while($row = mysqli_fetch_assoc($r2)) {
    print_r($row);
}
