
<?php
header('Content-Type: application/json');

$response = [
    'isLoggedIn' => false,
    'userRole' => 'guest',
    'lastLogin' => null
];

if (isset($_COOKIE[session_name()])) {
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        $response['isLoggedIn'] = true;
        $response['userRole'] = $_SESSION['role'] ?? 'user';
        $response['lastLogin'] = $_SESSION['login_time'] ?? null;
    }
}

echo json_encode($response);
?>