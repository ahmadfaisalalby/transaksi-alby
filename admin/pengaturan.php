<?php

require_once '../functions.php';

wajibAdmin();

$toko = getToko();
if (!$toko) {
    die('Data toko belum tersedia.');
}

$pesan = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasil = prosesPengaturanToko($_POST['nama_toko'] ?? '', $_POST['no_hp_toko'] ?? '', $_POST['alamat_toko'] ?? '', $_FILES['logo_toko'] ?? []);

    if ($hasil['success']) {
        $pesan = 'Pengaturan toko berhasil disimpan.';
        $toko = getToko();
    } else {
        $error = $hasil['error'];
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Toko</title>
    <link rel="stylesheet" href="../css/layout.css">
    <script src="../js/script.js"></script>
</head>

<body>
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <main class="page-content">
        <div class="page-header">
            <h1>Pengaturan Toko</h1>
        </div>

        <?php if ($pesan): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="page-main">
            <section>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama_toko">Nama Toko</label>
                        <input type="text" name="nama_toko" id="nama_toko" maxlength="60" value="<?= htmlspecialchars($toko['nama_toko']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="alamat_toko">Alamat Toko</label>
                        <textarea name="alamat_toko" id="alamat_toko" rows="4" maxlength="255" required><?= htmlspecialchars($toko['alamat_toko']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="no_hp_toko">No. HP Toko</label>
                        <input type="text" name="no_hp_toko" id="no_hp_toko" maxlength="20" value="<?= htmlspecialchars( $toko['no_hp_toko']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="logo_toko">Logo Toko</label>
                        <input type="file" name="logo_toko" id="logo_toko" accept=".jpg,.jpeg,.png,.webp">
                        <small>JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                    </div>

                    <?php if (!empty($toko['logo_toko'])): ?>
                        <div class="form-group">
                            <label>Logo Saat Ini</label>
                            <br>
                            <img src="../img/<?= htmlspecialchars($toko['logo_toko']); ?>" alt="Logo Toko" class="logo-preview">
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>