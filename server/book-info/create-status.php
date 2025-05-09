<?php
session_start(); 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Користувач не авторизований']);
    exit();
}
include __DIR__ . '/../database.php';
$user_id = $_SESSION['user_id'];
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

$book_id = (int)$data->book_id;
$status = $data->status;
$read_data = date('Y-m-d H:i:s'); 

$checkQuery = "SELECT COUNT(*) FROM diary WHERE user_id = ? AND book_id = ?";
$checkStmt = $pdo->prepare($checkQuery);
$checkStmt->execute([$user_id, $book_id]);
$exists = $checkStmt->fetchColumn();

if ($exists > 0) {
    echo json_encode(['success' => false, 'message' => 'Ця книга вже додана до щоденника']);
    exit();
}

$insertQuery = "INSERT INTO diary (user_id, book_id, status, read_data) VALUES (?, ?, ?, ?)";
$insertStmt = $pdo->prepare($insertQuery);
$insertStmt->execute([$user_id, $book_id, $status, $read_data]);
if ($insertStmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Не вдалося зберегти статус']);
}
