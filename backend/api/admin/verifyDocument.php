<?php
// Verify Document API (Admin)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

// Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->doc_id) || !isset($data->status)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$doc_id = $data->doc_id;
$status = $data->status; // approved, rejected
$admin_notes = isset($data->admin_notes) ? $data->admin_notes : '';

if (!in_array($status, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE verification_documents SET status = ?, admin_notes = ? WHERE doc_id = ?");
    $stmt->execute([$status, $admin_notes, $doc_id]);

    echo json_encode(['success' => true, 'message' => 'Document status updated']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
