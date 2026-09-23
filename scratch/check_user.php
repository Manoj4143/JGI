<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "DESCRIBE user");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
echo "\nRecords:\n";
$r2 = mysqli_query($conn, "SELECT * FROM user");
while ($row = mysqli_fetch_assoc($r2)) {
    print_r($row);
}
