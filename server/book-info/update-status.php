<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Користувач не авторизований']);
    exit();
}

include __DIR__ . '/../database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->book_id) || !isset($data->status)) {
    echo json_encode(['success' => false, 'message' => 'Немає необхідних параметрів']);
    exit();
}

$validStatuses = ['Збережено', 'Читаю', 'Прочитано'];
if (!in_array($data->status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Невалідний статус']);
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = (int)$data->book_id;
$status = $data->status;

$query = "UPDATE diary SET status = ? WHERE user_id = ? AND book_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$status, $user_id, $book_id]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Статус не оновлено (можливо, вже такий)']);
}
