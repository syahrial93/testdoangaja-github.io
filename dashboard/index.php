<?php
require_once("../dbconnection.php");
session_start();

// Definisikan APP_NAME
define('APP_NAME', 'PERUMNAS CAFE - BUKU TAMU');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?message=silahkan login terlebih dahulu");
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("location:../index.php?message=logout berhasil");
}

// Daftar role yang diizinkan mengakses halaman registrasi
$allowed_roles = ['admin', 'manager', 'supervisor'];
?>

<!-- Masuk ke dashboard -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="../dashboard/style.css" />
    <title><?php echo APP_NAME; ?></title>
</head>

<body>
    <div class="right-corner">
        <section>
            <h3><?php echo $_SESSION['full_name']; ?></h3>
            <p><?php echo $_SESSION['role']; ?></p>
            <form action="" method="POST">
                <button name="logout" type="submit">logout</button>
            </form>
        </section>
    </div>

    <input type="checkbox" id="check">
    <label for="check">
        <i class="material-icons" id="btn">menu</i>
        <i class="material-icons" id="cancel">close</i>
    </label>
    <div class="sidebar">
        <img src="../images/Perumnas1.png" alt="Logo" class="logo">
        <ul>
            <li><a href="index.php"><i class="material-icons">dashboard</i> Dashboard</a></li>

            <!-- Menampilkan menu registrasi untuk admin, manager, dan supervisor -->
            <?php if (in_array($_SESSION['role'], $allowed_roles)): ?>
            <li><a href="../registrasi.php"><i class="material-icons">token</i> Setup</a></li>
            <?php endif; ?>

            <li><a href="../meja.php"><i class="material-icons">table_bar</i> Meja</a></li>
            <li><a href="../menu.php"><i class="material-icons">menu</i> Menu</a></li>
            <li><a href="../order.php"><i class="material-icons">mic alert</i> Order</a></li>
            <li><a href="../payment.php"><i class="material-icons">payments</i> Payment</a></li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
                    <i class="material-icons">report</i> Laporan
                </a>
                <ul class="dropdown-menu">
                    <li><a href="../kas.php">Laporan Kas</a></li>
                    <li><a href="../history.php">History</a></li>
                    <!-- <li><a href="../laporan/stok_perlengkapan.php">Stok Perlengkapan</a></li>
                    <li><a href="../laporan/peralatan_masak.php">Peralatan Masak</a></li> -->
                </ul>
            </li>
        </ul>
    </div>

    <section class="section1">
        <div class="container">
            <h1>Selamat Datang</h1>
            <h2>Kami siap melayani anda</h2>
        </div>
    </section>



    <script>
    // Fungsi untuk toggle class 'show' pada dropdown
    function toggleDropdown(event) {
        event.preventDefault(); // Mencegah default action link

        var parentLi = event.target.closest('li'); // Mengambil elemen li terdekat
        parentLi.classList.toggle(
            'show'); // Menambahkan/menghapus class 'show' untuk menampilkan/menyembunyikan dropdown
    }
    </script>


</body>




</html>