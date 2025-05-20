<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'start':
        $book_id = $_POST['book_id'] ?? 0;
        $start_page = $_POST['start_page'] ?? 0;
        
        if (!$book_id || !$start_page) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            exit;
        }

        // Check if there's already an active session
        $stmt = $conn->prepare("SELECT session_id FROM reading_sessions WHERE user_id = ? AND book_id = ? AND is_active = 1");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Active session already exists']);
            exit;
        }

        // Start new session
        $stmt = $conn->prepare("INSERT INTO reading_sessions (user_id, book_id, start_time, start_page, is_active) VALUES (?, ?, NOW(), ?, 1)");
        $stmt->bind_param("iii", $user_id, $book_id, $start_page);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'session_id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to start session']);
        }
        break;

    case 'end':
        $session_id = $_POST['session_id'] ?? 0;
        $end_page = $_POST['end_page'] ?? 0;
        
        if (!$session_id || !$end_page) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            exit;
        }

        // Get session details
        $stmt = $conn->prepare("SELECT start_time, start_page FROM reading_sessions WHERE session_id = ? AND user_id = ? AND is_active = 1");
        $stmt->bind_param("ii", $session_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Session not found or not active']);
            exit;
        }

        $session = $result->fetch_assoc();
        $start_time = new DateTime($session['start_time']);
        $end_time = new DateTime();
        $duration = $end_time->diff($start_time);
        $duration_minutes = ($duration->h * 60) + $duration->i;
        $pages_read = $end_page - $session['start_page'];

        // Update session
        $stmt = $conn->prepare("UPDATE reading_sessions SET end_time = NOW(), end_page = ?, pages_read = ?, duration_minutes = ?, is_active = 0 WHERE session_id = ? AND user_id = ?");
        $stmt->bind_param("iiiii", $end_page, $pages_read, $duration_minutes, $session_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to end session']);
        }
        break;

    case 'get_active':
        $book_id = $_POST['book_id'] ?? 0;
        
        if (!$book_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing book_id']);
            exit;
        }

        $stmt = $conn->prepare("SELECT session_id, start_time, start_page FROM reading_sessions WHERE user_id = ? AND book_id = ? AND is_active = 1");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            echo json_encode(['success' => true, 'session' => $session]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?> 