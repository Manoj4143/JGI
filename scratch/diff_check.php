<?php
require_once __DIR__ . '/find_perfect_143.php';
$r = run_scheduler(2);
echo "find_perfect_143 run_scheduler(2): count=" . count($r['entries']) . "\n";

// Let's check which subjects are missing in generate_tt:
require_once __DIR__ . '/../includes/DBConnection.php';
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM sched");
echo "sched table count: " . mysqli_fetch_assoc($res)['c'] . "\n";
