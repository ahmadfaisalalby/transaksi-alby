<?php

require_once '../../functions.php';

wajibAdmin();

// AMBIL ID BARANG
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: barang.php');
    exit;
}

// AMBIL DATA BARANG
$barang = getBarangById($id);

if (!$barang) {
    header('Location: barang.php');
    exit;
}

// PROSES EDIT
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasil = prosesEditBarang($id, trim($_POST['nama_barang'] ?? ''), (int) ($_POST['harga_barang'] ?? 0), (int) ($_POST['stok'] ?? 0), $_FILES['gambar'] ?? null);

    if ($hasil['success']) {
        header('Location: barang.php?success=edit');
        exit;
    } else {
        $error = $hasil['error'];
        // Ambil kembali data barang
        // supaya gambar lama tetap tersedia
        $barang = getBarangById($id);
        if (!$barang) {
            header('Location: barang.php');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - Admin</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/master-barang.css">
</head>

<body>
<?php include '../../components/navbar.php'; ?>
<?php include '../../components/sidebar.php'; ?>

<div class="page-content">
    <header class="page-header">
        <div>
            <h1>Edit Barang</h1>
            <p>Ubah informasi barang.</p>
        </div>
    </header>

    <main class="page-main">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <section>
            <form action="edit-barang.php?id=<?= $barang['id']; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $barang['id']; ?>">

                <div class="form-group">
                    <label for="nama_barang">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($_POST['nama_barang'] ?? $barang['nama_barang']); ?>" maxlength="60" required>
                </div>

                <div class="form-group">
                    <label for="harga_barang">Harga Barang</label>
                    <input type="number" id="harga_barang" name="harga_barang" value="<?= htmlspecialchars($_POST['harga_barang'] ?? $barang['harga_barang']); ?>" min="0" step="1" required>
                </div>

                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" value="<?= htmlspecialchars($_POST['stok'] ?? $barang['stok']); ?>" min="0" step="1" required>
                </div>

                <div class="form-group">
                    <label>Gambar Saat Ini</label>

                    <?php if (!empty($barang['gambar'])): ?>
                        <div>
                            <img src="/img/<?= htmlspecialchars($barang['gambar']); ?>" alt="<?= htmlspecialchars($barang['nama_barang']); ?>" style="width:150px; height:150px; object-fit:cover; border-radius:8px;">
                        </div>
                    <?php else: ?>
                        <p>Belum ada gambar.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="gambar">Ganti Gambar</label>
                    <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp">
                    <small>
                        Kosongkan jika tidak ingin mengganti gambar.
                        Format JPG, PNG, atau WEBP.
                        Maksimal 2 MB.
                    </small>
                </div>

                <div class="form-actions">
                    <a href="barang.php" class="button-secondary">Batal</a>
                    <button type="submit" class="button-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>

<script src="/js/script.js"></script>
</body>
</html>