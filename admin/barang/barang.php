<?php

require_once '../../functions.php';

wajibAdmin();
$error = $_GET['error'] ?? '';
$success = '';

// SUCCESS / ALERT
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'tambah':
            $success = 'Barang berhasil ditambahkan.';
            break;
        case 'edit':
            $success = 'Barang berhasil diubah.';
            break;
        case 'hapus':
            $success = 'Barang berhasil dihapus.';
            break;
    }
}

// DATA BARANG
$barang = getBarang();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Barang - Admin</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/master-barang.css">
</head>

<body>
<?php include '../../components/navbar.php'; ?>
<?php include '../../components/sidebar.php'; ?>

<div class="page-content">
    <header class="page-header">
        <div>
            <h1>Master Barang</h1>
            <p>Kelola data barang yang tersedia.</p>
        </div>
    </header>

    <main class="page-main">
        <!-- TOOLBAR -->
        <div class="toolbar">
            <h2>Data Barang</h2>
            <a href="tambah-barang.php" class="btn btn-tambah">
                + Tambah Barang
            </a>
        </div>

        <!-- // SUCCESS -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- ERROR -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- TABLE -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($barang)): ?>
                        <tr>
                            <td colspan="6" class="kosong">
                                Belum ada data barang.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang as $index => $b): ?>
                            <tr>
                                <!-- NOMOR -->
                                <td>
                                    <?= $index + 1; ?>
                                </td>

                                <!-- GAMBAR -->
                                <td>
                                    <?php if (!empty($b['gambar'])): ?>
                                        <img src="/img/<?= htmlspecialchars($b['gambar']); ?>" alt="<?= htmlspecialchars($b['nama_barang']); ?>" class="gambar-produk">
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                                <!-- NAMA -->
                                <td>
                                    <?= htmlspecialchars($b['nama_barang']); ?>
                                </td>

                                <!-- HARGA -->
                                <td>Rp
                                    <?= number_format($b['harga_barang'], 0, ',', '.'); ?>
                                </td>

                                <!-- STOK -->
                                <td>
                                    <?php if ((int) $b['stok'] <= 0): ?>
                                        <span class="stok-habis">
                                            Habis
                                        </span>
                                    <?php else: ?>
                                        <?= (int) $b['stok']; ?>
                                    <?php endif; ?>
                                </td>

                                <!-- AKSI -->
                                <td>
                                    <div class="aksi">
                                        <!-- EDIT -->
                                        <a href="edit-barang.php?id=<?= $b['id']; ?>" class="btn btn-edit">Edit</a>

                                        <!-- HAPUS -->
                                        <form action="hapus-barang.php" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menghapus barang ini?');">
                                            <input type="hidden" name="id" value="<?= $b['id']; ?>">
                                            <button type="submit" class="btn btn-hapus">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="/js/script.js"></script>
</body>
</html>