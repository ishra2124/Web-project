<?php
// Get Portfolios API (Admin)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

// Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : null;

try {
    $sql = "SELECT p.*, u.full_name, u.email 
            FROM portfolios p 
            JOIN users u ON p.freelancer_id = u.user_id";
    
    $params = [];
    if ($status_filter) {
        $sql .= " WHERE p.status = ?";
        $params[] = $status_filter;
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $portfolios = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $portfolios]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
