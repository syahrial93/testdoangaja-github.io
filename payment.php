<?php
require_once("../perumnascafe/dbconnection.php");
require_once('../perumnascafe/pdf/fpdf.php');

session_start();

define("APP_NAME", "PERUMNAS CAFE - DATA PEMBAYARAN");

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?message=silahkan login terlebih dahulu");
}

// Filter values
$filter_bulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';
$filter_tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
$filter_nmr_meja = isset($_POST['nmr_meja']) ? $_POST['nmr_meja'] : '';
$filter_nama = isset($_POST['nama']) ? $_POST['nama'] : '';

// Query untuk mengambil data pembayaran dan nomor meja
$query_payment = "
    SELECT
        p.id_transaksi,
        p.id_pesanan,
        p.nm_pelanggan,
        GROUP_CONCAT(CONCAT(ps.menu_pesanan, ' x', p.jlh_porsi) SEPARATOR ', ') AS menu_pesanan,
        ps.nmr_meja,
        p.tgl_pembayaran,
        p.jlh_pembayaran,
        p.mtd_pembayaran,
        p.status,
        p.tgl_dibuat
    FROM tbl_payment p
    JOIN tbl_pesanan ps ON p.id_pesanan = ps.id_pesanan
    WHERE 1=1
";

// Menambahkan kondisi filter terpisah
if ($filter_bulan) {
    $query_payment .= " AND MONTH(p.tgl_pembayaran) = '$filter_bulan'";
}
if ($filter_tanggal) {
    $query_payment .= " AND DAY(p.tgl_pembayaran) = '$filter_tanggal'";
}
if ($filter_nmr_meja) {
    $query_payment .= " AND ps.nmr_meja = '$filter_nmr_meja'";
}
if ($filter_nama) {
    $query_payment .= " AND p.nm_pelanggan LIKE '%$filter_nama%'";
}

$query_payment .= " GROUP BY p.id_transaksi";  // To group the results by transaction ID

$result_payment = mysqli_query($db, $query_payment);

// Periksa apakah query berhasil
if (!$result_payment) {
    die("Query gagal: " . mysqli_error($db));
}

// Proses pembayaran
if (isset($_POST['bayar'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $mtd_pembayaran = $_POST['mtd_pembayaran'];

    // Query untuk mendapatkan total harga dari tbl_pesanan
    $query_total_harga = "SELECT total_harga FROM tbl_pesanan WHERE id_pesanan = (SELECT id_pesanan FROM tbl_payment WHERE id_transaksi = '$id_transaksi')";
    $result_total_harga = mysqli_query($db, $query_total_harga);
    $row_total_harga = mysqli_fetch_assoc($result_total_harga);
    $total_harga = $row_total_harga['total_harga'];

    // Update status pembayaran dan metode pembayaran, serta jumlah pembayaran
    $query_update = "UPDATE tbl_payment
                     SET status = 'Completed',
                         tgl_pembayaran = NOW(),
                         mtd_pembayaran = '$mtd_pembayaran',
                         jlh_pembayaran = '$total_harga'  -- Pastikan kolom ini terisi
                     WHERE id_transaksi = '$id_transaksi'";

    if (mysqli_query($db, $query_update)) {
        // Redirect untuk menghindari masalah resubmission form dan mengarahkan ke halaman dashboard
        header("Location: ../perumnascafe/payment.php");
        exit; // Pastikan tidak ada kode yang dieksekusi lagi setelah redirect
    } else {
        echo "Error: " . mysqli_error($db);
    }
}

// Cetak pembayaran untuk pelanggan tertentu
if (isset($_GET['id_transaksi'])) {
    $id_transaksi = $_GET['id_transaksi'];

    // Query untuk mengambil data pembayaran spesifik
    $query_payment = "SELECT * FROM tbl_payment WHERE id_transaksi = '$id_transaksi'";
    $result_payment = mysqli_query($db, $query_payment);

    if ($row = mysqli_fetch_assoc($result_payment)) {
        $id_pesanan = $row['id_pesanan'];

        // Query untuk mendapatkan total_harga
        $query_total_harga = "SELECT total_harga FROM tbl_pesanan WHERE id_pesanan = '$id_pesanan'";
        $result_total_harga = mysqli_query($db, $query_total_harga);
        $row_total_harga = mysqli_fetch_assoc($result_total_harga);
        $total_harga = $row_total_harga['total_harga'];

        // Generate PDF
        $pdf = new FPDF('P', 'mm', array(80, 180));
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, 'Lembar Pembayaran', 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('arial', '', 10);
        $pdf->Cell(0, 5, 'PERUMNAS CAFE', 0, 1, 'C');

        $pdf->Ln(5);

        // Detail pembayaran
        $pdf->SetLeftMargin(3);
        $pdf->SetX(3);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(35, 6, 'Nama Pelanggan', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['nm_pelanggan'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Kode Transaksi', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['id_transaksi'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Kode Pesanan', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['id_pesanan'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Menu diPesan', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');

        // Menggunakan MultiCell untuk membungkus teks agar tidak keluar halaman
        $pdf->MultiCell(0, 6, $row['menu_pesanan'], 0, 'L');

        $pdf->Cell(35, 6, 'Jumlah Porsi', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['jlh_porsi'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Metode Pembayaran', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['mtd_pembayaran'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Status Pembayaran', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['status'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Tanggal Pembayaran', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['tgl_pembayaran'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Tanggal Dibuat', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, $row['tgl_dibuat'], 0, 1, 'L');

        $pdf->Cell(35, 6, 'Jumlah Pembayaran', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->Cell(0, 6, 'Rp ' . number_format($total_harga, 2, ',', '.'), 0, 1, 'L');

        $pdf->Ln(10);  // Jarak sebelum penutupan

        // Kata-kata penutup
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->MultiCell(0, 6, 'Terima kasih telah berbelanja di Perumnas Cafe! Kami berharap Anda menikmati hidangan kami. Jangan ragu untuk kembali dan menikmati pengalaman kuliner yang lebih menyenangkan bersama kami. Sampai jumpa!', 0, 'C');

        $pdf->Output();
        exit;
    } else {
        echo "Data tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/stylepymnt.css">
</head>

<body class="payment-page">
    <div class="container">
        <h2>Data Pembayaran</h2>

        <!-- Form Filter -->
        <form method="POST" action="">
            <label for="bulan">Bulan:</label>
            <select name="bulan">
                <option value="">Semua</option>
                <?php for ($i = 1; $i <= 12; $i++) { ?>
                <option value="<?= $i ?>" <?= ($filter_bulan == $i) ? 'selected' : ''; ?>>
                    <?= date('F', mktime(0, 0, 0, $i, 10)) ?></option>
                <?php } ?>
            </select>
            <label for="tanggal">Tanggal:</label>
            <input type="date" name="tanggal" value="<?= $filter_tanggal ?>">
            <label for="nmr_meja">Nomor Meja:</label>
            <input type="text" name="nmr_meja" value="<?= $filter_nmr_meja ?>">
            <label for="nama">Nama Pelanggan:</label>
            <input type="text" name="nama" value="<?= $filter_nama ?>">
            <button type="submit">Filter</button>
        </form>

        <!-- Tabel untuk menampilkan data pembayaran -->
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Transaksi</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Menu Dipesan</th>
                    <th>Nomor Meja</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Jumlah Pembayaran</th>
                    <th>Metode Pembayaran</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1; // Nomor urut
                while ($row = mysqli_fetch_assoc($result_payment)) {
                    $id_transaksi = $row['id_transaksi'];
                    $id_pesanan = $row['id_pesanan'];

                    // Query untuk mengambil total_harga dari tbl_pesanan
                    $query_total_harga = "SELECT total_harga FROM tbl_pesanan WHERE id_pesanan = '$id_pesanan'";
                    $result_total_harga = mysqli_query($db, $query_total_harga);
                    $row_total_harga = mysqli_fetch_assoc($result_total_harga);
                    $total_harga = $row_total_harga['total_harga'];
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['id_transaksi'] ?></td>
                    <td><?= $row['id_pesanan'] ?></td>
                    <td><?= $row['nm_pelanggan'] ?></td>
                    <td><?= $row['menu_pesanan'] ?></td>
                    <td><?= $row['nmr_meja'] ?></td>
                    <td><?= $row['tgl_pembayaran'] ?></td>
                    <td>Rp <?= number_format($total_harga, 2, ',', '.') ?></td>
                    <td><?= $row['mtd_pembayaran'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $row['tgl_dibuat'] ?></td>
                    <td>
                        <?php if ($row['status'] != 'Completed') { ?>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="id_transaksi" value="<?= $id_transaksi ?>">
                            <select name="mtd_pembayaran" required>
                                <option value="" disabled selected>Pilih Metode</option>
                                <option value="Cash">Cash</option>
                                <option value="Credit">Credit</option>
                                <option value="E-Wallet">E-Wallet</option>
                            </select>
                            <button type="submit" name="bayar" class="btn-action">Bayar</button>
                        </form>
                        <?php } else { ?>
                        <span class="completed">Sudah Dibayar</span>
                        <?php } ?>
                        <!-- Tombol Print -->
                        <a href="?id_transaksi=<?= $id_transaksi ?>" target="_blank">
                            <button type="button" class="btn-action">Print</button>
                        </a>
                    </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>