<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$lastLogin = isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'Невідомо';
echo json_encode([
    'isLoggedIn' => $isLoggedIn,
    'lastLogin' => $lastLogin 
]);
?>