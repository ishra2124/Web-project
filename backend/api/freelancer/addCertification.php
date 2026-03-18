<?php
// Add Certification API (Freelancer)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->title)) {
    echo json_encode(['success' => false, 'message' => 'Missing title']);
    exit;
}

$title = trim($data->title);
$issuer = isset($data->issuer) ? trim($data->issuer) : null;
$year = isset($data->year) ? intval($data->year) : null;
$certificate_path = isset($data->certificate_path) ? trim($data->certificate_path) : null;

try {
    $stmt = $conn->prepare("INSERT INTO certifications (freelancer_id, title, issuer, year, certificate_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $issuer, $year, $certificate_path]);

    echo json_encode(['success' => true, 'message' => 'Certification added successful']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
