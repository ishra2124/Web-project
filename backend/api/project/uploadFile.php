<?php
// Upload File API (Project)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['project_id']) || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Missing project ID or file']);
    exit;
}

$project_id = $_POST['project_id'];
$file = $_FILES['file'];

// Security Checks
$allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/zip'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
    echo json_encode(['success' => false, 'message' => 'File too large (Max 5MB)']);
    exit;
}

// Upload Logic (Placeholder directory, assume 'uploads/' exists or specific path)
$upload_dir = '../../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_name = basename($file['name']);
$target_path = $upload_dir . uniqid() . '_' . $file_name;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    try {
        $stmt = $conn->prepare("INSERT INTO project_files (project_id, uploaded_by, file_name, file_path, file_size) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$project_id, $user_id, $file_name, $target_path, $file['size']]);

        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?>
