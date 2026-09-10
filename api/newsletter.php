<?php require_once '../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
    $stmt = db()->prepare('INSERT IGNORE INTO newsletter_subscribers(email) VALUES(?)');
    $stmt->execute([$_POST['email']]);
}
redirect('../index.php');
