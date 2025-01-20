<?php
require_once("../perumnascafe/dbconnection.php");

// Pastikan sudah menerima parameter id_transaksi
if (isset($_POST['hapus'])) {
    $id_transaksi = $_POST['id_transaksi'];

    // Query untuk menghapus pesanan
    $query_delete = "DELETE FROM tbl_payment WHERE id_transaksi = '$id_transaksi'";

    if (mysqli_query($db, $query_delete)) {
        echo "<script>alert('Pesanan berhasil dihapus!'); window.location.href='payment.php';</script>";
    } else {
        echo "Error: " . mysqli_error($db);
    }
}
