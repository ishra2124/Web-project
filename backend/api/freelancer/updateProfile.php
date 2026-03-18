<?php
// Update Profile API (Freelancer)
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"));

// Fields to update
$title = isset($data->title) ? trim($data->title) : null;
$skills = isset($data->skills) ? trim($data->skills) : null;
$experience_level = isset($data->experience_level) ? trim($data->experience_level) : null;
$hourly_rate = isset($data->hourly_rate) ? floatval($data->hourly_rate) : null;
$bio = isset($data->bio) ? trim($data->bio) : null;
$github_link = isset($data->github_link) ? trim($data->github_link) : null;

try {
    $sql = "UPDATE freelancer_profiles SET 
            title = COALESCE(?, title), 
            skills = COALESCE(?, skills), 
            experience_level = COALESCE(?, experience_level), 
            hourly_rate = COALESCE(?, hourly_rate), 
            bio = COALESCE(?, bio), 
            github_link = COALESCE(?, github_link) 
            WHERE freelancer_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$title, $skills, $experience_level, $hourly_rate, $bio, $github_link, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
