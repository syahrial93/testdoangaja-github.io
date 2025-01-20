<?php
require_once("../perumnascafe/dbconnection.php");
session_start();

define("APP_NAME", "PERUMNAS CAFE - BUKU MENU");


// Periksa apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?message=silahkan login terlebih dahulu");
}

// Query untuk mengambil data menu
$query = "SELECT * FROM tbl_menu"; // Sesuaikan dengan nama tabel menu di database Anda
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
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/style1.css">
</head>

<body class="menu-page">
    <div class="container">
        <div class="menu">
            <h2>DAFTAR MENU</h2>
            <!-- Tombol Add untuk menampilkan form -->
            <button onclick="document.getElementById('addForm').style.display='block'">Add Menu</button>

            <table>
                <tr>
                    <th>No.</th>
                    <th>Makan dan Minuman</th>
                    <th>Harga</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>ditambahkan</th>
                    <th>diperbarui</th>
                    <!-- <th>Opsi</th> -->

                </tr>
                <?php
                $no = 1; // Nomor urut
                $data = mysqli_query($db, "select * from tbl_menu");
                while ($row = mysqli_fetch_assoc($data)) {
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['nm_makanan']; ?></td>
                    <td><?php echo "Rp " . number_format($row['harga'], 2, ',', '.'); ?></td>
                    <td><?php echo $row['kategori']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['ditambahkan']; ?></td>
                    <td><?php echo $row['diperbarui']; ?></td>


                    <!-- <td>
                        <a href="#">Edit</a> |
                        <a href="#">Hapus</a>
                    </td> -->
                </tr>
                <?php
                }


                if (isset($_POST['add_menu'])) {
                    $nm_makanan = $_POST['nm_makanan'];
                    $harga = $_POST['harga'];
                    $kategori = $_POST['kategori'];
                    $status = $_POST['status'];

                    // Menambahkan data ke dalam tabel tbl_menu
                    $query = "INSERT INTO tbl_menu (nm_makanan, harga, kategori, status, ditambahkan, diperbarui)
                              VALUES ('$nm_makanan', '$harga', '$kategori', '$status', NOW(), NOW())";

                    // Menjalankan query dan mengecek apakah berhasil
                    if (mysqli_query($db, $query)) {
                        echo "<script>alert('Menu berhasil ditambahkan'); window.location.href='';</script>";
                    } else {
                        echo "Error: " . mysqli_error($db);
                    }
                }




                ?>
            </table>

            <div id="addForm">
                <h3>Tambah Menu Baru</h3>
                <form action="" method="POST">
                    <label for="nm_makanan">Nama Makanan/Minuman:</label><br>
                    <input type="text" id="nm_makanan" name="nm_makanan" required><br><br>

                    <label for="harga">Harga:</label><br>
                    <input type="number" id="harga" name="harga" required><br><br>

                    <label for="kategori">Kategori:</label><br>
                    <select id="kategori" name="kategori">
                        <option value="makanan">Makanan</option>
                        <option value="minuman">Minuman</option>
                    </select><br><br>

                    <label for="status">Status:</label><br>
                    <select id="status" name="status">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                    </select><br><br>
                    <input type="submit" name="add_menu" value="Tambah Menu">
                    <button type="button"
                        onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>