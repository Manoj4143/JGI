<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
error_reporting( error_reporting() & ~E_NOTICE );

if (empty($_SESSION['is']['username'])) {
    header("Location: /index.php");
    exit;
}
$user = $_SESSION['is']['username'];
if (!$user) { 
    header("Location: /index.php");
    exit;
}
?>