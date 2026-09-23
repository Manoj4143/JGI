<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, "SELECT * FROM user WHERE username = 'admin'");
print_r(mysqli_fetch_assoc($r));
