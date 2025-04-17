<?php
session_start();
require_once 'config.php';

// Cek apakah user login
if (!isset($_SESSION['user_id'])) {
    header("Location: log.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cek apakah user sudah tergabung dalam tim atau solo
    $stmt = $pdo->prepare("SELECT team_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || (!$user['team_id'])) {
        // Belum tergabung dalam tim atau solo
        header("Location: choose_team.php");
        exit;
    }

    // Jika sudah tergabung, lanjut ke laman pembayaran dan upload syarat
    header("Location: submit_requirement.php");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
