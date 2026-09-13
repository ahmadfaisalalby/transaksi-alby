<?php

require_once '../functions.php';

wajibKasir();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Transaksi</title>
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/pengaturan.css">
</head>

<body>
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <div class="page-content">
        <header class="page-header">
            <h1>Pengaturan</h1>
            <p>Pengaturan aplikasi kasir.</p>
        </header>
        <main class="page-main">
            <a href="/kasir/" class="back-link">← Kembali ke Penjualan</a>
        </main>
    </div>
</body>
</html>