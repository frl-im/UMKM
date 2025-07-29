<?php
// check_session.php
require_once 'session_helper.php';

header('Content-Type: application/json');

$response = [
    'valid' => false,
    'user_id' => null,
    'role' => null,
    'message' => ''
];

if (is_logged_in()) {
    $response['valid'] = true;
    $response['user_id'] = $_SESSION['user_id'];
    $response['role'] = $_SESSION['user_role'];
    $response['message'] = 'Session valid';
} else {
    $response['message'] = 'Session expired or invalid';
}

echo json_encode($response);
?>