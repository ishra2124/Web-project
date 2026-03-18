<?php
// Approve Proposal API (Client)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$client_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->proposal_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing proposal ID']);
    exit;
}

$proposal_id = $data->proposal_id;

try {
    $conn->beginTransaction();

    // 1. Get Proposal Details
    $stmt = $conn->prepare("SELECT p.job_id, p.freelancer_id, j.client_id FROM proposals p JOIN jobs j ON p.job_id = j.job_id WHERE p.proposal_id = ?");
    $stmt->execute([$proposal_id]);
    $proposal = $stmt->fetch();

    if (!$proposal) {
        throw new Exception("Proposal not found");
    }

    if ($proposal['client_id'] !== $client_id) {
        throw new Exception("Unauthorized to approve this proposal");
    }

    // 2. Update Proposal Status
    $stmt = $conn->prepare("UPDATE proposals SET status = 'accepted' WHERE proposal_id = ?");
    $stmt->execute([$proposal_id]);

    // 3. Reject other proposals for the same job (optional, depending on business logic - keeping simple for now)
    
    // 4. Create Project
    $stmt = $conn->prepare("INSERT INTO projects (job_id, freelancer_id, client_id, status) VALUES (?, ?, ?, 'active')");
    $stmt->execute([$proposal['job_id'], $proposal['freelancer_id'], $client_id]);

    // 5. Update Job Status
    $stmt = $conn->prepare("UPDATE jobs SET status = 'in_progress' WHERE job_id = ?");
    $stmt->execute([$proposal['job_id']]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Proposal approved and project started']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
