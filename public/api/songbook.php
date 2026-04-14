<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/user_auth.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if (!is_user_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user = current_user();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action']) || !isset($input['song_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$action = trim((string) $input['action']);
$songId = (int) $input['song_id'];

if ($action === 'toggle') {
    // Check if song exists
    $checkStmt = db()->prepare('SELECT 1 FROM songs WHERE id = ? LIMIT 1');
    $checkStmt->execute([$songId]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Song not found']);
        exit;
    }

    // Check if already in songbook
    $stmt = db()->prepare('SELECT 1 FROM user_songbooks WHERE user_id = ? AND song_id = ? LIMIT 1');
    $stmt->execute([(int) $user['id'], $songId]);
    $exists = (bool) $stmt->fetch();

    if ($exists) {
        // Remove from songbook
        $deleteStmt = db()->prepare('DELETE FROM user_songbooks WHERE user_id = ? AND song_id = ?');
        $deleteStmt->execute([(int) $user['id'], $songId]);
        echo json_encode(['success' => true, 'added' => false]);
    } else {
        // Add to songbook
        $insertStmt = db()->prepare('INSERT INTO user_songbooks (user_id, song_id) VALUES (?, ?)');
        $insertStmt->execute([(int) $user['id'], $songId]);
        echo json_encode(['success' => true, 'added' => true]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
