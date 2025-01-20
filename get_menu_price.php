<?php
require_once("../perumnascafe/dbconnection.php");

// Ambil data yang dikirimkan oleh request POST
$data = json_decode(file_get_contents("php://input"), true);

// Pastikan menu_pesanan ada dalam request
if (isset($data['menu_pesanan'])) {
    $menu_pesanan = $data['menu_pesanan'];

    // Query untuk mendapatkan harga dari menu yang dipilih
    $query = "SELECT harga FROM tbl_menu WHERE nm_makanan = '$menu_pesanan' LIMIT 1";
    $result = mysqli_query($db, $query);

    // Periksa apakah query berhasil
    if ($result && mysqli_num_rows($result) > 0) {
        // Ambil harga dari hasil query
        $row = mysqli_fetch_assoc($result);
        $harga = $row['harga'];

        // Kirimkan harga sebagai respon dalam format JSON
        echo json_encode(['harga' => $harga]);
    } else {
        // Jika menu tidak ditemukan
        echo json_encode(['error' => 'Menu tidak ditemukan.']);
    }
} else {
    // Jika tidak ada menu_pesanan yang dikirim
    echo json_encode(['error' => 'Menu Pesanan tidak ditemukan.']);
}
