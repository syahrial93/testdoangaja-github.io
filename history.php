<?php
require_once("../perumnascafe/dbconnection.php");

// Fungsi untuk menyinkronkan data dari tbl_payment ke tbl_history
function syncHistory($db)
{
    $query = "
        INSERT INTO tbl_history (id_transaksi, nm_pelanggan, menu_pesanan, jlh_pembayaran, tgl_pembayaran)
        SELECT id_transaksi, nm_pelanggan, menu_pesanan, jlh_pembayaran, tgl_pembayaran
        FROM tbl_payment
        WHERE id_transaksi NOT IN (SELECT id_transaksi FROM tbl_history);
    ";
}

// Fungsi untuk menampilkan semua data dari tbl_history
function getHistoryData($db)
{
    $query = "SELECT * FROM tbl_history ORDER BY tgl_pembayaran DESC";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellspacing='0' cellpadding='5'>";
        echo "<tr>
                <th>Tanggal Pembayaran</th>
                <th>ID Transaksi</th>
                <th>Nama Pelanggan</th>
                <th>Menu Pesanan</th>
                <th>Jumlah Pembayaran</th>
                <th>Aksi</th>
              </tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['tgl_pembayaran']}</td>
                    <td>{$row['id_transaksi']}</td>
                    <td>{$row['nm_pelanggan']}</td>
                    <td>{$row['menu_pesanan']}</td>
                    <td>Rp " . number_format($row['jlh_pembayaran'], 2, ',', '.') . "</td>
                    <td>
                        <form method='POST' action='' style='display:inline;' onsubmit='return confirmDelete();'>
                            <input type='hidden' name='action' value='delete'>
                            <input type='hidden' name='id_transaksi' value='{$row['id_transaksi']}'>
                            <button type='submit'>Hapus</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada data di tabel history.</p>";
    }
}

// Fungsi untuk menghapus data dari tbl_history berdasarkan id_transaksi
function deleteFromHistory($db, $id_transaksi)
{
    $query = "DELETE FROM tbl_history WHERE id_transaksi = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_transaksi);

    if (mysqli_stmt_execute($stmt)) {
        // Data berhasil dihapus, tampilkan notifikasi sukses
        echo "<script>showNotification('Data dengan ID Transaksi $id_transaksi berhasil dihapus!', 'success');</script>";
    } else {
        // Terjadi error saat penghapusan, tampilkan notifikasi error
        echo "<script>showNotification('Error: " . mysqli_error($db) . "', 'error');</script>";
    }
    mysqli_stmt_close($stmt);
}

// Sinkronisasi data setiap kali halaman dimuat
syncHistory($db);

// Jika ada permintaan POST untuk penghapusan data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete' && isset($_POST['id_transaksi'])) {
        $id_transaksi = $_POST['id_transaksi'];
        deleteFromHistory($db, $id_transaksi);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tabel History</title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/historystyle.css"> <!-- Menghubungkan CSS eksternal -->
</head>

<body>
    <h2>Data History</h2>
    <div>
        <?php getHistoryData($db); ?>
    </div>

    <!-- Notifikasi -->
    <div id="notification" class="notification">
        <span id="notification-message"></span>
    </div>

    <script>
        // Function to display notification with custom message and type
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationMessage = document.getElementById('notification-message');

            // Set message and style based on notification type
            notificationMessage.innerText = message;
            notification.classList.remove('success', 'error');
            notification.classList.add(type);

            // Show notification with animation
            notification.classList.add('show');

            // Auto hide notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('fadeOut');
            }, 3000);
        }

        // Confirm deletion before proceeding
        function confirmDelete() {
            // If the user confirms the deletion, show success message
            if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
                showNotification("Data berhasil dihapus!", "success");
                return true;
            }
            // If the user cancels the deletion, show error message
            else {
                showNotification("Penghapusan data dibatalkan.", "error");
                return false;
            }
        }
    </script>
</body>

</html>