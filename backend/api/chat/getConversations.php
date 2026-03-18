<?php
// Get Conversations API (Chat)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Basic logic: Find unique users I exchanged messages with
    // We get the most recent message for each conversation
    $sql = "
        SELECT 
            u.user_id, 
            u.full_name, 
            u.profile_image, 
            m.message, 
            m.sent_at,
            (m.receiver_id = ?) as is_received
        FROM messages m
        JOIN users u ON (m.sender_id = u.user_id OR m.receiver_id = u.user_id)
        WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.user_id != ?
        AND m.message_id IN (
            SELECT MAX(message_id) 
            FROM messages 
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
        )
        ORDER BY m.sent_at DESC
    ";

    // Optimization: The subquery above is complex.
    // Simplified approach for academic project:
    // Just get list of users who messaged me or I messaged.
    
    $stmt = $conn->prepare("
        SELECT DISTINCT u.user_id, u.full_name, u.role
        FROM users u
        JOIN messages m ON (m.sender_id = u.user_id OR m.receiver_id = u.user_id)
        WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.user_id != ?
    ");
    
    $stmt->execute([$user_id, $user_id, $user_id]);
    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $users]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
