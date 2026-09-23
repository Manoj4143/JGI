<?php
session_start();
header('Content-Type: application/json');
if (empty($_SESSION['is']['login']) || $_SESSION['is']['login'] !== TRUE) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['login_image'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}
$file = $_FILES['login_image'];
$allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxBytes = 5 * 1024 * 1024;
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type.']);
    exit;
}
if ($file['size'] > $maxBytes) {
    echo json_encode(['success' => false, 'error' => 'File too large. Max 5MB.']);
    exit;
}
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$destDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
foreach (glob($destDir . 'login_illustration.*') as $old) { @unlink($old); }
$destFile = $destDir . 'login_illustration.' . $ext;
if (move_uploaded_file($file['tmp_name'], $destFile)) {
    echo json_encode(['success' => true, 'url' => '../images/login_illustration.' . $ext, 'message' => 'Updated successfully!']);
} else {
    echo json_encode(['success' => false, 'error' => 'Save failed. Check permissions.']);
}
