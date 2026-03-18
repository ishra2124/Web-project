<?php
// Create Job API (Client)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->title) || !isset($data->description) || !isset($data->budget)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$title = trim($data->title);
$description = trim($data->description);
$budget = floatval($data->budget);
$deadline = isset($data->deadline) ? $data->deadline : null;

try {
    $stmt = $conn->prepare("INSERT INTO jobs (client_id, title, description, budget, deadline) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $budget, $deadline]);

    echo json_encode(['success' => true, 'message' => 'Job posted successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
