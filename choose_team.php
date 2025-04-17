<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'template/header.php';
?>

<div class="container py-5">
    <h2>Pilih Mode Partisipasi</h2>
    <p>Kamu belum tergabung dalam tim. Pilih salah satu opsi di bawah ini:</p>

    <div class="row">
        <div class="col-md-4">
            <a href="create_team.php" class="btn btn-primary w-100 mb-3">Buat Tim</a>
        </div>
        <div class="col-md-4">
            <a href="join_team.php" class="btn btn-warning w-100 mb-3">Gabung Tim</a>
        </div>
    </div>
</div>

<?php include 'template/footer.php'; ?>
