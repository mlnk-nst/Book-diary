<?php
require_once __DIR__ . '/../database.php';
header('Content-Type: application/json');

if (!isset($_GET['session_id'])) {
    echo json_encode(['error' => 'ID сеансу не надано']);
    exit;
}

$sessionId = $_GET['session_id'];
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'Не автентифіковано']);
    exit;
}

$stmt = $pdo->prepare("SELECT start_time FROM reading_sessions WHERE id = ? AND user_id = ?");
$stmt->execute([$sessionId, $userId]);
$session = $stmt->fetch();

if (!$session) {
    echo json_encode(['error' => 'Сеанс не знайдено']);
    exit;
}

$startTime = new DateTime($session['start_time']);
$currentTime = new DateTime();
$interval = $currentTime->diff($startTime);

echo json_encode([
    'hours' => $interval->h,
    'minutes' => $interval->i,
    'seconds' => $interval->s,
    'success' => true
]);
?>