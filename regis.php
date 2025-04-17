<?php
session_start();
require_once 'config.php';

// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Validasi dasar
    if ($password !== $confirm_password) {
        $error = "Sandi tidak cocok!";
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cek apakah email sudah terdaftar
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email sudah terdaftar!";
            } else {
                // Hash password dan simpan ke database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                header("Location: log.php?message=Pendaftaran berhasil! Silakan login.");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTF Registration - Wreckit</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ctf_styles.css">    
    <link rel="stylesheet" href="sidebar_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
</head>
<body>
    <canvas id="matrixCanvas"></canvas>

    <div class="menu-btn" onclick="toggleSidebar()">☰</div>
    <div class="sidebar" id="sidebar">
        <a href="javascript:void(0)" class="close-btn" onclick="toggleSidebar()">×</a>
        <a href="regis.php">Register</a>
        <a href="verification.html">Verify</a>
        <a href="#">Services</a>
        <a href="index.html">Dashboard</a>
    </div>


    <div class="content-container">
        <div class="glow-container">
            <canvas id="glitchCanvas"></canvas>
        </div>

        <div class=" form-container">
            <h1 class="form-title">Daftar</h1>
            <p class="form-subtitle">Silahkan buat akun baru</p>
            
            <?php if(isset($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="username">Nama Pengguna</label>
                <input type="text" id="username" name="username" placeholder="Masukkan nama pengguna" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email@gmail.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

                <label for="password">Sandi</label>
                <input type="password" id="password" name="password" placeholder="Masukkan sandi" required>

                <label for="confirm-password">Konfirmasi Sandi</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Konfirmasi sandi" required>

                <button type="submit">Daftar</button>
            </form>

            <p class="login-redirect">
                Sudah Punya Akun? <a href="log.php">Masuk</a>
            </p>
        </div>
    </div>

    <script src="sidebar_script.js"></script>
    <script src="ctf_script.js"></script>
</body>
</html>
