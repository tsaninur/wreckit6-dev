<?php
session_start();
require_once 'config.php'; // Pastikan config.php berisi koneksi PDO $pdo

// Cek apakah user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_code = trim($_POST['team_code']);
    $user_id = $_SESSION['user_id'];

    try {
        // Cari tim berdasarkan invite_code (diubah dari code)
        $stmt = $pdo->prepare("SELECT id FROM teams WHERE invite_code = ?");
        $stmt->execute([$team_code]);
        $team = $stmt->fetch();

        if ($team) {
            // Cek apakah user sudah punya tim
            $check = $pdo->prepare("SELECT team_id FROM users WHERE id = ?");
            $check->execute([$user_id]);
            $user = $check->fetch();
            
            if ($user['team_id']) {
                $error = "Anda sudah bergabung dengan tim lain.";
            } else {
                // Update user ke dalam tim
                $update = $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ?");
                $update->execute([$team['id'], $user_id]);

                header("Location: ctf.php");
                exit;
            }
        } else {
            $error = "Kode undangan tim tidak ditemukan.";
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Gabung Tim - Wreckit 6</title>
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
    <h2 class="mb-4 text-center">Gabung ke Tim</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="team_code" class="form-label">Kode Undangan Tim</label>
            <input type="text" id="team_code" name="team_code" class="form-control" required placeholder="Masukkan kode undangan">
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-warning">Gabung Tim</button>
        </div>
    </form>
</div>
</body>
</html>