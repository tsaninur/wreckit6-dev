<?php
session_start();
require_once 'config.php';

// Cek apakah sudah login
//if (isset($_SESSION['user_id'])) {
   // header("Location: dashboard.php"); // Ganti dengan halaman tujuan setelah login
   // exit;
//}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.html"); // Ganti dengan halaman tujuan
            exit;
        } else {
            $error = "Email atau kata sandi salah!";
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTF Login - Wreckit</title>
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

        <div class="form-container">
            <h1 class="form-title">Masuk</h1>
            <p class="form-subtitle">Silahkan masuk ke akun yang telah terdaftar</p>
            
            <?php if(isset($_GET['message'])): ?>
                <p class="success-message"><?php echo htmlspecialchars($_GET['message']); ?></p>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="email@gmail.com" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="Masukkan sandi" required>

                <button type="submit">Masuk</button>
            </form>

            <p class="login-redirect">
                Lupa kata sandi? <a href="forgot_password.php">Klik di sini</a>
            </p>
            <p class="login-redirect">
                Belum Punya Akun? <a href="regis.php">Daftar</a>
            </p>
        </div>
    </div>

    <script src="sidebar_script.js"></script>
    <script src="ctf_script.js"></script>
</body>
</html>