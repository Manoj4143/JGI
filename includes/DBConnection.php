<?php  
	require_once __DIR__ . '/php8_compat.php';
	$conn = @mysqli_connect('127.0.0.1', 'root', 'Manojkumar#098', 'last1');
	if (!$conn) {
		$conn = @mysqli_connect('localhost', 'root', '', 'last1');
	}
	if (!$conn) {
		die('Could not connect: ' . mysqli_connect_error());
	}
	mysqli_select_db($conn, "last1");
	@mysqli_query($conn, "SET sql_mode = ''");
	$GLOBALS['conn'] = $conn;
?>