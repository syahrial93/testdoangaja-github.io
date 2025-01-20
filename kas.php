<?php
require_once("../perumnascafe/dbconnection.php");

session_start();

define("APP_NAME", "PERUMNAS CAFE - DATA KAS");

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?message=silahkan login terlebih dahulu");
    exit;
}

// Ambil nilai filter dari form
$filter_id_transaksi = isset($_POST['id_transaksi']) ? mysqli_real_escape_string($db, $_POST['id_transaksi']) : '';
$filter_ket = isset($_POST['ket']) ? mysqli_real_escape_string($db, $_POST['ket']) : '';

// Query utama dengan filter
$query_kas = "SELECT * FROM tbl_kas WHERE 1=1";

if (!empty($filter_id_transaksi)) {
    $query_kas .= " AND id_transaksi LIKE '%$filter_id_transaksi%'";
}
if (!empty($filter_ket)) {
    $query_kas .= " AND ket LIKE '%$filter_ket%'";
}

// Jalankan query
$result_kas = mysqli_query($db, $query_kas);
if (!$result_kas) {
    die("Query gagal: " . mysqli_error($db));
}

// Tambahkan transaksi baru ke tabel kas
if (isset($_POST['add_kas'])) {
    $id_transaksi = mysqli_real_escape_string($db, $_POST['id_transaksi']);
    $debet = mysqli_real_escape_string($db, $_POST['debet']);
    $kredit = mysqli_real_escape_string($db, $_POST['kredit']);
    $ket = mysqli_real_escape_string($db, $_POST['ket']);

    $query_insert = "
        INSERT INTO tbl_kas (id_transaksi, debet, kredit, ket)
        VALUES ('$id_transaksi', '$debet', '$kredit', '$ket')
    ";

    if (mysqli_query($db, $query_insert)) {
        header("Location: kas.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($db);
    }
}

// ** Otomatis isi tbl_kas berdasarkan tbl_payment **
$query_payment_completed = "
    SELECT p.id_transaksi, ps.menu_pesanan, p.jlh_pembayaran
    FROM tbl_payment p
    JOIN tbl_pesanan ps ON p.id_pesanan = ps.id_pesanan
    WHERE p.status = 'Completed' AND NOT EXISTS (
        SELECT 1 FROM tbl_kas k WHERE k.id_transaksi = p.id_transaksi
    )
";

$result_payment_completed = mysqli_query($db, $query_payment_completed);

while ($row = mysqli_fetch_assoc($result_payment_completed)) {
    $id_transaksi = $row['id_transaksi'];
    $menu_pesanan = $row['menu_pesanan'];
    $jlh_pembayaran = $row['jlh_pembayaran'];

    $query_auto_insert = "
        INSERT INTO tbl_kas (id_transaksi, debet, kredit, ket)
        VALUES ('$id_transaksi', '$jlh_pembayaran', 0, '$menu_pesanan')
    ";
    mysqli_query($db, $query_auto_insert);
}

// Hitung total debet dan total kredit
$query_total = "
    SELECT SUM(debet) AS total_debet, SUM(kredit) AS total_kredit
    FROM tbl_kas
    WHERE 1=1
";

if (!empty($filter_id_transaksi)) {
    $query_total .= " AND id_transaksi LIKE '%$filter_id_transaksi%'";
}
if (!empty($filter_ket)) {
    $query_total .= " AND ket LIKE '%$filter_ket%'";
}

$result_total = mysqli_query($db, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_debet = $row_total['total_debet'];
$total_kredit = $row_total['total_kredit'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/stylekas.css">
</head>

<body class="kas-page">
    <div class="container">
        <h2>Data Kas</h2>

        <!-- Form Filter -->
        <form method="POST" action="">
            <label for="id_transaksi">ID Transaksi:</label>
            <input type="text" name="id_transaksi" value="<?= $filter_id_transaksi ?>">
            <label for="ket">Keterangan:</label>
            <input type="text" name="ket" value="<?= $filter_ket ?>">
            <button type="submit">Filter</button>
        </form>

        <!-- Form Tambah Transaksi -->
        <form method="POST" action="" class="add-form">
            <h3>Tambah Transaksi</h3>
            <label for="id_transaksi">ID Transaksi:</label>
            <input type="text" name="id_transaksi" required>
            <label for="debet">Debet:</label>
            <input type="number" name="debet" step="0.01" min="0">
            <label for="kredit">Kredit:</label>
            <input type="number" name="kredit" step="0.01" min="0">
            <label for="ket">Keterangan:</label>
            <textarea name="ket" required></textarea>
            <button type="submit" name="add_kas">Tambah</button>
        </form>

        <!-- Tabel untuk menampilkan data kas -->
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>ID Transaksi</th>
                    <th>Debet</th>
                    <th>Kredit</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($result_kas)) {
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['id_transaksi'] ?></td>
                    <td>Rp <?= number_format($row['debet'], 2, ',', '.') ?></td>
                    <td>Rp <?= number_format($row['kredit'], 2, ',', '.') ?></td>
                    <td><?= $row['Ket'] ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Grand Total -->
        <div class="grand-total">
            <p><strong>Total Debet: </strong>Rp <?= number_format($total_debet, 2, ',', '.') ?></p>
            <p><strong>Total Kredit: </strong>Rp <?= number_format($total_kredit, 2, ',', '.') ?></p>
        </div>
    </div>
</body>

</html>