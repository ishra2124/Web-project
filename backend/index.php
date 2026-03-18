<?php
// SkillBridge Backend Entry Point
header('Content-Type: application/json');

echo json_encode([
    'project' => 'SkillBridge Backend API',
    'version' => '1.0.0',
    'status' => 'active',
    'endpoints' => [
        'auth' => '/api/auth/',
        'admin' => '/api/admin/',
        'freelancer' => '/api/freelancer/',
        'client' => '/api/client/',
        'project' => '/api/project/',
        'chat' => '/api/chat/'
    ]
]);
?>
