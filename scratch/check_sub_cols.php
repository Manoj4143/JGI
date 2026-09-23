<?php
require_once __DIR__ . '/../includes/DBConnection.php';
$r = mysqli_query($conn, 'SELECT DISTINCT dept_id, sem_id, group_id, cys FROM subjects');
while ($row = mysqli_fetch_assoc($r)) {
    print_r($row);
}
