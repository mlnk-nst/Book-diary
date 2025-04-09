<?php
$host = '127.0.0.1';
$dbname = 'book_diary';
$username = 'root';
$password = '1234567890';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Підключено успішно");
} catch (PDOException $e) {
    error_log("Підключення не вдалося");
}
?>
