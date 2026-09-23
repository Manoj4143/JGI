<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, 'SELECT * FROM dept');
while ($row = mysqli_fetch_assoc($r)) {
    print_r($row);
}
