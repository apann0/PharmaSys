<?php
/**
 * Delete Medicine – Handles POST deletion with CSRF validation.
 */
require_once __DIR__ . '/../../includes/auth_check.php';

$pdo = getDBConnection();

$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request token.');
        header('Location: list.php');
        exit;
    }

    // Get medicine name for logging
    $stmt = $pdo->prepare('SELECT name FROM medicines WHERE id = ?');
    $stmt->execute([$id]);
    $med = $stmt->fetch();

    if ($med) {
        $del = $pdo->prepare('DELETE FROM medicines WHERE id = ?');
        $del->execute([$id]);
        logAction($_SESSION['user_id'], 'DELETE_MEDICINE', "Deleted medicine: {$med['name']}");
        setFlash('success', "Medicine \"{$med['name']}\" deleted.");
    } else {
        setFlash('error', 'Medicine not found.');
    }
} else {
    setFlash('error', 'Invalid request.');
}

header('Location: list.php');
exit;
