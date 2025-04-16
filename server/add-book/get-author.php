<?php
include __DIR__ . '/../database.php';

// Обробка CORS (для AJAX-запитів)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Отримуємо HTTP-метод
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Пошук авторів для Tom Select
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        try {
            $stmt = $pdo->prepare("SELECT id, name FROM authors WHERE name LIKE :search LIMIT 10");
            $stmt->execute([':search' => "%$search%"]);
            $authors = $stmt->fetchAll();
            
            echo json_encode(['data' => $authors]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Помилка бази даних']);
        }
        break;

    case 'POST':
        // Додавання нового автора
        $data = json_decode(file_get_contents('php://input'), true);
        $name = isset($data['name']) ? trim($data['name']) : '';
        
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => "Ім'я автора обов'язкове"]);
            exit;
        }
        
        try {
            // Перевірка на існуючого автора
            $stmt = $pdo->prepare("SELECT id FROM authors WHERE name = :name");
            $stmt->execute([':name' => $name]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                echo json_encode($existing);
            } else {
                // Додавання нового
                $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (:name)");
                $stmt->execute([':name' => $name]);
                
                $id = $pdo->lastInsertId();
                echo json_encode(['id' => $id, 'name' => $name]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Помилка при додаванні автора']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Метод не підтримується']);
}
?>