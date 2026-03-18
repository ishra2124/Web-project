<?php
// Get Ratings API (Freelancer)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT r.rating, r.comment, r.created_at, p.title as project_title, u.full_name as client_name
        FROM reviews r
        JOIN projects pr ON r.project_id = pr.project_id
        JOIN jobs p ON pr.job_id = p.job_id
        JOIN users u ON r.reviewer_id = u.user_id
        WHERE r.reviewee_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $reviews]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
