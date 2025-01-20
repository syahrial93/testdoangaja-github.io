<?php
require_once("../perumnascafe/dbconnection.php");
session_start();

define("APP_NAME", "PERUMNAS CAFE - FORM PESANAN");

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?message=silahkan login terlebih dahulu");
}

// Query untuk mendapatkan daftar nomor meja dari tabel meja
$query_meja = "SELECT nm_meja FROM daftar_meja WHERE status = 'tersedia'";
$result_meja = mysqli_query($db, $query_meja);
if (!$result_meja) {
    die("Query gagal: " . mysqli_error($db));
}

// Query untuk mendapatkan daftar menu dan detail harga serta kategori
$query_menu = "SELECT nm_makanan, harga, kategori FROM tbl_menu WHERE status = 'Tersedia'";
$result_menu = mysqli_query($db, $query_menu);
if (!$result_menu) {
    die("Query gagal: " . mysqli_error($db));
}

// Fungsi untuk menghasilkan angka acak integer
function generateRandomInteger($length = 6)
{
    return rand(pow(10, $length - 1), pow(10, $length) - 1);  // Angka acak dengan panjang tertentu
}

// Proses jika form dikirimkan
if (isset($_POST['submit_order'])) {
    $nm_meja = $_POST['nm_meja'];
    $menu_pesanan = $_POST['menu_pesanan'];  // Array menu pesanan
    $nama_pelanggan = $_POST['nm_pelanggan'];

    // Perulangan untuk setiap menu yang dipesan
    foreach ($menu_pesanan as $menu) {
        $jumlah = $_POST['jumlah'][$menu]; // Jumlah pesanan untuk menu tersebut

        // Ambil harga dan kategori dari menu berdasarkan menu yang dipilih
        $query_get_menu = "SELECT harga, kategori FROM tbl_menu WHERE nm_makanan = '$menu' LIMIT 1";
        $result_get_menu = mysqli_query($db, $query_get_menu);
        if ($row_menu = mysqli_fetch_assoc($result_get_menu)) {
            $harga = $row_menu['harga'];
            $kategori = $row_menu['kategori'];

            // Hitung total harga
            $total_harga = $harga * $jumlah;

            // Generate angka acak untuk id_pesanan dan id_transaksi
            $id_pesanan = generateRandomInteger(6); // ID pesanan acak dengan 6 digit
            $id_transaksi = generateRandomInteger(6); // ID transaksi acak dengan 6 digit

            // Masukkan data ke tabel tbl_pesanan
            $query_order = "INSERT INTO tbl_pesanan (id_pesanan, nm_pelanggan, nmr_meja, menu_pesanan, total_harga, kategori)
                            VALUES ('$id_pesanan', '$nama_pelanggan', '$nm_meja', '$menu', '$total_harga', '$kategori')";

            if (mysqli_query($db, $query_order)) {
                // Masukkan data ke tabel tbl_payment
                $query_payment = "INSERT INTO tbl_payment (id_transaksi, id_pesanan, nm_pelanggan, menu_pesanan, jlh_porsi, tgl_pembayaran, tgl_dibuat)
                      VALUES ('$id_transaksi', '$id_pesanan', '$nama_pelanggan', '$menu', '$jumlah', NOW(), NOW())";

                if (!mysqli_query($db, $query_payment)) {
                    echo "Error Pembayaran: " . mysqli_error($db);
                }
            } else {
                echo "Error Pesanan: " . mysqli_error($db);
            }
        } else {
            echo "Menu $menu tidak ditemukan.";
        }
    }

    echo "<script>alert('Pesanan berhasil ditambahkan dan pembayaran tercatat'); window.location.href='order.php';</script>";
}

// Query untuk mengambil semua data pesanan dan menggabungkan menu yang dipesan oleh satu pelanggan
$query_pesanan = "
    SELECT
        tbl_pesanan.nm_pelanggan,
        tbl_pesanan.nmr_meja,
        GROUP_CONCAT(CONCAT(tbl_pesanan.menu_pesanan, ' (x', tbl_payment.jlh_porsi, ') - Rp ', tbl_menu.harga) SEPARATOR ', ') AS menu_pesanan,
        SUM(tbl_payment.jlh_porsi * tbl_menu.harga) AS total_harga
    FROM tbl_pesanan
    JOIN tbl_payment ON tbl_pesanan.id_pesanan = tbl_payment.id_pesanan
    JOIN tbl_menu ON tbl_pesanan.menu_pesanan = tbl_menu.nm_makanan
    GROUP BY tbl_pesanan.nm_pelanggan, tbl_pesanan.nmr_meja
    ORDER BY tbl_pesanan.nm_pelanggan ASC
";
$result_pesanan = mysqli_query($db, $query_pesanan);
if (!$result_pesanan) {
    die("Query gagal: " . mysqli_error($db));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/styleformpesan.css">
</head>

<body>
    <div class="container">
        <button type="button" id="toggleFormBtn">Tambah Pesanan</button>
    </div>

    <!-- Form Pesanan (hidden by default) -->
    <div class="container form-container" id="formContainer">
        <h2>Form Pesanan</h2>
        <form action="" method="POST">
            <!-- Input Nama Pelanggan -->
            <label for="nm_pelanggan">Nama Pelanggan:</label>
            <input type="text" id="nm_pelanggan" name="nm_pelanggan" required><br><br>

            <!-- Pilihan Nomor Meja -->
            <label for="nm_meja">Nomor Meja:</label>
            <select name="nm_meja" id="nm_meja" required>
                <option value="" disabled selected>Pilih Nomor Meja</option>
                <?php while ($row_meja = mysqli_fetch_assoc($result_meja)) { ?>
                    <option value="<?= $row_meja['nm_meja'] ?>"><?= $row_meja['nm_meja'] ?></option>
                <?php } ?>
            </select><br><br>

            <!-- Pilihan Menu (Checkbox untuk memilih banyak menu) -->
            <label for="menu_pesanan">Menu Pesanan:</label>
            <?php while ($row_menu = mysqli_fetch_assoc($result_menu)) { ?>
                <div>
                    <input type="checkbox" name="menu_pesanan[]" value="<?= $row_menu['nm_makanan'] ?>"
                        data-harga="<?= $row_menu['harga'] ?>" data-kategori="<?= $row_menu['kategori'] ?>">
                    <?= $row_menu['nm_makanan'] ?> - Rp <?= number_format($row_menu['harga'], 2, ',', '.') ?>
                </div>
            <?php } ?><br>

            <!-- Jumlah Pesanan untuk setiap menu -->
            <div id="jumlah-container"></div><br>

            <button type="submit" name="submit_order">Tambah Pesanan</button>
        </form>
    </div>

    <div class="container">
        <h2>Daftar Pesanan</h2>
        <!-- Tampilkan Daftar Pesanan -->
        <table border="1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Nomor Meja</th>
                    <th>Menu Pesanan</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($row_pesanan = mysqli_fetch_assoc($result_pesanan)) {
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row_pesanan['nm_pelanggan'] ?></td>
                        <td><?= $row_pesanan['nmr_meja'] ?></td>
                        <td><?= $row_pesanan['menu_pesanan'] ?></td>
                        <td><?= "Rp " . number_format($row_pesanan['total_harga'], 2, ',', '.') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        // Toggle form visibility
        const toggleFormBtn = document.getElementById('toggleFormBtn');
        const formContainer = document.getElementById('formContainer');

        toggleFormBtn.addEventListener('click', () => {
            formContainer.classList.toggle('form-container');
            if (formContainer.classList.contains('form-container')) {
                toggleFormBtn.textContent = "Tambah Pesanan";
            } else {
                toggleFormBtn.textContent = "Tutup Form Pesanan";
            }
        });

        // Menampilkan jumlah pesanan untuk setiap menu yang dipilih
        document.querySelectorAll('input[name="menu_pesanan[]"]').forEach((checkbox) => {
            checkbox.addEventListener('change', function() {
                const menuName = this.value;
                const jumlahContainer = document.getElementById('jumlah-container');

                // Jika checkbox dipilih, tampilkan input jumlah untuk menu tersebut
                if (this.checked) {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <label for="jumlah_${menuName}">Jumlah ${menuName}:</label>
                        <input type="number" name="jumlah[${menuName}]" id="jumlah_${menuName}" min="1" required><br><br>
                    `;
                    jumlahContainer.appendChild(div);
                } else {
                    // Jika checkbox tidak dipilih, hapus input jumlah yang terkait
                    const div = document.getElementById('jumlah_' + menuName);
                    if (div) div.remove();
                }
            });
        });
    </script>
</body>

</html>