<?php
// Get Profile API (Freelancer)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get User and Profile Data
    $stmt = $conn->prepare("
        SELECT u.full_name, u.email, u.profile_image, 
               p.title, p.skills, p.experience_level, p.hourly_rate, p.bio, p.github_link
        FROM users u
        LEFT JOIN freelancer_profiles p ON u.user_id = p.freelancer_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();

    if ($profile) {
        echo json_encode(['success' => true, 'data' => $profile]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Profile not found']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
