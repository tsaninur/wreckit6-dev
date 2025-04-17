<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - Wreckit</title>
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
            <h1 class="form-title">Reset Kata Sandi</h1>
            <p class="form-subtitle">Masukkan kata sandi baru Anda</p>
            
            <?php if(isset($_GET['error'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>

            <form method="POST" action="process_reset_password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                <label for="password">Kata Sandi Baru</label>
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi baru" required>
                <label for="confirm-password">Konfirmasi Kata Sandi</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Konfirmasi kata sandi" required>
                <button type="submit">Simpan</button>
            </form>
        </div>
    </div>

    <script src="sidebar_script.js"></script>
    <script src="ctf_script.js"></script>
</body>
</html>