<?php
require_once __DIR__ . '/DBConnection.php';

$rooms = [
    [1, 'Lab 1 (Room 311)', 'Computer Lab 1', 1, 60],
    [2, 'Lab 2 (Room 312)', 'Computer Lab 2', 1, 60],
    [4, 'Room 217', 'Lecture Hall', 0, 80],
    [14, 'SF212', 'Lecture Room', 0, 80]
];

foreach ($rooms as $rm) {
    $check = mysqli_query($conn, "SELECT room_id FROM room WHERE room_id = {$rm[0]}");
    if ($check && mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE room SET room_name = '{$rm[1]}', room_desc = '{$rm[2]}', room_isNKN = {$rm[3]}, room_size = {$rm[4]} WHERE room_id = {$rm[0]}");
    } else {
        mysqli_query($conn, "INSERT INTO room (room_id, room_name, room_desc, room_isNKN, room_size) VALUES ({$rm[0]}, '{$rm[1]}', '{$rm[2]}', {$rm[3]}, {$rm[4]})");
    }
}

$res = mysqli_query($conn, "SELECT * FROM room");
while ($r = mysqli_fetch_assoc($res)) {
    echo json_encode($r) . PHP_EOL;
}
