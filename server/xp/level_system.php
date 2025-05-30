<?php
function getXpForNextLevel(int $level): int {
    $baseXp = 100;   
    $step = 50;      

    return $baseXp + ($level - 1) * $step;
}
function checkAndUpdateLevel(int $userId, PDO $pdo): void {
    $stmt = $pdo->prepare("SELECT experience_points, level FROM user_progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$progress) {
        $stmtInsert = $pdo->prepare("INSERT INTO user_progress (user_id, experience_points, level) VALUES (?, 0, 1)");
        $stmtInsert->execute([$userId]);
        return;
    }

    $currentExp = (int)$progress['experience_points'];
    $currentLevel = (int)$progress['level'];

    $xpForNextLevel = getXpForNextLevel($currentLevel);

    if ($currentExp >= $xpForNextLevel) {
        $currentLevel++;

        $stmtUpdate = $pdo->prepare("UPDATE user_progress SET level = ? WHERE user_id = ?");
        $stmtUpdate->execute([$currentLevel, $userId]);
    }
}
function addExperiencePoints(int $userId, int $pointsToAdd, PDO $pdo): void {
    $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, experience_points, level) VALUES (?, ?, 1)
                           ON DUPLICATE KEY UPDATE experience_points = experience_points + ?");
    $stmt->execute([$userId, $pointsToAdd, $pointsToAdd]);
    checkAndUpdateLevel($userId, $pdo);
}
?>
