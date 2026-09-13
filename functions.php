<?php

session_start();

$conn = mysqli_connect("localhost", "root", "", "transaksi");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

function sudahLogin()
{
    return isset($_SESSION['user']);
}

function wajibLogin()
{
    if (!sudahLogin()) {
        header('Location: /login.php');
        exit;
    }
}

function wajibAdmin()
{
    wajibLogin();
    if ($_SESSION['user']['role'] !== 'admin') {
        header('Location: /kasir/index.php');
        exit;
    }
}

function wajibKasir()
{
    wajibLogin();
    if ($_SESSION['user']['role'] !== 'kasir') {
        header('Location: /admin/index.php');
        exit;
    }
}

// MASTER KASIR

function getKasirAdmin()
{
    return query("
        SELECT k.id, k.nama_kasir, k.id_users, u.username FROM kasir k INNER JOIN users u ON u.id = k.id_users WHERE u.role = 'kasir' ORDER BY k.id DESC");
}


function getKasirByIdAdmin($id)
{
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT k.id, k.id_users, k.nama_kasir, u.username FROM kasir k INNER JOIN users u ON u.id = k.id_users WHERE k.id = ? AND u.role = 'kasir' LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $kasir = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $kasir;
}

function getDataHalamanKasir()
{
    wajibLogin();
    wajibKasir();
    $barang = getBarang();
    $kasir = getKasirByUserId($_SESSION['user']['id']);
    if (!$kasir) {
        return ['success' => false, 'error' => 'Data kasir belum terhubung dengan akun.'];
    }

    return ['success' => true, 'error' => '', 'barang' => $barang, 'id_kasir' => (int) $kasir['id'], 'nama_kasir' => $kasir['nama_kasir'], 'id_transaksi_display' => generateIdTransaksi()];
}


function prosesTambahKasir($nama_kasir, $username, $password, $konfirmasi_password) {
    global $conn;
    $nama_kasir = trim($nama_kasir);
    $username = trim($username);

    // VALIDASI
    if ($nama_kasir === '') {
        return ['success' => false, 'error' => 'Nama kasir wajib diisi.'];
    }

    if ($username === '') {
        return ['success' => false, 'error' => 'Username wajib diisi.'];
    }

    if ($password === '') {
        return ['success' => false, 'error' => 'Password wajib diisi.'];
    }

    if (strlen($password) < 7) {
        return ['success' => false, 'error' => 'Password minimal 7 karakter.'];
    }

    if ($konfirmasi_password === '') {
        return ['success' => false, 'error' => 'Konfirmasi password wajib diisi.'];
    }

    if ($password !== $konfirmasi_password) {
        return ['success' => false, 'error' => 'Password dan konfirmasi password tidak sama.'];
    }

    // CEK USERNAME
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        return ['success' => false, 'error' => 'Username sudah digunakan.'];
    }
    mysqli_stmt_close($stmt);

    // SIMPAN
    mysqli_begin_transaction($conn);

    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // INSERT USERS
        $stmtUser = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, 'kasir')");

        mysqli_stmt_bind_param($stmtUser, "ss", $username, $password_hash);
        if (!mysqli_stmt_execute($stmtUser)) {
            throw new Exception('Gagal membuat akun kasir.');
        }

        $id_users = mysqli_insert_id($conn);
        mysqli_stmt_close($stmtUser);

        // INSERT KASIR
        $stmtKasir = mysqli_prepare($conn, "INSERT INTO kasir (id_users, nama_kasir) VALUES (?, ?)");

        mysqli_stmt_bind_param($stmtKasir, "is", $id_users, $nama_kasir);

        if (!mysqli_stmt_execute($stmtKasir)) {
            throw new Exception('Gagal menyimpan data kasir.');
        }

        mysqli_stmt_close($stmtKasir);

        mysqli_commit($conn);

        return ['success' => true, 'error' => ''];
    } catch (Exception $e) {
        mysqli_rollback($conn);

        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function prosesEditKasir($id, $nama_kasir, $username, $password, $konfirmasi_password) {
    global $conn;

    $id = (int) $id;
    $nama_kasir = trim($nama_kasir);
    $username = trim($username);

    if ($id <= 0) {
        return ['success' => false, 'error' => 'ID kasir tidak valid.'];
    }

    // AMBIL DATA KASIR
    $kasir = getKasirByIdAdmin($id);
    if (!$kasir) {
        return ['success' => false, 'error' => 'Data kasir tidak ditemukan.'];
    }

    // VALIDASI
    if ($nama_kasir === '') {
        return ['success' => false, 'error' => 'Nama kasir wajib diisi.'];
    } elseif ($username === '') {
        return ['success' => false, 'error' => 'Username wajib diisi.'];
    } elseif ($password !== '' && strlen($password) < 7) {
        return ['success' => false, 'error' => 'Password minimal 7 karakter.'];
    } elseif ($password !== '' && $konfirmasi_password === '') {
        return ['success' => false, 'error' => 'Konfirmasi password wajib diisi.'];
    } elseif ($password !== '' && $password !== $konfirmasi_password) {
        return ['success' => false, 'error' => 'Password dan konfirmasi password tidak sama.'];
    }

    // CEK USERNAME
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");

    mysqli_stmt_bind_param($stmt, "si", $username, $kasir['id_users']);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        return ['success' => false, 'error' => 'Username sudah digunakan.'];
    }
    mysqli_stmt_close($stmt);

    // UPDATE
    mysqli_begin_transaction($conn);
    try {
        // UPDATE USERS
        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmtUser = mysqli_prepare($conn, "UPDATE users SET username = ?, password = ? WHERE id = ? AND role = 'kasir'");

            mysqli_stmt_bind_param($stmtUser, "ssi", $username, $password_hash, $kasir['id_users']);
        } else {
            $stmtUser = mysqli_prepare($conn, "UPDATE users SET username = ? WHERE id = ? AND role = 'kasir'");
            mysqli_stmt_bind_param($stmtUser, "si", $username, $kasir['id_users']);
        }

        if (!mysqli_stmt_execute($stmtUser)) {
            throw new Exception(
                'Gagal mengubah akun kasir.'
            );
        }
        mysqli_stmt_close($stmtUser);

        // UPDATE KASIR
        $stmtKasir = mysqli_prepare($conn, "UPDATE kasir SET nama_kasir = ? WHERE id = ?");

        mysqli_stmt_bind_param($stmtKasir, "si", $nama_kasir, $id);

        if (!mysqli_stmt_execute($stmtKasir)) {
            throw new Exception(
                'Gagal mengubah data kasir.'
            );
        }

        mysqli_stmt_close($stmtKasir);
        mysqli_commit($conn);
        return ['success' => true, 'error' => ''];
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function prosesHapusKasir($id)
{
    global $conn;

    $id = (int) $id;

    if ($id <= 0) {
        return [
            'success' => false,
            'error' => 'ID kasir tidak valid.'
        ];
    }


    // AMBIL DATA KASIR

    $kasir = getKasirByIdAdmin($id);

    if (!$kasir) {
        return [
            'success' => false,
            'error' => 'Data kasir tidak ditemukan.'
        ];
    }


    // CEK RIWAYAT TRANSAKSI

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM transaksi
         WHERE id_kasir = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ((int) $data['total'] > 0) {

        return [
            'success' => false,
            'error' => 'Kasir tidak dapat dihapus karena sudah memiliki riwayat transaksi.'
        ];
    }


    // HAPUS USER
    // Data kasir ikut terhapus karena FK
    // ON DELETE CASCADE

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM users
         WHERE id = ?
         AND role = 'kasir'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $kasir['id_users']
    );

    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        return [
            'success' => false,
            'error' => 'Kasir gagal dihapus.'
        ];
    }

    mysqli_stmt_close($stmt);

    return [
        'success' => true,
        'error' => ''
    ];
}


/*
|--------------------------------------------------------------------------
| QUERY SELECT
|--------------------------------------------------------------------------
*/

function query($sql)
{
    global $conn;

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die(
            "Query gagal: "
            . mysqli_error($conn)
        );
    }

    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}


/*
|--------------------------------------------------------------------------
| AMBIL KASIR
|--------------------------------------------------------------------------
*/

function getKasir()
{
    $kasir = query(
        "SELECT *
         FROM kasir
         LIMIT 1"
    );

    return $kasir[0] ?? null;
}

function getKasirByUserId($user_id)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM kasir
         WHERE id_users = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $kasir = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $kasir;
}


// bikin id transaksi
function generateIdTransaksi($tanggal = null)
{
    global $conn;

    if ($tanggal === null) {
        $tanggal = date('Y-m-d');
    }

    // Ambil jumlah transaksi pada tanggal tersebut
    $stmt = mysqli_prepare(
        $conn,
        "SELECT MAX(CAST(RIGHT(kode_transaksi, 5) AS UNSIGNED)) AS nomor_terakhir FROM transaksi WHERE DATE(tanggal) = ? AND kode_transaksi IS NOT NULL AND kode_transaksi <> ''"
    );

    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan kode transaksi: "
            . mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param($stmt, "s", $tanggal);

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_error($conn);

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Gagal mengambil nomor transaksi: "
            . $error
        );
    }

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    $nomor_terakhir = (int) ($data['nomor_terakhir'] ?? 0);
    $nomor_baru = $nomor_terakhir + 1;

    return 'TRS-' . date('Ymd', strtotime($tanggal)) . '-' . str_pad($nomor_baru, 5, '0', STR_PAD_LEFT);
}

function getAdminTransaksi($search = '', $page = 1, $perPage = 10)
{
    global $conn;

    $search = trim($search);

    $page = (int) $page;

    if ($page < 1) {
        $page = 1;
    }

    $perPage = (int) $perPage;

    if ($perPage <= 0) {
        $perPage = 10;
    }

    $offset = ($page - 1) * $perPage;

    $where = '';

    if ($search !== '') {

        $searchSafe = mysqli_real_escape_string(
            $conn,
            $search
        );

        $where = "
            WHERE
                t.kode_transaksi LIKE '%$searchSafe%'
                OR CAST(t.id AS CHAR) LIKE '%$searchSafe%'
                OR c.nama LIKE '%$searchSafe%'
                OR k.nama_kasir LIKE '%$searchSafe%'
                OR DATE_FORMAT(t.tanggal, '%d-%m-%Y') LIKE '%$searchSafe%'
        ";
    }

    /*
     * HITUNG TOTAL DATA
     */

    $totalData = query("
        SELECT COUNT(*) AS total
        FROM transaksi t

        INNER JOIN customer c
            ON c.id = t.id_customer

        INNER JOIN kasir k
            ON k.id = t.id_kasir

        $where
    ");

    $totalTransaksi = (int) (
        $totalData[0]['total'] ?? 0
    );

    $totalPages = max(
        1,
        (int) ceil($totalTransaksi / $perPage)
    );

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    /*
     * AMBIL DATA
     */

    $transaksi = query("
        SELECT
            t.id,
            t.kode_transaksi,
            t.id_customer,
            t.id_kasir,
            t.total_barang,
            t.total_harga,
            t.uang_bayar,
            t.uang_kembali,
            t.tanggal,

            c.nama AS nama_customer,

            k.nama_kasir

        FROM transaksi t

        INNER JOIN customer c
            ON c.id = t.id_customer

        INNER JOIN kasir k
            ON k.id = t.id_kasir

        $where

        ORDER BY
            t.tanggal DESC,
            t.id DESC

        LIMIT $perPage
        OFFSET $offset
    ");

    return [
        'search' => $search,
        'perPage' => $perPage,
        'page' => $page,
        'offset' => $offset,
        'totalTransaksi' => $totalTransaksi,
        'totalPages' => $totalPages,
        'transaksi' => $transaksi
    ];
}

function prosesTransaksiKasir(
    $nama_customer,
    $alamat_customer,
    $no_hp_customer,
    $id_barang,
    $jumlah_barang,
    $uang_bayar
) {
    $nama_customer = trim($nama_customer);
    $alamat_customer = trim($alamat_customer);
    $no_hp_customer = trim($no_hp_customer);

    $uang_bayar = (int) $uang_bayar;

    /*
     * VALIDASI CUSTOMER
     */

    if ($nama_customer === '') {
        throw new Exception(
            'Nama customer wajib diisi.'
        );
    }

    /*
     * KASIR
     */

    $kasir = getKasirByUserId(
        $_SESSION['user']['id']
    );

    if (!$kasir) {
        throw new Exception(
            'Data kasir belum terhubung dengan akun.'
        );
    }

    $id_kasir = (int) $kasir['id'];

    if ($id_kasir <= 0) {
        throw new Exception(
            'Kasir tidak ditemukan.'
        );
    }

    /*
     * BARANG
     */

    if (empty($id_barang)) {
        throw new Exception(
            'Tidak ada produk yang dipilih.'
        );
    }

    /*
     * CUSTOMER
     */

    $id_customer = getOrCreateCustomer(
        $nama_customer,
        $alamat_customer,
        $no_hp_customer
    );

    /*
     * DETAIL
     */

    $dataTransaksi = siapkanDetailTransaksi(
        $id_barang,
        $jumlah_barang
    );

    /*
     * SIMPAN
     */

    $hasil = simpanTransaksi(
        $id_customer,
        $id_kasir,
        $uang_bayar,
        $dataTransaksi
    );

    /*
     * DATA TOKO
     */

    $toko = getToko();

    if (!$toko) {
        throw new Exception(
            'Data toko belum tersedia.'
        );
    }

    return [
        'kode_transaksi' => $hasil['kode_transaksi'],
        'detail_transaksi' => $dataTransaksi['detail'],
        'tanggal_transaksi' => date('d-m-Y H:i:s'),
        'nama_kasir' => $kasir['nama_kasir'],
        'nama_customer' => $nama_customer,
        'alamat_customer' => $alamat_customer,
        'no_hp_customer' => $no_hp_customer,
        'toko' => $toko,
        'hasil' => $hasil
    ];
}

function getAdminDetailTransaksi($id)
{
    global $conn;

    $id = (int) $id;

    if ($id <= 0) {
        return null;
    }

    /*
     * DATA TRANSAKSI
     */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            t.id,
            t.kode_transaksi,
            t.total_barang,
            t.total_harga,
            t.uang_bayar,
            t.uang_kembali,
            t.tanggal,

            c.nama AS nama_customer,
            c.alamat,
            c.no_hp,

            k.nama_kasir

        FROM transaksi t

        INNER JOIN customer c
            ON c.id = t.id_customer

        INNER JOIN kasir k
            ON k.id = t.id_kasir

        WHERE t.id = ?

        LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $transaksi = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$transaksi) {
        return null;
    }

    /*
     * DETAIL BARANG
     */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            dt.id,
            dt.id_barang,
            dt.harga_barang,
            dt.jumlah_barang,
            dt.total_harga,

            b.nama_barang

        FROM detail_transaksi dt

        INNER JOIN barang b
            ON b.id = dt.id_barang

        WHERE dt.id_transaksi = ?

        ORDER BY dt.id ASC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $detail = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $detail[] = $row;
    }

    mysqli_stmt_close($stmt);

    return [
        'transaksi' => $transaksi,
        'detail' => $detail
    ];
}


/*
|--------------------------------------------------------------------------
| AMBIL BARANG
|--------------------------------------------------------------------------
*/

function getBarang()
{
    return query(
        "SELECT *
         FROM barang
         ORDER BY nama_barang ASC"
    );
}

function tambahBarang($nama_barang, $harga_barang, $stok, $gambar)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO barang
        (nama_barang, harga_barang, stok, gambar)
        VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "siis",
        $nama_barang,
        $harga_barang,
        $stok,
        $gambar
    );

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_error($conn);
        mysqli_stmt_close($stmt);

        throw new Exception("Gagal menyimpan barang: " . $error);
    }

    mysqli_stmt_close($stmt);
}

function prosesTambahBarang(
    $nama_barang,
    $harga_barang,
    $stok,
    $file_gambar
) {
    $error = '';

    if ($nama_barang === '') {
        $error = 'Nama barang wajib diisi.';
    } elseif ($harga_barang < 0) {
        $error = 'Harga barang tidak boleh negatif.';
    } elseif ($stok < 0) {
        $error = 'Stok tidak boleh negatif.';
    }

    $nama_gambar = null;

    if ($error === '' && isset($file_gambar) && $file_gambar['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($file_gambar['error'] !== UPLOAD_ERR_OK) {
            $error = 'Gagal mengupload gambar.';
        } elseif ($file_gambar['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 2 MB.';
        } else {

            $tmp_file = $file_gambar['tmp_name'];
            $info_gambar = getimagesize($tmp_file);

            if ($info_gambar === false) {
                $error = 'File yang diupload bukan gambar yang valid.';
            } else {

                $allowed_mime = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $mime = $info_gambar['mime'];

                if (!isset($allowed_mime[$mime])) {
                    $error = 'Format gambar harus JPG, PNG, atau WEBP.';
                } else {

                    $extension = $allowed_mime[$mime];

                    $nama_gambar =
                        uniqid('barang_', true)
                        . '.'
                        . $extension;

                    $folder_gambar = __DIR__ . '/img/';

                    if (!is_dir($folder_gambar)) {
                        mkdir($folder_gambar, 0755, true);
                    }

                    $tujuan = $folder_gambar . $nama_gambar;

                    if (!move_uploaded_file(
                        $tmp_file,
                        $tujuan
                    )) {
                        $error = 'Gagal menyimpan gambar.';
                        $nama_gambar = null;
                    }
                }
            }
        }
    }

    if ($error !== '') {
        return [
            'success' => false,
            'error' => $error
        ];
    }

    try {

        tambahBarang(
            $nama_barang,
            $harga_barang,
            $stok,
            $nama_gambar
        );

        return [
            'success' => true,
            'error' => ''
        ];

    } catch (Exception $e) {

        if ($nama_gambar !== null) {

            $file_gambar =
                __DIR__
                . '/img/'
                . basename($nama_gambar);

            if (file_exists($file_gambar)) {
                unlink($file_gambar);
            }
        }

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function ubahBarang($id, $nama_barang, $harga_barang, $stok, $gambar)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE barang
         SET nama_barang = ?,
             harga_barang = ?,
             stok = ?,
             gambar = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "siisi",
        $nama_barang,
        $harga_barang,
        $stok,
        $gambar,
        $id
    );

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_error($conn);
        mysqli_stmt_close($stmt);

        throw new Exception("Gagal mengubah barang: " . $error);
    }

    mysqli_stmt_close($stmt);
}

function prosesEditBarang(
    $id,
    $nama_barang,
    $harga_barang,
    $stok,
    $file_gambar
) {
    $id = (int) $id;
    $nama_barang = trim($nama_barang);
    $harga_barang = (int) $harga_barang;
    $stok = (int) $stok;

    // Cek ID
    if ($id <= 0) {
        return [
            'success' => false,
            'error' => 'ID barang tidak valid.'
        ];
    }

    // Ambil data barang lama
    $barang_lama = getBarangById($id);

    if (!$barang_lama) {
        return [
            'success' => false,
            'error' => 'Barang tidak ditemukan.'
        ];
    }

    // Validasi
    if ($nama_barang === '') {
        return [
            'success' => false,
            'error' => 'Nama barang wajib diisi.'
        ];
    }

    if ($harga_barang < 0) {
        return [
            'success' => false,
            'error' => 'Harga barang tidak boleh negatif.'
        ];
    }

    if ($stok < 0) {
        return [
            'success' => false,
            'error' => 'Stok tidak boleh negatif.'
        ];
    }

    // Default: tetap gunakan gambar lama
    $nama_gambar = $barang_lama['gambar'];
    $gambar_baru_path = null;

    // Kalau user upload gambar baru
    if (
        isset($file_gambar) &&
        $file_gambar['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($file_gambar['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'Gagal mengupload gambar.'
            ];
        }

        // Maksimal 2 MB
        if ($file_gambar['size'] > 2 * 1024 * 1024) {
            return [
                'success' => false,
                'error' => 'Ukuran gambar maksimal 2 MB.'
            ];
        }

        // Cek apakah file benar-benar gambar
        $info_gambar = @getimagesize(
            $file_gambar['tmp_name']
        );

        if ($info_gambar === false) {
            return [
                'success' => false,
                'error' => 'File yang diupload bukan gambar yang valid.'
            ];
        }

        // Cek MIME
        $allowed_mime = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        $mime = $info_gambar['mime'];

        if (!isset($allowed_mime[$mime])) {
            return [
                'success' => false,
                'error' => 'Format gambar harus JPG, PNG, atau WEBP.'
            ];
        }

        $extension = $allowed_mime[$mime];

        // Nama file baru
        $nama_gambar =
            uniqid('barang_', true)
            . '.'
            . $extension;

        $folder_gambar = __DIR__ . '/img/';

        if (!is_dir($folder_gambar)) {
            mkdir($folder_gambar, 0755, true);
        }

        $gambar_baru_path =
            $folder_gambar . $nama_gambar;

        // Pindahkan gambar baru
        if (!move_uploaded_file(
            $file_gambar['tmp_name'],
            $gambar_baru_path
        )) {

            return [
                'success' => false,
                'error' => 'Gagal menyimpan gambar baru.'
            ];
        }
    }

    // Update database
    try {

        ubahBarang(
            $id,
            $nama_barang,
            $harga_barang,
            $stok,
            $nama_gambar
        );

    } catch (Exception $e) {

        // Kalau database gagal, hapus gambar baru
        if (
            $gambar_baru_path !== null &&
            file_exists($gambar_baru_path)
        ) {
            unlink($gambar_baru_path);
        }

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }

    // Kalau berhasil dan ada gambar baru,
    // hapus gambar lama
    if (
        $gambar_baru_path !== null &&
        !empty($barang_lama['gambar'])
    ) {

        $gambar_lama_path =
            __DIR__
            . '/img/'
            . basename($barang_lama['gambar']);

        if (file_exists($gambar_lama_path)) {
            unlink($gambar_lama_path);
        }
    }

    return [
        'success' => true,
        'error' => ''
    ];
}

function prosesHapusBarang($id)
{
    global $conn;

    $barang = getBarangById($id);

    if (!$barang) {
        return [
            'success' => false,
            'error' => 'Barang tidak ditemukan.'
        ];
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS jumlah
         FROM detail_transaksi
         WHERE id_barang = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ((int) $data['jumlah'] > 0) {
        return [
            'success' => false,
            'error' => 'Barang tidak dapat dihapus karena sudah digunakan dalam transaksi.'
        ];
    }

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM barang WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        return [
            'success' => false,
            'error' => 'Barang gagal dihapus.'
        ];
    }

    mysqli_stmt_close($stmt);

    if (!empty($barang['gambar'])) {

        $file_gambar =
            __DIR__
            . '/img/'
            . basename($barang['gambar']);

        if (file_exists($file_gambar)) {
            unlink($file_gambar);
        }
    }

    return [
        'success' => true,
        'error' => ''
    ];
}

/*
|--------------------------------------------------------------------------
| CARI CUSTOMER
|--------------------------------------------------------------------------
*/

function cariCustomer($nama, $no_hp)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT id FROM customer WHERE nama = ? AND no_hp = ? LIMIT 1");

    mysqli_stmt_bind_param($stmt, "ss", $nama, $no_hp);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);    
    $customer = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $customer;
}


/*
|--------------------------------------------------------------------------
| SIMPAN CUSTOMER
|--------------------------------------------------------------------------
*/

function simpanCustomer(
    $nama, $alamat, $no_hp
) {
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO customer (nama, alamat, no_hp) VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $nama,
        $alamat,
        $no_hp
    );

    if (!mysqli_stmt_execute($stmt)) {

        $error =
            mysqli_error($conn);

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Gagal menyimpan customer: "
            . $error
        );
    }

    $id_customer =
        mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);

    return $id_customer;
}


/*
|--------------------------------------------------------------------------
| CARI ATAU BUAT CUSTOMER
|--------------------------------------------------------------------------
*/

function getOrCreateCustomer($nama, $alamat, $no_hp) {
    $customer =
        cariCustomer(
            $nama,
            $no_hp
        );

    if ($customer) {
        return (int) $customer['id'];
    }

    return simpanCustomer(
        $nama,
        $alamat,
        $no_hp
    );
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA BARANG BERDASARKAN ID
|--------------------------------------------------------------------------
*/

function getBarangById($id)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, nama_barang, harga_barang, stok, gambar
         FROM barang
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $barang = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $barang;
}


/*
|--------------------------------------------------------------------------
| SIAPKAN DETAIL TRANSAKSI
|--------------------------------------------------------------------------
*/

function siapkanDetailTransaksi(
    $id_barang,
    $jumlah_barang
) {
    if (
        count($id_barang)
        !==
        count($jumlah_barang)
    ) {
        throw new Exception(
            "Data produk tidak valid."
        );
    }

    $total_barang = 0;
    $total_harga = 0;

    $detail = [];


    for (
        $i = 0;
        $i < count($id_barang);
        $i++
    ) {

        $id =
            (int) $id_barang[$i];

        $jumlah =
            (int) $jumlah_barang[$i];


        if ($id <= 0) {
            throw new Exception(
                "ID produk tidak valid."
            );
        }


        if ($jumlah <= 0) {
            throw new Exception(
                "Jumlah produk tidak valid."
            );
        }


        $barang =
            getBarangById($id);


        if (!$barang) {
            throw new Exception(
                "Produk tidak ditemukan."
            );
        }


        if (
            $jumlah >
            (int) $barang['stok']
        ) {

            throw new Exception(
                "Stok "
                . $barang['nama_barang']
                . " tidak mencukupi."
            );

        }


        $harga =
            (int) $barang['harga_barang'];

        $subtotal =
            $harga * $jumlah;


        $total_barang += $jumlah;

        $total_harga += $subtotal;


        $detail[] = [
            'id_barang' =>
                $id,

            'harga_barang' =>
                $harga,

            'jumlah_barang' =>
                $jumlah,

            'total_harga' =>
                $subtotal
        ];
    }


    if ($total_barang <= 0) {
        throw new Exception(
            "Jumlah barang tidak boleh 0."
        );
    }


    return [
        'detail' =>
            $detail,

        'total_barang' =>
            $total_barang,

        'total_harga' =>
            $total_harga
    ];
}


/*
|--------------------------------------------------------------------------
| SIMPAN TRANSAKSI
|--------------------------------------------------------------------------
*/

function simpanTransaksi(
    $id_customer,
    $id_kasir,
    $uang_bayar,
    $dataTransaksi
) {
    global $conn;


    $total_barang =
        $dataTransaksi['total_barang'];

    $total_harga =
        $dataTransaksi['total_harga'];

    $detail =
        $dataTransaksi['detail'];


    if ($uang_bayar < $total_harga) {

        throw new Exception(
            "Uang bayar kurang."
        );

    }


    $uang_kembali = $uang_bayar - $total_harga;


    mysqli_begin_transaction($conn);


    try {

        /*
        |--------------------------------------------------------------------------
        | INSERT TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $tanggal = date('Y-m-d H:i:s');

        $kode_transaksi = generateIdTransaksi(date('Y-m-d', strtotime($tanggal)));

        $stmt =
            mysqli_prepare(
                $conn,
                "INSERT INTO transaksi
                (
                    kode_transaksi,
                    id_customer,
                    id_kasir,
                    total_barang,
                    total_harga,
                    uang_bayar,
                    uang_kembali,
                    tanggal
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

        if (!$stmt) {
            throw new Exception(
                mysqli_error($conn)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
            "siiiiiis",
            $kode_transaksi,
            $id_customer,
            $id_kasir,
            $total_barang,
            $total_harga,
            $uang_bayar,
            $uang_kembali,
            $tanggal
        );


        if (
            !mysqli_stmt_execute($stmt)
        ) {

            throw new Exception(
                mysqli_error($conn)
            );

        }


        $id_transaksi =
            mysqli_insert_id($conn);


        mysqli_stmt_close($stmt);


        /*
        |--------------------------------------------------------------------------
        | INSERT DETAIL
        |--------------------------------------------------------------------------
        */

        foreach ($detail as $item) {

            $stmt =
                mysqli_prepare(
                    $conn,
                    "INSERT INTO detail_transaksi
                    (
                        id_transaksi,
                        id_barang,
                        harga_barang,
                        jumlah_barang,
                        total_harga
                    )
                    VALUES (?, ?, ?, ?, ?)"
                );

            if (!$stmt) {
                throw new Exception(
                    mysqli_error($conn)
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "iiiii",
                $id_transaksi,
                $item['id_barang'],
                $item['harga_barang'],
                $item['jumlah_barang'],
                $item['total_harga']
            );

            if (
                !mysqli_stmt_execute($stmt)
            ) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }

            mysqli_stmt_close($stmt);

            /*
            |--------------------------------------------------------------------------
            | UPDATE STOK
            |--------------------------------------------------------------------------
            */

            $stmt =
                mysqli_prepare(
                    $conn,
                    "UPDATE barang
                     SET stok = stok - ?
                     WHERE id = ?
                     AND stok >= ?"
                );

            if (!$stmt) {
                throw new Exception(
                    mysqli_error($conn)
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "iii",
                $item['jumlah_barang'],
                $item['id_barang'],
                $item['jumlah_barang']
            );

            if (
                !mysqli_stmt_execute($stmt)
            ) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }

            if (
                mysqli_stmt_affected_rows($stmt)
                !== 1
            ) {

                throw new Exception(
                    "Stok produk tidak mencukupi."
                );

            }

            mysqli_stmt_close($stmt);
        }

        mysqli_commit($conn);

        return [
            'kode_transaksi' => $kode_transaksi,
            'id_transaksi' => $id_transaksi,
            'total_barang' => $total_barang,
            'total_harga' => $total_harga,
            'uang_bayar' => $uang_bayar,
            'uang_kembali' => $uang_kembali
        ];

    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}


/*
|--------------------------------------------------------------------------
| PENGATURAN TOKO
|--------------------------------------------------------------------------
*/

function getToko()
{
    global $conn;

    $result = mysqli_query($conn, "SELECT * FROM toko LIMIT 1");

    if (!$result) {
        throw new Exception(
            "Gagal mengambil data toko: " . mysqli_error($conn) 
        );
    }

    return mysqli_fetch_assoc($result);
}


function updateToko(
    $nama_toko,
    $no_hp_toko,
    $alamat_toko,
    $logo_toko = null
) {
    global $conn;

    if ($logo_toko !== null) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE toko
             SET
                nama_toko = ?,
                no_hp_toko = ?,
                alamat_toko = ?,
                logo_toko = ?
             WHERE id = 1"
        );

        if (!$stmt) {
            throw new Exception(
                "Gagal menyiapkan pengaturan toko: "
                . mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $nama_toko,
            $no_hp_toko,
            $alamat_toko,
            $logo_toko
        );

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE toko
             SET
                nama_toko = ?,
                no_hp_toko = ?,
                alamat_toko = ?
             WHERE id = 1"
        );

        if (!$stmt) {
            throw new Exception(
                "Gagal menyiapkan pengaturan toko: "
                . mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $nama_toko,
            $no_hp_toko,
            $alamat_toko
        );
    }

    if (!mysqli_stmt_execute($stmt)) {

        $error = mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        throw new Exception(
            "Gagal menyimpan pengaturan toko: "
            . $error
        );
    }

    mysqli_stmt_close($stmt);

    return true;
}

function prosesPengaturanToko(
    $nama_toko,
    $no_hp_toko,
    $alamat_toko,
    $file_logo
) {

    $nama_toko = trim($nama_toko);
    $no_hp_toko = trim($no_hp_toko);
    $alamat_toko = trim($alamat_toko);

    $toko = getToko();

    if (!$toko) {
        return [
            'success' => false,
            'error' => 'Data toko belum tersedia.'
        ];
    }

    /*
     * VALIDASI
     */

    if ($nama_toko === '') {
        return [
            'success' => false,
            'error' => 'Nama toko wajib diisi.'
        ];
    }

    if ($no_hp_toko === '') {
        return [
            'success' => false,
            'error' => 'No. HP toko wajib diisi.'
        ];
    }

    if ($alamat_toko === '') {
        return [
            'success' => false,
            'error' => 'Alamat toko wajib diisi.'
        ];
    }

    $logo_toko = $toko['logo_toko'];

    $folderLogo = __DIR__ . '/img/';

    /*
     * UPLOAD LOGO
     */

    if (
        isset($file_logo) &&
        $file_logo['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($file_logo['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'Gagal mengupload logo.'
            ];
        }

        if ($file_logo['size'] > 2 * 1024 * 1024) {
            return [
                'success' => false,
                'error' => 'Ukuran logo maksimal 2 MB.'
            ];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mime = finfo_file(
            $finfo,
            $file_logo['tmp_name']
        );

        finfo_close($finfo);

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedMime[$mime])) {
            return [
                'success' => false,
                'error' => 'Logo harus JPG, PNG, atau WEBP.'
            ];
        }

        $extension = $allowedMime[$mime];

        $namaFile = 'logo-toko-' . time() . '.' . $extension;

        if (!is_dir($folderLogo)) {
            mkdir($folderLogo, 0755, true);
        }

        $pathLogo = $folderLogo . $namaFile;

        if (!move_uploaded_file(
            $file_logo['tmp_name'],
            $pathLogo
        )) {
            return [
                'success' => false,
                'error' => 'Logo gagal disimpan.'
            ];
        }

        $logo_toko = $namaFile;
    }

    /*
     * UPDATE DATABASE
     */

    try {

        updateToko(
            $nama_toko,
            $no_hp_toko,
            $alamat_toko,
            $logo_toko
        );

        /*
        * HAPUS LOGO LAMA
        */

        if (
            $logo_toko !== $toko['logo_toko'] &&
            !empty($toko['logo_toko'])
        ) {

            $logoLama = $folderLogo . basename(
                $toko['logo_toko']
            );

            if (is_file($logoLama)) {
                unlink($logoLama);
            }
        }

        return [
            'success' => true,
            'error' => ''
        ];

    } catch (Exception $e) {

        /*
        * UPDATE DATABASE GAGAL
        * Hapus logo baru supaya tidak menjadi file sampah
        */

        if (
            $logo_toko !== $toko['logo_toko'] &&
            !empty($logo_toko)
        ) {

            $logoBaru = $folderLogo . basename($logo_toko);

            if (is_file($logoBaru)) {
                unlink($logoBaru);
            }
        }

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}