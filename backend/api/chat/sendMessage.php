<?php
// Send Message API (Chat)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->receiver_id) || !isset($data->message)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$receiver_id = $data->receiver_id;
$message = trim($data->message);
$project_id = isset($data->project_id) ? $data->project_id : null;

try {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, project_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $project_id, $message]);

    echo json_encode(['success' => true, 'message' => 'Message sent']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
