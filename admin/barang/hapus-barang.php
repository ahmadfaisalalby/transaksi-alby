<?php

require_once '../../functions.php';

wajibAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: barang.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: barang.php?error=' . urlencode('ID barang tidak valid.'));
    exit;
}

$hasil = prosesHapusBarang($id);
if ($hasil['success']) {
    header('Location: barang.php?success=hapus');
    exit;
}

header('Location: barang.php?error=' . urlencode($hasil['error']));
exit;