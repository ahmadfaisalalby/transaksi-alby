<?php

require_once '../functions.php';

$dataKasir = getDataHalamanKasir();

if (!$dataKasir['success']) {
    die($dataKasir['error']);
}

$barang = $dataKasir['barang'];
$id_kasir = $dataKasir['id_kasir'];
$nama_kasir = $dataKasir['nama_kasir'];
$id_transaksi_display = $dataKasir['id_transaksi_display'];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/layout.css">    
    <link rel="stylesheet" href="../css/kasir.css">
    <title>Penjualan</title>
</head>


<body>
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <div class="page-content">
        <header class="page-header">
            <h1>Penjualan</h1>
        </header>

        <main class="page-main">
            <form action="struk.php" method="POST" id="formTransaksi">
                <input type="hidden" name="id_transaksi_display" value="<?= htmlspecialchars($id_transaksi_display); ?>">

                <!-- PANEL 1 — DATA CUSTOMER -->
                <section class="panel-customer">
                    <h2>Data Customer</h2>
                    <!-- Nama Customer -->
                    <div>
                        <label for="nama_customer">Nama Customer</label>
                        <input type="text" name="nama_customer" id="nama_customer" value="Customer" required>
                    </div>

                    <!-- No. HP -->
                    <div>
                        <label for="no_hp_customer">No. HP</label>
                        <input type="text" name="no_hp_customer" id="no_hp_customer" value="-">
                    </div>

                    <!-- Alamat -->
                    <div class="field-alamat">
                        <label for="alamat_customer">Alamat</label>
                        <input type="text" name="alamat_customer" id="alamat_customer" value="-" placeholder="Masukkan alamat customer">
                    </div>

                    <!-- DATA KASIR -->
                    <input type="hidden" name="id_kasir" value="<?= $id_kasir; ?>">
                </section>

                <!-- PANEL 2 — DAFTAR PRODUK -->
                <section class="panel-produk">
                    <h2>Daftar Produk</h2>
                    <div class="barang-list">
                        <?php if (empty($barang)): ?>
                            <p>Belum ada produk yang tersedia.</p>
                        <?php else: ?>
                            <?php foreach ($barang as $b): ?>
                                <article class="barang" data-id="<?= $b['id']; ?>" data-harga="<?= $b['harga_barang']; ?>" data-stok="<?= $b['stok']; ?>">
                                    <img src="../img/<?= htmlspecialchars($b['gambar']); ?>" alt="<?= htmlspecialchars($b['nama_barang']); ?>">
                                    <div class="barang-info">
                                        <h3><?= htmlspecialchars($b['nama_barang']); ?></h3>
                                        <p>Harga:
                                            <strong>
                                                Rp <?= number_format($b['harga_barang'], 0, ',', '.'); ?>
                                            </strong>
                                        </p>
                                        <label for="jumlah_<?= $b['id']; ?>">Jumlah</label>

                                        <div class="jumlah-control">
                                            <button type="button" class="btn-jumlah btn-minus" data-target="jumlah_<?= $b['id']; ?>">
                                                -
                                            </button>

                                            <input type="number" id="jumlah_<?= $b['id']; ?>" class="jumlah-input" min="0" max="<?= $b['stok']; ?>" value="0">

                                            <button type="button" class="btn-jumlah btn-plus" data-target="jumlah_<?= $b['id']; ?>">
                                                +
                                            </button>
                                        </div>
                                        <p>Subtotal:
                                            <strong class="subtotal">0</strong>
                                        </p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- PANEL 3 — RINGKASAN PEMBELIAN -->
                <section class="panel-ringkasan">
                    <h2>Ringkasan Pembelian</h2>
                    <p class="id-transaksi">ID Transaksi: <strong><?= htmlspecialchars($id_transaksi_display); ?></strong></p>

                    <div class="ringkasan-barang" id="ringkasanBarang">
                        <p class="ringkasan-kosong">Belum ada barang dipilih.</p>
                    </div>

                    <dl>
                        <dt>
                            Total Barang
                        </dt>
                        <dd>
                            <strong id="totalBarang">0</strong>
                        </dd>
                        <dt>
                            Total Harga
                        </dt>
                        <dd>
                            <strong>Rp
                                <span id="totalHarga">
                                    0
                                </span>
                            </strong>
                        </dd>
                    </dl>
                </section>

                <!-- PANEL 4 — PEMBAYARAN -->
                <section class="panel-pembayaran">
                    <h2>Pembayaran</h2>
                    <div class="pembayaran-layout">
                        <!-- INPUT PEMBAYARAN -->
                        <div class="input-pembayaran">
                            <label for="uang_bayar">Uang Bayar</label>
                            <input type="number" name="uang_bayar" id="uang_bayar" min="0" required autocomplete="off">
                            <div class="kembalian">
                                <p>Kembalian:
                                    <strong>Rp
                                        <span id="uangKembali">0</span>
                                    </strong>
                                </p>
                            </div>
                        </div>

                        <!-- NUMPAD -->
                        <div class="numpad">
                            <button type="button" class="numpad-btn" data-number="1">
                                1
                            </button>
                            <button type="button" class="numpad-btn" data-number="2">
                                2
                            </button>
                            <button type="button" class="numpad-btn" data-number="3">
                                3
                            </button>
                            <button type="button" class="numpad-btn" data-number="4">
                                4
                            </button>
                            <button type="button" class="numpad-btn" data-number="5">
                                5
                            </button>
                            <button type="button" class="numpad-btn" data-number="6">
                                6
                            </button>
                            <button type="button" class="numpad-btn" data-number="7">
                                7
                            </button>
                            <button type="button" class="numpad-btn" data-number="8">
                                8
                            </button>
                            <button type="button" class="numpad-btn" data-number="9">
                                9
                            </button>
                            <button type="button" class="numpad-btn numpad-clear">
                                C
                            </button>
                            <button type="button" class="numpad-btn" data-number="0">
                                0
                            </button>
                            <button type="button" class="numpad-btn numpad-backspace">
                                ⌫
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-simpan-transaksi">
                        Simpan Transaksi
                    </button>
                </section>
            </form>
        </main>
    </div>

<script src="../js/script.js"></script>
<script src="../js/kasir.js"></script>
</body>
</html>