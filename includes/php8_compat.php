<?php
// PHP 8+ Compatibility Layer for Automated-Time-Table Generator

ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

mysqli_report(MYSQLI_REPORT_OFF);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '1');

// Legacy unquoted constants
if (!defined('PHP_SELF')) define('PHP_SELF', 'PHP_SELF');
if (!defined('ID_my_site')) define('ID_my_site', 'ID_my_site');
if (!defined('Key_my_site')) define('Key_my_site', 'Key_my_site');
if (!defined('MYSQL_NUM')) define('MYSQL_NUM', MYSQLI_NUM);
if (!defined('MYSQL_ASSOC')) define('MYSQL_ASSOC', MYSQLI_ASSOC);
if (!defined('MYSQL_BOTH')) define('MYSQL_BOTH', MYSQLI_BOTH);

// Removed PHP functions
if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc() {
        return false;
    }
}

if (!function_exists('session_register')) {
    function session_register(...$keys) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        foreach ($keys as $key) {
            if (!isset($_SESSION[$key]) && isset($GLOBALS[$key])) {
                $_SESSION[$key] = $GLOBALS[$key];
            }
        }
        return true;
    }
}

if (!function_exists('session_unregister')) {
    function session_unregister(...$keys) {
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
            unset($GLOBALS[$key]);
        }
        return true;
    }
}

if (!function_exists('session_is_registered')) {
    function session_is_registered($key) {
        return isset($_SESSION[$key]);
    }
}

// Database helper & mysql_* polyfill
function _get_mysql_link($link = null) {
    if ($link instanceof mysqli) {
        return $link;
    }
    if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
        return $GLOBALS['conn'];
    }
    // Attempt auto-connect
    $conn = @mysqli_connect('127.0.0.1', 'root', 'Manojkumar#098', 'last1');
    if (!$conn) {
        $conn = @mysqli_connect('localhost', 'root', '', 'last1');
    }
    if ($conn) {
        @mysqli_query($conn, "SET sql_mode = ''");
        $GLOBALS['conn'] = $conn;
        return $conn;
    }
    return null;
}

if (!function_exists('mysql_connect')) {
    function mysql_connect($server = '127.0.0.1', $username = 'root', $password = 'Manojkumar#098') {
        $link = @mysqli_connect($server, $username, $password);
        if ($link) {
            $GLOBALS['conn'] = $link;
        }
        return $link;
    }
}

if (!function_exists('mysql_select_db')) {
    function mysql_select_db($database_name, $link = null) {
        $l = _get_mysql_link($link);
        return $l ? mysqli_select_db($l, $database_name) : false;
    }
}

if (!function_exists('mysql_query')) {
    function mysql_query($query, $link = null) {
        $l = _get_mysql_link($link);
        if (!$l) return false;
        return mysqli_query($l, $query);
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
        if (!$result instanceof mysqli_result) return false;
        return mysqli_fetch_array($result, $result_type);
    }
}

if (!function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc($result) {
        if (!$result instanceof mysqli_result) return false;
        return mysqli_fetch_assoc($result);
    }
}

if (!function_exists('mysql_num_rows')) {
    function mysql_num_rows($result) {
        if (!$result instanceof mysqli_result) return 0;
        return mysqli_num_rows($result);
    }
}

if (!function_exists('mysql_num_fields')) {
    function mysql_num_fields($result) {
        if (!$result instanceof mysqli_result) return 0;
        return mysqli_num_fields($result);
    }
}

if (!function_exists('mysql_fetch_field')) {
    function mysql_fetch_field($result, $field_offset = 0) {
        if (!$result instanceof mysqli_result) return false;
        return mysqli_fetch_field($result);
    }
}

if (!function_exists('mysql_result')) {
    function mysql_result($result, $row, $field = 0) {
        if (!$result instanceof mysqli_result) return false;
        $result->data_seek($row);
        $data = $result->fetch_array();
        return $data[$field] ?? false;
    }
}

if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id($link = null) {
        $l = _get_mysql_link($link);
        return $l ? mysqli_insert_id($l) : 0;
    }
}

if (!function_exists('mysql_error')) {
    function mysql_error($link = null) {
        $l = _get_mysql_link($link);
        return $l ? mysqli_error($l) : mysqli_connect_error();
    }
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($string, $link = null) {
        $l = _get_mysql_link($link);
        return $l ? mysqli_real_escape_string($l, $string) : addslashes($string);
    }
}

if (!function_exists('mysql_data_seek')) {
    function mysql_data_seek($result, $row_number) {
        if (!$result instanceof mysqli_result) return false;
        return mysqli_data_seek($result, $row_number);
    }
}

if (!function_exists('mysql_close')) {
    function mysql_close($link = null) {
        $l = _get_mysql_link($link);
        return $l ? mysqli_close($l) : false;
    }
}
