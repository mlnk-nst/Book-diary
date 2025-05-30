<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    $response = [
        'isLoggedIn' => false,
        'userRole' => 'guest',
        'userId' => null,
        'lastLogin' => null
    ];

    if (isset($_SESSION['user_id'])) {
        $response['isLoggedIn'] = true;
        $response['userRole'] = $_SESSION['role'] ?? 'user';
        $response['userId'] = $_SESSION['user_id'];
        $response['lastLogin'] = $_SESSION['login_time'] ?? null;
    }

    echo json_encode($response);
    exit;
}