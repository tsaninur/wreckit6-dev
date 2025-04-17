<?php
session_start();
ini_set('display_errors', 1); // Hapus di produksi
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

function generateInviteCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = trim($_POST['team_name']);
    $user_id = $_SESSION['user_id'];

    if (empty($team_name)) {
        $errors[] = "Nama tim tidak boleh kosong.";
    } else {
        try {
            // Cek apakah nama tim sudah dipakai
            $check = $pdo->prepare("SELECT id FROM teams WHERE name = ?");
            $check->execute([$team_name]);
            if ($check->fetch()) {
                $errors[] = "Nama tim sudah digunakan.";
            } else {
                // Generate unique invite code
                $invite_code = generateInviteCode();
                
                // Cek apakah invite code sudah ada (jarang terjadi tapi lebih aman)
                $code_check = $pdo->prepare("SELECT id FROM teams WHERE invite_code = ?");
                $code_check->execute([$invite_code]);
                while ($code_check->fetch()) {
                    $invite_code = generateInviteCode();
                    $code_check->execute([$invite_code]);
                }

                // Insert tim baru dengan invite code
                $stmt = $pdo->prepare("INSERT INTO teams (name, leader_id, invite_code) VALUES (?, ?, ?)");
                $stmt->execute([$team_name, $user_id, $invite_code]);

                $team_id = $pdo->lastInsertId();

                // Update user dengan team_id
                $update = $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ?");
                $update->execute([$team_id, $user_id]);

                // Tampilkan kode undangan ke user
                $_SESSION['success_message'] = "Tim berhasil dibuat! Kode undangan tim Anda: <strong>$invite_code</strong>";
                header("Location: ctf.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
            error_log("Database error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Tim - Wreckit 6</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">Buat Tim Baru</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="team_name" class="form-label">Nama Tim</label>
            <input type="text" id="team_name" name="team_name" class="form-control" required 
                   placeholder="Masukkan nama tim Anda" maxlength="30">
            <div class="form-text">Nama tim harus unik dan maksimal 30 karakter</div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Buat Tim</button>
        </div>
    </form>
</div>
</body>
</html>