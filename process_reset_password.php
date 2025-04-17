<?php
session_start();
require_once 'config.php';

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode request tidak diizinkan";
    header("Location: reset_password.php");
    exit;
}

// Ambil dan sanitasi data input
$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validasi input
if (empty($token) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "Semua kolom harus diisi";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Kata sandi tidak cocok";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['error'] = "Kata sandi harus minimal 8 karakter";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

try {
    // Setup koneksi database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Set timezone untuk konsistensi
    $pdo->exec("SET time_zone = '+07:00'"); // Sesuaikan dengan timezone Anda
    
    // Debug: Log token yang diterima
    error_log("Token received: " . $token);
    
    // Cek token dengan BINARY comparison untuk case-sensitive
    $stmt = $pdo->prepare("SELECT id, reset_token, reset_token_expiry FROM users WHERE BINARY reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    // Debug: Log hasil query
    error_log("User found: " . print_r($user, true));
    
    if ($user) {
        // Validasi expiry time
        $current_time = new DateTime();
        $expiry_time = new DateTime($user['reset_token_expiry']);
        
        // Debug: Log waktu
        error_log("Current time: " . $current_time->format('Y-m-d H:i:s'));
        error_log("Expiry time: " . $expiry_time->format('Y-m-d H:i:s'));
        
        if ($current_time < $expiry_time) {
            // Update password dan clear token
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $update_stmt->execute([$hashed_password, $user['id']]);
            
            $_SESSION['message'] = "Kata sandi berhasil direset. Silakan login.";
            header("Location: log.php");
            exit;
        } else {
            $_SESSION['error'] = "Token reset password telah kedaluwarsa";
            header("Location: reset_password.php?token=" . urlencode($token));
            exit;
        }
    } else {
        $_SESSION['error'] = "Token reset password tidak valid";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}
?>