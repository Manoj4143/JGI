<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

setcookie('ID_my_site', '', time() - 3600, '/');
setcookie('Key_my_site', '', time() - 3600, '/');

header("Location: ../Admin/index.php");
exit;
?>