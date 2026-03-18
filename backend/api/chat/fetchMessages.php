<?php
// Fetch Messages API (Chat)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

try {
    $sql = "SELECT m.*, u.full_name as sender_name 
            FROM messages m 
            JOIN users u ON m.sender_id = u.user_id 
            WHERE ";
    $params = [];

    if ($project_id) {
        $sql .= "m.project_id = ?";
        $params[] = $project_id;
    } else if ($other_user_id) {
        $sql .= "((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))";
        $params = [$current_user_id, $other_user_id, $other_user_id, $current_user_id];
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing project_id or user_id']);
        exit;
    }

    $sql .= " ORDER BY m.sent_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $messages]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
