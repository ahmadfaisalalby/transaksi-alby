<?php

require_once __DIR__ . '/../functions.php';

$toko = getToko();
$nama_toko = $toko['nama_toko'] ?? 'Transaksi';
$logo_toko = $toko['logo_toko'] ?? '';

?>

<header class="navbar">
    <div class="navbar-left">
        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
            aria-label="Toggle sidebar"
        >
            ☰
        </button>

        <a class="brand">
            <?php if (!empty($logo_toko)): ?>
                <img src="/img/<?= htmlspecialchars($logo_toko); ?>" alt="Logo <?= htmlspecialchars($nama_toko); ?>" class="brand-logo">
            <?php else: ?>
                <span class="brand-logo-fallback">
                    T
                </span>
            <?php endif; ?>

            <span class="brand-text">
                <?= htmlspecialchars($nama_toko); ?>
            </span>
        </a>
    </div>

    <div class="navbar-right">
        <div class="kasir-profile">
            <div class="kasir-avatar">
                <?= strtoupper(substr($_SESSION['user']['username'], 0, 1)) ?>
            </div>

            <div class="kasir-info">
                <span class="kasir-label">
                    <?= ucfirst($_SESSION['user']['role']) ?>
                </span>
                <span class="kasir-name">
                    <?= htmlspecialchars($_SESSION['user']['username']) ?>
                </span>
            </div>
        </div>
    </div>

    <a href="/logout.php" class="logout-button">Logout</a>

</header>