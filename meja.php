<?php
require_once("../perumnascafe/dbconnection.php");
session_start();

define("APP_NAME", "PERUMNAS CAFE - BUKU MENU");

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?message=silahkan login terlebih dahulu");
}

// Query untuk mengambil data meja yang tidak kosong atau NULL
$query = "SELECT * FROM daftar_meja WHERE nm_meja IS NOT NULL AND nm_meja != '' AND jlh_kursi IS NOT NULL AND jlh_kursi != '' AND status IS NOT NULL AND status != ''";
$result = mysqli_query($db, $query);

// Periksa apakah ada data
if (!$result) {
    die("Query gagal: " . mysqli_error($db));
}

// Proses penambahan meja
if (isset($_POST['add_meja'])) {
    $nm_meja = trim($_POST['nm_meja']);
    $jlh_kursi = trim($_POST['jlh_kursi']);
    $status = trim($_POST['status']);

    // Validasi data input: Pastikan tidak ada kolom yang kosong
    if (empty($nm_meja) || empty($jlh_kursi) || empty($status)) {
        echo "<script>alert('Semua kolom harus diisi!');</script>";
    } else {
        // Cek apakah nomor meja sudah ada di database
        $check_query = "SELECT * FROM daftar_meja WHERE nm_meja = '$nm_meja'";
        $check_result = mysqli_query($db, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // Jika nomor meja sudah ada
            echo "<script>alert('Nomor meja sudah ada, mohon pilih nomor meja lain');</script>";
        } else {
            // Jika nomor meja belum ada, lanjutkan dengan proses insert
            $query = "INSERT INTO daftar_meja (nm_meja, jlh_kursi, status)
                      VALUES ('$nm_meja', '$jlh_kursi', '$status')";

            if (mysqli_query($db, $query)) {
                echo "<script>alert('Meja berhasil ditambahkan'); window.location.href = window.location.href = '../perumnascafe/dashboard/index.php';</script>";
            } else {
                echo "Error: " . mysqli_error($db);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com" rel="stylesheet" />
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/stylemeja.css">
</head>

<body class="meja-page">
    <div class="container">
        <div class="meja">
            <h2>DAFTAR MEJA</h2>
            <!-- Tombol Add untuk menampilkan form -->
            <button onclick="document.getElementById('addForm').style.display='block'">Add Meja</button>

            <table>
                <tr>
                    <th>No.</th>
                    <th>Nomor Meja</th>
                    <th>Kursi (Bh)</th>
                    <th>Status</th>
                </tr>
                <?php
                $no = 1; // Nomor urut
                // Hanya menampilkan data yang valid (tidak kosong atau NULL)
                $data = mysqli_query($db, "SELECT * FROM daftar_meja WHERE nm_meja != '' AND jlh_kursi != '' AND status != ''");

                while ($row = mysqli_fetch_assoc($data)) {
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nm_meja']); ?></td>
                    <td><?php echo htmlspecialchars($row['jlh_kursi']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
                <?php
                }
                ?>
            </table>

            <div id="addForm">
                <h3>Tambah Meja Baru</h3>
                <form action="" method="POST">
                    <label for="nm_meja">Nomor Meja:</label><br>
                    <input type="number" id="nm_meja" name="nm_meja" required><br><br>

                    <label for="jlh_kursi">Jumlah Kursi:</label><br>
                    <input type="number" id="jlh_kursi" name="jlh_kursi" required><br><br>

                    <label for="status">Status:</label><br>
                    <select id="status" name="status">
                        <option value="tersedia">tersedia</option>
                        <option value="tidak tersedia">tidak tersedia</option>
                    </select><br><br>
                    <input type="submit" name="add_meja" value="Tambah Meja">
                    <button type="button"
                        onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    const addForm = document.getElementById('addForm');
    const formOverlay = document.createElement('div');
    formOverlay.id = 'formOverlay';
    document.body.appendChild(formOverlay);

    // Tombol "Add Meja"
    document.querySelector('button[onclick]').addEventListener('click', () => {
        addForm.classList.add('show');
        formOverlay.classList.add('show');
    });

    // Tombol "Cancel" di dalam form
    document.querySelector('#addForm button[type="button"]').addEventListener('click', () => {
        addForm.classList.remove('show');
        formOverlay.classList.remove('show');
    });

    // Tutup form ketika klik pada overlay
    formOverlay.addEventListener('click', () => {
        addForm.classList.remove('show');
        formOverlay.classList.remove('show');
    });
    </script>
</body>

</html>