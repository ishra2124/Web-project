<?php
// Register API
header('Content-Type: application/json');
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get raw POST data
$data = json_decode(file_get_contents("php://input"));

// Validation
if (!isset($data->full_name) || !isset($data->email) || !isset($data->password) || !isset($data->role)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$full_name = trim($data->full_name);
$email = trim($data->email);
$password = $data->password;
$role = $data->role;

// Validate Role
$allowed_roles = ['freelancer', 'client'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

// Check if email already exists
try {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $hashed_password, $role]);
    $user_id = $conn->lastInsertId();

    // Create Profile based on Role
    if ($role === 'freelancer') {
        $stmt = $conn->prepare("INSERT INTO freelancer_profiles (freelancer_id) VALUES (?)");
        $stmt->execute([$user_id]);
    } else if ($role === 'client') {
        $stmt = $conn->prepare("INSERT INTO client_profiles (client_id) VALUES (?)");
        $stmt->execute([$user_id]);
    }

    // Create Wallet
    $stmt = $conn->prepare("INSERT INTO wallet (user_id) VALUES (?)");
    $stmt->execute([$user_id]);

    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
