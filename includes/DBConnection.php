<?php
require_once __DIR__ . '/php8_compat.php';

// Detect whether running locally on PC or on live InfinityFree hosting
$httpHost = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
$isLocal = (
    strpos($httpHost, 'localhost') !== false ||
    strpos($httpHost, '127.0.0.1') !== false ||
    strpos($httpHost, '10.') === 0 ||
    strpos($httpHost, '192.168.') === 0 ||
    php_sapi_name() === 'cli'
);

$conn = false;

if ($isLocal) {
    // 1. Local PC Database (XAMPP / MySQL)
    $conn = @mysqli_connect('127.0.0.1', 'root', 'Manojkumar#098', 'last1');
    if (!$conn) {
        $conn = @mysqli_connect('localhost', 'root', '', 'last1');
    }
    if ($conn) {
        @mysqli_select_db($conn, 'last1');
    }
} else {
    // 2. InfinityFree Cloud Database (Only accessible when hosted on InfinityFree servers)
    $conn = @mysqli_connect(
        'sql212.infinityfree.com',
        'if0_42991991',
        'manojkumar9110',
        'if0_42991991_jgi',
        3306
    );
    if (!$conn) {
        $conn = @mysqli_connect('localhost', 'if0_42991991', 'manojkumar9110', 'if0_42991991_jgi');
    }
    if ($conn) {
        @mysqli_select_db($conn, 'if0_42991991_jgi');
    }
}

if (!$conn) {
    die('Could not connect to database (' . ($isLocal ? 'Local' : 'Live Host') . '): ' . mysqli_connect_error());
}

@mysqli_query($conn, "SET sql_mode = ''");
$GLOBALS['conn'] = $conn;
?>