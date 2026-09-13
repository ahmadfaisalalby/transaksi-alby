<?php

require_once '../../functions.php';

wajibAdmin();

$error = $_GET['error'] ?? '';
$success = '';

// ALERT SUCCESS
switch ($_GET['success'] ?? '') {
    case 'kasir_ditambahkan':
        $success = 'Kasir berhasil ditambahkan.';
        break;
    case 'kasir_diubah':
        $success = 'Data kasir berhasil diubah.';
        break;
    case 'kasir_dihapus':
        $success = 'Kasir berhasil dihapus.';
        break;
}

// ALERT ERROR
if ($_GET['error'] ?? '' === 'kasir_pernah_transaksi') {
    $error = 'Kasir tidak dapat dihapus karena sudah memiliki riwayat transaksi.';
}
if ($_GET['error'] ?? '' === 'gagal_hapus') {
    $error = 'Kasir gagal dihapus.';
}

// DATA KASIR
$kasir = getKasirAdmin();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Kasir - Transaksi</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/master-kasir.css">
</head>

<body>
<?php include '../../components/navbar.php'; ?>
<?php include '../../components/sidebar.php'; ?>

<div class="page-content">
    <header class="page-header">
        <div>
            <h1>Master Kasir</h1>
            <p>Kelola akun kasir.</p>
        </div>
    </header>

    <main class="page-main">
        <div class="table-card">
            <div class="table-header">
                <h2>Daftar Kasir</h2>
                <a href="tambah-kasir.php" class="button-primary">+ Tambah Kasir</a>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($kasir)): ?>
                <div class="empty-data">
                    Belum ada data kasir.
                </div>
            <?php else: ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kasir</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($kasir as $i => $data): ?>
                            <tr>
                                <td>
                                    <?= $i + 1 ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($data['nama_kasir'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($data['username']) ?>
                                </td>
                                <td>
                                    <span class="badge">
                                        Kasir
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- EDIT -->
                                        <a href="edit-kasir.php?id=<?= (int) $data['id'] ?>" class="button-edit">Edit</a>

                                        <!-- HAPUS -->
                                        <form action="hapus-kasir.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus kasir ini?')" style="margin:0;">
                                            <input type="hidden" name="id" value="<?= (int) $data['id'] ?>">
                                            <button type="submit" class="button-delete">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="/js/script.js"></script>
</body>
</html>