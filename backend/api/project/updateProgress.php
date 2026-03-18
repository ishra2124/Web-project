<?php
// Update Progress API (Project)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->project_id) || !isset($data->progress_percent)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$project_id = $data->project_id;
$progress = intval($data->progress_percent);
$details = isset($data->details) ? trim($data->details) : '';

if ($progress < 0 || $progress > 100) {
    echo json_encode(['success' => false, 'message' => 'Invalid progress value']);
    exit;
}

try {
    // Verify User is part of Project
    $stmt = $conn->prepare("SELECT freelancer_id, client_id FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();

    if (!$project || ($project['freelancer_id'] !== $_SESSION['user_id'] && $project['client_id'] !== $_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access to project']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO project_progress (project_id, progress_percent, details) VALUES (?, ?, ?)");
    $stmt->execute([$project_id, $progress, $details]);

    echo json_encode(['success' => true, 'message' => 'Progress updated successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
