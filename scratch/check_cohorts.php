<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../includes/cohort_helpers.php';

$groups = [75, 85, 91, 107, 117];
foreach ($groups as $g) {
    $info = getCohortDetails($conn, $g);
    print_r($info);
}
