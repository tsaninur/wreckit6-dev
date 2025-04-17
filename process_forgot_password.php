<?php
session_start();
require_once 'config.php';

// Define PHPMailer path
$phpmailer_path = 'lib/PHPMailer/src/';

// Check if PHPMailer files exist
if (!file_exists($phpmailer_path . 'PHPMailer.php') ||
    !file_exists($phpmailer_path . 'SMTP.php') ||
    !file_exists($phpmailer_path . 'Exception.php')) {
    $_SESSION['error'] = "PHPMailer tidak ditemukan di $phpmailer_path.";
    header("Location: forgot_password.php");
    exit;
}

// Include PHPMailer manually
require $phpmailer_path . 'PHPMailer.php';
require $phpmailer_path . 'SMTP.php';
require $phpmailer_path . 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Log start of script
error_log("Starting process_forgot_password.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode tidak diizinkan.";
    header("Location: forgot_password.php");
    exit;
}

// Get and validate email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    $_SESSION['error'] = "Email tidak valid.";
    header("Location: forgot_password.php");
    exit;
}

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful");

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Email check completed: " . ($user ? "User found" : "User not found"));

    if ($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        error_log("Token generated: $token");

        // Store token in users table
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user['id']]);
        error_log("Token stored in database");

        // Generate reset link
        $reset_link = "https://wreckit.id/wreckit6-dev/reset_password.php?token=" . urlencode($token);
        error_log("Reset link: $reset_link");

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Enable debugging
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer debug [$level]: $str");
            };

            // SMTP settings from cPanel
            $mail->isSMTP();
            $mail->Host = 'mail.wreckit.id';
            $mail->SMTPAuth = true;
            $mail->Username = 'no-reply@wreckit.id';
            $mail->Password = '{o!!T9H&Ouoc';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Email details
            $mail->setFrom('no-reply@wreckit.id', 'Wreckit CTF');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password - Wreckit CTF';

            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; background-color: #ffffff; border: 1px solid #ddd;">
                    <img src="https://wreckit.id/wreckit6-dev/banner-top.png" alt="Banner Atas Wreck It 6" style="width: 100%; max-width: 100%; height: auto;">

                    <h2 style="text-align: center;"> Wreck It 6.0 - Reset Password</h2>

                    <p>Dear Participant,</p>
                    <p>We received a request to reset your password. If this was you, click the link below to reset it:</p>

                    <p style="text-align: center; margin: 20px 0;">
                        <a href="' . $reset_link . '" style="display: inline-block; background-color: #007BFF; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;">Reset Your Password</a>
                    </p>

                    <p>If the button above does not work, copy and paste the link below into your browser:</p>
                    <p><a href="' . $reset_link . '">' . $reset_link . '</a></p>

                    <p><strong>Note:</strong> This link will expire in 1 hour for security purposes.</p>

                    <hr style="margin: 30px 0;">

                    <p>🎯 Join our Discord server for real-time updates and coordination:</p>
                    <p><a href="https://discord.gg/wreckit">https://discord.gg/wreckit</a></p>

                    <p>📢 Stay updated by following our official channels or reach out to us directly:</p>

                    <table style="width: 100%; border: 1px solid #ccc; border-collapse: collapse; margin-top: 10px;">
                        <tr><td style="padding: 8px;">📸 Instagram: <a href="https://instagram.com/wreckit.id">@wreckit.id</a></td></tr>
                        <tr><td style="padding: 8px;">📸 Instagram: <a href="https://instagram.com/korpstar.poltekssn">@korpstar.poltekssn</a></td></tr>
                        <tr><td style="padding: 8px;">📧 Email: <a href="mailto:admin@wreckit.id">admin@wreckit.id</a></td></tr>
                        <tr><td style="padding: 8px;">📱 WhatsApp/Telegram: +62 858 25506613 (Ivhan) | +62 812 39976545 (Iska)</td></tr>
                    </table>

                    <p style="text-align: center; margin-top: 20px;">We can’t wait to see your team in action at Wreck It 6.0! 🔥</p>

                    <img src="https://wreckit.id/wreckit6-dev/banner-bottom.png" alt="Banner Bawah Wreck It 6" style="width: 100%; max-width: 100%; height: auto;">
                </div>
            ';

            $mail->AltBody = "Halo,\n\nKlik link berikut untuk mereset password Anda:\n$reset_link\n\nLink ini berlaku selama 1 jam.";

            error_log("Attempting to send email to $email");
            $mail->send();
            error_log("Email sent successfully to $email");
            $_SESSION['message'] = "Link reset kata sandi telah dikirim ke email Anda.";
        } catch (Exception $e) {
            error_log("PHPMailer error: " . $mail->ErrorInfo);
            $_SESSION['error'] = "Gagal mengirim email: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['message'] = "Jika email terdaftar, link reset akan dikirim.";
        error_log("Email not found in database");
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat memproses permintaan.";
}

header("Location: forgot_password.php");
exit;
?>
