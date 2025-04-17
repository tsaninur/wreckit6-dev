<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: log.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil informasi tim user termasuk invite_code
    $stmt = $pdo->prepare("SELECT u.team_id, t.name as team_name, t.invite_code 
                          FROM users u 
                          LEFT JOIN teams t ON u.team_id = t.id 
                          WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['team_id']) {
        $_SESSION['error'] = "Anda belum tergabung dalam tim atau solo.";
        header("Location: choose_team.php");
        exit;
    }

    $team_id = $user['team_id'];
    $team_name = $user['team_name'];
    $invite_code = $user['invite_code'];

    // Ambil anggota tim
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE team_id = ?");
    $stmt->execute([$team_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle file upload
    $uploadSuccess = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $requirement_file = $_FILES['requirement_file'] ?? null;
        $payment_file = $_FILES['payment_file'] ?? null;

        if ($requirement_file && $payment_file) {
            $req_dir = "uploads/requirements/";
            $pay_dir = "uploads/payments/";
            if (!is_dir($req_dir)) mkdir($req_dir, 0777, true);
            if (!is_dir($pay_dir)) mkdir($pay_dir, 0777, true);

            $req_name = $req_dir . "req_team_" . $team_id . "_" . basename($requirement_file['name']);
            $pay_name = $pay_dir . "pay_team_" . $team_id . "_" . basename($payment_file['name']);

            if (move_uploaded_file($requirement_file['tmp_name'], $req_name) &&
                move_uploaded_file($payment_file['tmp_name'], $pay_name)) {

                // Simpan path ke DB
                $stmt = $pdo->prepare("REPLACE INTO team_requirements (team_id, requirement_file, payment_file) VALUES (?, ?, ?)");
                $stmt->execute([$team_id, $req_name, $pay_name]);
                $uploadSuccess = true;
            } else {
                $error = "Gagal mengunggah file.";
            }
        } else {
            $error = "Kedua file wajib diunggah.";
        }
    }

    // Ambil status
    $stmt = $pdo->prepare("SELECT * FROM team_requirements WHERE team_id = ?");
    $stmt->execute([$team_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Persyaratan dan Pembayaran</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .team-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .member-list {
            margin-top: 15px;
        }
        .member-item {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .invite-code {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 1.2em;
            text-align: center;
        }
        .copy-btn {
            cursor: pointer;
            background: #0d6efd;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="team-info">
        <h2>Informasi Tim</h2>
        <h3>Nama Tim: <?= htmlspecialchars($team_name) ?></h3>
        
        <div class="invite-code-container">
            <h4>Kode Undangan Tim:</h4>
            <div class="invite-code">
                <?= htmlspecialchars($invite_code) ?>
                <button class="copy-btn" onclick="copyInviteCode()">Salin</button>
            </div>
            <small>Bagikan kode ini untuk mengundang anggota lain bergabung ke tim Anda.</small>
        </div>
        
        <h4>Anggota Tim:</h4>
        <div class="member-list">
            <?php foreach ($members as $member): ?>
                <div class="member-item">
                    <?= htmlspecialchars($member['username']) ?> 
                    (<?= htmlspecialchars($member['email']) ?>)
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h1>Upload Dokumen Persyaratan & Bukti Pembayaran</h1>

    <?php if (!empty($uploadSuccess)): ?>
        <div class="alert alert-success">Berhasil mengunggah dokumen!</div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="card p-4">
        <div class="mb-3">
            <label class="form-label">Unggah Dokumen Persyaratan (.pdf/.jpg/.png):</label>
            <input type="file" name="requirement_file" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Unggah Bukti Pembayaran (.pdf/.jpg/.png):</label>
            <input type="file" name="payment_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Kirim</button>
    </form>

    <hr>
    <h3>Status Saat Ini:</h3>
    <?php if ($submission): ?>
        <div class="card p-3">
            <p>Dokumen Persyaratan: <a href="<?= htmlspecialchars($submission['requirement_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a></p>
            <p>Bukti Pembayaran: <a href="<?= htmlspecialchars($submission['payment_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a></p>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Belum ada dokumen yang diunggah.</div>
    <?php endif; ?>
</div>

<script>
function copyInviteCode() {
    const inviteCode = "<?= $invite_code ?>";
    navigator.clipboard.writeText(inviteCode).then(() => {
        alert("Kode undangan berhasil disalin: " + inviteCode);
    }).catch(err => {
        console.error('Gagal menyalin teks: ', err);
    });
}
</script>
</body>
</html>