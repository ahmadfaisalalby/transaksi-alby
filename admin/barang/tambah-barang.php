<?php

require_once '../../functions.php';

wajibAdmin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasil = prosesTambahBarang(trim($_POST['nama_barang'] ?? ''), (int) ($_POST['harga_barang'] ?? 0), (int) ($_POST['stok'] ?? 0), $_FILES['gambar'] ?? null);

    if ($hasil['success']) {
        header('Location: barang.php?success=tambah');
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
    <title>Tambah Barang - Admin</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/master-barang.css">
</head>

<body>
<?php include '../../components/navbar.php'; ?>
<?php include '../../components/sidebar.php'; ?>

<div class="page-content">
    <header class="page-header">
        <div>
            <h1>Tambah Barang</h1>
            <p>Tambahkan barang baru ke dalam data barang.</p>
        </div>
    </header>

    <main class="page-main">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <section>
            <form action="tambah-barang.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_barang">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($_POST['nama_barang'] ?? ''); ?>" maxlength="60" required>
                </div>

                <div class="form-group">
                    <label for="harga_barang">Harga Barang</label>
                    <input type="number" id="harga_barang" name="harga_barang" value="<?= htmlspecialchars($_POST['harga_barang'] ?? ''); ?>" min="0" step="1" required>
                </div>

                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" value="<?= htmlspecialchars($_POST['stok'] ?? ''); ?>" min="0" step="1" required>
                </div>

                <div class="form-group">
                    <label for="gambar">Gambar Barang</label>
                    <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp">
                    <small>
                        Format JPG, PNG, atau WEBP.
                        Maksimal 2 MB.
                    </small>
                </div>

                <div class="form-actions">
                    <a href="barang.php" class="button-secondary">Batal</a>
                    <button type="submit" class="button-primary">
                        Simpan Barang
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>

<script src="/js/script.js"></script>
</body>
</html>