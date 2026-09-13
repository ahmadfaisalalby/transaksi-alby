<?php

require_once '../../functions.php';

wajibAdmin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: transaksi.php');
    exit;
}

$dataDetail = getAdminDetailTransaksi($id);
if (!$dataDetail) {
    header('Location: transaksi.php');
    exit;
}

$transaksi = $dataDetail['transaksi'];
$detail = $dataDetail['detail'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi #<?= $transaksi['id'] ?> - Transaksi</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/admin-detail-transaksi.css">
</head>

<body>
    <?php include '../../components/navbar.php'; ?>
    <?php include '../../components/sidebar.php'; ?>

    <div class="page-content">
        <header class="page-header">
            <h1>Detail Transaksi</h1>
            <p>Informasi lengkap transaksi.</p>
        </header>

        <main class="page-main">
            <section class="detail-card">

                <!-- HEADER -->
                <div class="detail-header">
                    <div>
                        <h2>Transaksi#<?= $transaksi['id'] ?></h2>
                        <p><?= date('d-m-Y H:i:s', strtotime($transaksi['tanggal'])) ?></p>
                    </div>
                </div>

                <!-- INFORMASI -->
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">
                            Customer
                        </span>
                        <span class="info-value">
                            <?= htmlspecialchars($transaksi['nama_customer']) ?>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            Kasir
                        </span>
                        <span class="info-value">
                            <?= htmlspecialchars($transaksi['nama_kasir']) ?>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            Alamat
                        </span>
                        <span class="info-value">
                            <?= htmlspecialchars($transaksi['alamat'] ?: '-') ?>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">
                            No. HP
                        </span>
                        <span class="info-value">
                            <?= htmlspecialchars($transaksi['no_hp'] ?: '-') ?>
                        </span>
                    </div>
                </div>

                <!-- BARANG -->
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>
                                No
                            </th>
                            <th>
                                Produk
                            </th>
                            <th class="text-right">
                                Harga
                            </th>
                            <th class="text-center">
                                Jumlah
                            </th>
                            <th class="text-right">
                                Subtotal
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($detail as $index => $item): ?>
                            <tr>
                                <td class="text-center">
                                    <?= $index + 1 ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['nama_barang']) ?>
                                </td>
                                <td class="text-right">Rp
                                    <?= number_format($item['harga_barang'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?= $item['jumlah_barang'] ?>
                                </td>
                                <td class="text-right">Rp
                                    <?= number_format($item['total_harga'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- RINGKASAN -->
                <div class="summary">
                    <div class="summary-row">
                        <span>
                            Total Barang
                        </span>
                        <strong>
                            <?= $transaksi['total_barang'] ?>
                        </strong>
                    </div>

                    <div class="summary-row total">
                        <span>
                            Total Harga
                        </span>
                        <span>Rp
                            <?= number_format($transaksi['total_harga'], 0, ',', '.') ?>
                        </span>
                    </div>

                    <div class="summary-row">
                        <span>
                            Uang Bayar
                        </span>
                        <span>Rp
                            <?= number_format($transaksi['uang_bayar'], 0, ',', '.') ?>
                        </span>
                    </div>

                    <div class="summary-row kembalian">
                        <span>
                            Kembalian
                        </span>
                        <span>Rp
                            <?= number_format($transaksi['uang_kembali'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>

                <!-- AKSI -->
                <div class="detail-actions">
                    <a href="transaksi.php" class="button-secondary">Kembali</a>
                </div>
            </section>
        </main>
    </div>

    <script src="/js/script.js"></script>
</body>
</html>