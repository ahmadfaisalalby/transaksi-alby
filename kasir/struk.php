<?php
require_once '../functions.php';

wajibLogin();
wajibKasir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /kasir/');
    exit;
}

try {
    $dataStruk = prosesTransaksiKasir($_POST['nama_customer'] ?? '', $_POST['alamat_customer'] ?? '', $_POST['no_hp_customer'] ?? '', $_POST['id_barang'] ?? [], $_POST['jumlah_barang'] ?? [], $_POST['uang_bayar'] ?? 0);
    $kode_transaksi = $dataStruk['kode_transaksi'];
    $detail_transaksi = $dataStruk['detail_transaksi'];
    $tanggal_transaksi = $dataStruk['tanggal_transaksi'];
    $nama_kasir = $dataStruk['nama_kasir'];
    $nama_customer = $dataStruk['nama_customer'];
    $alamat_customer = $dataStruk['alamat_customer'];
    $no_hp_customer = $dataStruk['no_hp_customer'];
    $toko = $dataStruk['toko'];
    $hasil = $dataStruk['hasil'];
} catch (Exception $e) {
    die('Transaksi gagal: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= htmlspecialchars($kode_transaksi); ?></title>
    <link rel="stylesheet" href="../css/struk.css">
</head>

<body>
    <main class="struk">
        <!-- HEADER TOKO -->
        <header class="header-toko">
            <?php if (!empty($toko['logo_toko'])): ?>
                <div class="logo-toko">
                    <img src="../img/<?= htmlspecialchars($toko['logo_toko']); ?>" alt="Logo <?= htmlspecialchars($toko['nama_toko']); ?>">
                </div>
            <?php endif; ?>
            <h1><?= htmlspecialchars($toko['nama_toko']); ?></h1>
            <p><?= nl2br(htmlspecialchars($toko['alamat_toko'])); ?></p>
            <p>No. Telp: <?= htmlspecialchars($toko['no_hp_toko']); ?></p>
        </header>

        <div class="garis"></div>

        <!-- INFORMASI TRANSAKSI -->
        <section class="info-transaksi">
            <div class="baris-info">
                <div class="info-kiri">Tanggal:
                    <?= date('Y-m-d H:i:s', strtotime($tanggal_transaksi)); ?>
                </div>
                <div class="info-kanan">Kasir:
                    <?= htmlspecialchars($nama_kasir); ?>
                </div>
            </div>

            <div class="baris-info">
                <div class="info-kiri">Pelanggan:
                    <?= htmlspecialchars($nama_customer); ?>
                </div>
                <?php if ($alamat_customer): ?>
                    <div class="info-kanan">Alamat:
                        <?= htmlspecialchars($alamat_customer); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($no_hp_customer): ?>
                <div class="baris-info">
                    <div class="info-kiri">No. HP:
                        <?= htmlspecialchars($no_hp_customer); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="kode-transaksi">No. Transaksi: 
                <?= htmlspecialchars($kode_transaksi); ?>
            </div>
        </section>

        <div class="garis"></div>

        <!-- DETAIL BARANG -->
        <section class="detail-barang">
            <?php foreach ($detail_transaksi as $index => $item): ?>
                <?php $barang_detail = getBarangById($item['id_barang']); ?>

                <div class="item-struk">
                    <div class="nama-barang">
                        <?= ($index + 1) . '. ' ?>
                        <?= htmlspecialchars($barang_detail['nama_barang']); ?>
                    </div>

                    <div class="detail-harga">
                        <span>
                            <?= $item['jumlah_barang']; ?>x<?= number_format($item['harga_barang'], 0, ',', '.'); ?>
                        </span>

                        <span>Rp
                            <?= number_format($item['total_harga'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <div class="garis"></div>

        <!-- RINGKASAN -->
        <section class="ringkasan">
            <div class="baris-total">
                <span>
                    Total QTY
                </span>
                <span>
                    <?= $hasil['total_barang']; ?>
                </span>
            </div>

            <div class="baris-total">
                <span>
                    Subtotal
                </span>
                <span>Rp
                    <?= number_format($hasil['total_harga'], 0, ',', '.'); ?>
                </span>
            </div>

            <div class="baris-total total">
                <strong>Total</strong>
                <strong>Rp<?= number_format($hasil['total_harga'], 0, ',', '.'); ?></strong>
            </div>

            <div class="baris-total">
                <span>
                    Bayar (Cash)
                </span>
                <span>Rp<?= number_format($hasil['uang_bayar'], 0, ',', '.'); ?>
                </span>
            </div>

            <div class="baris-total">
                <span>Kembali</span>
                <span>Rp<?= number_format($hasil['uang_kembali'], 0, ',', '.'); ?>
                </span>
            </div>
        </section>

        <div class="garis"></div>

        <!-- FOOTER -->
        <footer class="footer-struk">
            <div class="terima-kasih">
                Terimakasih Telah Berbelanja
            </div>
            <p>Kritik dan Saran</p>
            <p>Silakan hubungi kami</p>
        </footer>

        <!-- TOMBOL -->
        <div class="aksi">
            <button type="button" onclick="cetakStruk()">
                Cetak Struk
            </button>
            <a href="index.php">Kembali</a>
        </div>
    </main>

    <script src="../js/struk.js"></script>
</body>
</html>