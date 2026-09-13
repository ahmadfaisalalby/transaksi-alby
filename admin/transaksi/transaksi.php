<?php

require_once '../../functions.php';

wajibAdmin();

$dataTransaksi = getAdminTransaksi(
    $_GET['search'] ?? '',
    (int) ($_GET['page'] ?? 1)
);

$search = $dataTransaksi['search'];
$perPage = $dataTransaksi['perPage'];
$page = $dataTransaksi['page'];
$offset = $dataTransaksi['offset'];
$totalTransaksi = $dataTransaksi['totalTransaksi'];
$totalPages = $dataTransaksi['totalPages'];
$transaksi = $dataTransaksi['transaksi'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Transaksi</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/admin-transaksi.css">
</head>

<body>
    <?php include '../../components/navbar.php'; ?>
    <?php include '../../components/sidebar.php'; ?>

    <div class="page-content">
        <header class="page-header">
            <h1>Riwayat Transaksi</h1>
        </header>

        <main class="page-main">
            <div class="table-card">
                <div class="table-header">
                    <div>
                        <h2>Daftar Transaksi</h2>
                        <span class="transaction-count">
                            <?= $totalTransaksi ?>
                            transaksi
                        </span>
                    </div>

                    <form method="GET" class="search-form">
                        <input type="text" name="search" class="search-input" placeholder="Cari transaksi..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        <?php if ($search !== ''): ?>
                            <a href="transaksi.php" class="search-reset">×</a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (empty($transaksi)): ?>
                    <div class="empty-data">
                        Belum ada transaksi.
                    </div>
                <?php else: ?>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    No.
                                </th>
                                <th>
                                    ID Transaksi
                                </th>
                                <th>
                                    Tanggal
                                </th>
                                <th>
                                    Customer
                                </th>
                                <th>
                                    Kasir
                                </th>
                                <th class="text-center">
                                    Jumlah
                                </th>
                                <th class="text-right">
                                    Total
                                </th>
                                <th class="text-right">
                                    Bayar
                                </th>
                                <th class="text-right">
                                    Kembalian
                                </th>
                                <th class="text-center">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($transaksi as $index => $data): ?>
                                <?php $nomor = $offset + $index + 1;?>
                                <tr>
                                    <td class="text-left">
                                        <?= $nomor ?>
                                    </td>
                                    <td class="transaction-id">
                                        <?= htmlspecialchars($data['kode_transaksi']) ?>
                                    </td>
                                    <td>
                                        <?= date('d-m-Y H:i', strtotime($data['tanggal'])) ?>
                                    </td>
                                    <td class="customer-name">
                                        <?= htmlspecialchars($data['nama_customer']) ?>
                                    </td>
                                    <td class="kasir-name">
                                        <?= htmlspecialchars($data['nama_kasir']) ?>
                                    </td>
                                    <td class="text-left">
                                        <?= $data['total_barang'] ?>
                                    </td>
                                    <td class="text-left">Rp
                                        <?= number_format($data['total_harga'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-left">Rp
                                        <?= number_format($data['uang_bayar'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-left">Rp
                                        <?= number_format($data['uang_kembali'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-left">
                                        <a href="detail-transaksi.php?id=<?= $data['id'] ?>" class="button-detail">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="pagination-button"><</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="pagination-button <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="pagination-button">></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="/js/script.js"></script>
    <script src="/js/transaksi.js"></script>
</body>
</html>