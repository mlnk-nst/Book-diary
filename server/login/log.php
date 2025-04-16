<?php
ini_set('session.gc_maxlifetime', 1209600); // 14 днів
session_set_cookie_params([
    'lifetime' => 1209600,
    'path' => '/',
    'secure' => true,   
    'httponly' => true,  
    'samesite' => 'Lax' 
]);
session_start(); 

// Припустимо, що користувач ввів правильні дані
$email = $_POST['email'];
$password = $_POST['password'];

// Тут має бути перевірка в БД (наприклад, через PDO)
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Якщо пароль вірний → створюємо сесію
    $_SESSION['user_id'] = $user['id']; // Зберігаємо ID користувача
    $_SESSION['login_time'] = time();   // Час входу (можна використати для перевірки "як давно увійшов")
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Невірний логін або пароль']);
}
?>