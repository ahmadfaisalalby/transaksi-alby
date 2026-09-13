<?php

require_once '../../functions.php';

wajibAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kasir.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: kasir.php?error=gagal_hapus');
    exit;
}

$hasil = prosesHapusKasir($id);
if ($hasil['success']) {

    header(
        'Location: kasir.php?success=kasir_dihapus'
    );

    exit;
}

// Tentukan kode error berdasarkan pesan
if (
    $hasil['error'] === 'Kasir tidak dapat dihapus karena sudah memiliki riwayat transaksi.'
) {
    header('Location: kasir.php?error=kasir_pernah_transaksi');
    exit;
}

header('Location: kasir.php?error=gagal_hapus');
exit;

?>