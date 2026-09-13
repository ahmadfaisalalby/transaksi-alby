<?php

require_once '../functions.php';

wajibAdmin();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Transaksi</title>
    <link rel="stylesheet" href="/css/layout.css">
    <script src="../js/script.js"></script>
</head>

<body>
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>
    <div class="page-content">
        <header class="page-header">
            <h1>Dashboard Admin</h1>
            <p>Selamat datang, <?= htmlspecialchars($_SESSION['user']['username']) ?></p>
        </header>

        <main class="page-main">
            <section>
                <h2>Menu Admin</h2>
            </section>
        </main>
    </div>
</body>
</html>
