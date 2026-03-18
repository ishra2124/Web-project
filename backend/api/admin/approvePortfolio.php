<?php
// Approve Portfolio API (Admin)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

// Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->portfolio_id) || !isset($data->status)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$portfolio_id = $data->portfolio_id;
$status = $data->status; // approved, rejected, changes_requested

$allowed_statuses = ['approved', 'rejected', 'changes_requested'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE portfolios SET status = ? WHERE portfolio_id = ?");
    $stmt->execute([$status, $portfolio_id]);

    echo json_encode(['success' => true, 'message' => 'Portfolio status updated']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
