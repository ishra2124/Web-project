<?php
// Get Users API (Admin)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

// Check if Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role_filter = isset($_GET['role']) ? $_GET['role'] : null;

try {
    $sql = "SELECT user_id, full_name, email, role, status, created_at FROM users";
    $params = [];

    if ($role_filter) {
        $sql .= " WHERE role = ?";
        $params[] = $role_filter;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $users]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
