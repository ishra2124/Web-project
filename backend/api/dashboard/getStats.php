<?php
// Get Dashboard Stats API
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$stats = [];

try {
    if ($role === 'admin') {
        // Pending Freelancers
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'freelancer' AND status = 'pending'");
        $stats['pending_freelancers'] = $stmt->fetchColumn();

        // Pending Clients
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND status = 'pending'");
        $stats['pending_clients'] = $stmt->fetchColumn();

        // Pending Verifications
        $stmt = $conn->query("SELECT COUNT(*) FROM verification_documents WHERE status = 'pending'");
        $stats['pending_verifications'] = $stmt->fetchColumn();

    } else if ($role === 'freelancer') {
        // Wallet Balance
        $stmt = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['wallet_balance'] = $stmt->fetchColumn() ?: 0;

        // Active Projects
        $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE freelancer_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        $stats['active_projects'] = $stmt->fetchColumn();

        // Completed Projects
        $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE freelancer_id = ? AND status = 'completed'");
        $stmt->execute([$user_id]);
        $stats['completed_projects'] = $stmt->fetchColumn();

        // Rating
        $stmt = $conn->prepare("SELECT AVG(rating) FROM reviews WHERE reviewee_id = ?");
        $stmt->execute([$user_id]);
        $stats['rating'] = number_format($stmt->fetchColumn() ?: 0, 1);

    } else if ($role === 'client') {
        // Active Projects
        $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE client_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        $stats['active_projects'] = $stmt->fetchColumn();

        // Pending Proposals (on my jobs)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM proposals p JOIN jobs j ON p.job_id = j.job_id WHERE j.client_id = ? AND p.status = 'pending'");
        $stmt->execute([$user_id]);
        $stats['pending_proposals'] = $stmt->fetchColumn();
        
        // Total Spent (Simple calculation from completed projects/wallet could go here, for now placeholder)
        $stats['total_spent'] = 0; 
    }

    echo json_encode(['success' => true, 'role' => $role, 'data' => $stats]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
