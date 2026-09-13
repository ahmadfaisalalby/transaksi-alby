<?php
    $isAdmin = $_SESSION['user']['role'] === 'admin';
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-menu">
        <?php if ($isAdmin): ?>
            <div class="menu-section">
                MASTER DATA
            </div>
            <a href="/admin/barang/barang.php" class="sidebar-link">
                <span class="sidebar-icon">📦</span>
                <span class="sidebar-text">Master Barang</span>
            </a>
            <a href="/admin/kasir/kasir.php" class="sidebar-link">
                <span class="sidebar-icon">👤</span>
                <span class="sidebar-text">Master Kasir</span>
            </a>
            <div class="menu-section">
                TRANSAKSI
            </div>
            <a href="/admin/transaksi/transaksi.php" class="sidebar-link">
                <span class="sidebar-icon">📋</span>
                <span class="sidebar-text">Riwayat Transaksi</span>
            </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-bottom">
        <?php if ($isAdmin): ?>
            <a href="/admin/pengaturan.php" class="sidebar-link">
                <span class="sidebar-icon">⚙️</span>
                <span class="sidebar-text">Pengaturan</span>
            </a>
        <?php else: ?>
            <a href="../kasir/pengaturan.php" class="sidebar-link">
                <span class="sidebar-icon">⚙️</span>
                <span class="sidebar-text">Pengaturan</span>
            </a>
        <?php endif; ?>
    </div>
</aside>