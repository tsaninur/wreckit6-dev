<?php
session_start(); // Start session to access messages from process_forgot_password.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Wreckit</title>
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
        <a href="ctf_index.html">Coming Soon</a>
    </div>

    <div class="content-container">
        <div class="glow-container">
            <canvas id="glitchCanvas"></canvas>
        </div>

        <div class="form-container">
            <h1 class="form-title">Lupa Kata Sandi</h1>
            <p class="form-subtitle">Masukkan email Anda untuk reset kata sandi</p>
            
            <?php if (isset($_SESSION['message'])): ?>
                <p class="success-message"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <form method="POST" action="process_forgot_password.php">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email@gmail.com" required>
                <button type="submit">Kirim Link Reset</button>
            </form>
            
            <p class="login-redirect">
                Kembali ke <a href="log.php">Login</a>
            </p>
        </div>
    </div>

    <script src="sidebar_script.js"></script>
    <script src="ctf_script.js"></script>
</body>
</html>