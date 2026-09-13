<?php

require_once '../../functions.php';

wajibAdmin();

$error = '';
$nama_kasir = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kasir = trim($_POST['nama_kasir'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $hasil = prosesTambahKasir($nama_kasir, $username, $_POST['password'] ?? '', $_POST['konfirmasi_password'] ?? '');

    if ($hasil['success']) {
        header('Location: kasir.php?success=kasir_ditambahkan');
        exit;
    }
    $error = $hasil['error'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kasir - Transaksi</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/master-kasir.css">
</head>

<body>
<?php include '../../components/navbar.php'; ?>
<?php include '../../components/sidebar.php'; ?>

<div class="page-content">
    <header class="page-header">
        <h1>Tambah Kasir</h1>
        <p>Tambahkan akun kasir baru.</p>
    </header>

    <main class="page-main">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <section class="form-card-tambah">
            <h2>Data Kasir</h2>
            <form action="tambah-kasir.php" method="POST">
                <div class="form-group">
                    <label for="nama_kasir">Nama Kasir</label>
                    <input type="text" id="nama_kasir" name="nama_kasir" value="<?= htmlspecialchars($nama_kasir); ?>" placeholder="Masukkan nama kasir" maxlength="60" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" placeholder="Masukkan username" maxlength="60" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 7 karakter" required>
                    <small>Password minimal 7 karakter.</small>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Masukkan kembali password", required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button-primary">
                        Simpan
                    </button>
                    <a href="kasir.php" class="button-secondary">Batal</a>
                </div>
            </form>
        </section>
    </main>
</div>

<script src="/js/script.js"></script>
</body>
</html>