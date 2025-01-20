<?php
require_once("../perumnascafe/dbconnection.php");
session_start();

define('APP_NAME', 'PERUMNAS CAFE - BUKU TAMU');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?message=silahkan login terlebih dahulu");
}

// Query untuk mengambil data menu
$query = "SELECT * FROM tbl_pesanan"; // Sesuaikan dengan nama tabel menu di database Anda
$result = mysqli_query($db, $query);

// Periksa apakah ada data
if (!$result) {
    die("Query gagal: " . mysqli_error($db));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com" rel="stylesheet" />
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/style1.css"> <!-- Jika diperlukan -->
</head>

<body class="order-page">
    <div class="container">

        <div class="order">
            <h2>Pesanan</h2>
            <table>
                <tr>
                    <th>No.</th>
                    <th>Kode Pesanan</th>
                    <th>Meja</th>
                    <th>Menu</th>
                    <th>Total Harga</th>
                    <th>Kategori</th>
                </tr>
                <?php
                $no = 1; // Nomor urut
                $data = mysqli_query($db, "select * from tbl_pesanan");
                while ($row = mysqli_fetch_assoc($data)) {
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['id_pesanan']; ?></td>
                    <td><?php echo $row['nmr_meja']; ?></td>
                    <td><?php echo $row['menu_pesanan']; ?></td>
                    <td><?php echo "Rp " . number_format($row['harga'], 2, ',', '.'); ?></td>
                    <td><?php echo $row['kategori']; ?></td>



                    <td>
                        <a href="#">Edit</a> |
                        <a href="#">Hapus</a> |
                    </td>
                </tr>
                <?php
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>