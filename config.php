<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'wrer2578_admin');
define('DB_PASS', '}fZ^qNM6;&*!');
define('DB_NAME', 'wrer2578_wreckit6');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>