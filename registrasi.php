<?php
// Kode PHP untuk mengambil role dari database
require_once("../perumnascafe/dbconnection.php");
session_start();

// Cek koneksi database
if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}

// Ambil data role dari database
$query = "SELECT * FROM tbl_roles";  // Query untuk mengambil role
$result = $db->query($query);  // Menggunakan $db untuk query

// Proses registrasi jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $fullname = $_POST['fullname'];  // Ambil data fullname dari form
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi input
    if (empty($employee_id) || empty($fullname) || empty($password) || empty($role)) {
        $_SESSION['message'] = "Semua field harus diisi!";
        $_SESSION['message_type'] = "error";
    } else {
        // Hash password untuk keamanan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Masukkan data ke database
        $stmt = $db->prepare("INSERT INTO users (employee_id, full_name, password, role) VALUES (?, ?, ?, ?)");

        // Periksa jika query prepare gagal
        if ($stmt === false) {
            die('Query prepare gagal: ' . $db->error);
        }

        // Mengikat parameter
        $stmt->bind_param("ssss", $employee_id, $fullname, $hashedPassword, $role);

        // Eksekusi query
        if ($stmt->execute()) {
            $_SESSION['message'] = "Registrasi berhasil!";
            $_SESSION['message_type'] = "success";
            header("Location: registrasi.php");
            exit();  // Pastikan untuk berhenti setelah redirect
        } else {
            $_SESSION['message'] = "Terjadi kesalahan: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }

        // Tutup statement
        $stmt->close();
    }
}

// Menutup koneksi database
$db->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="../perumnascafe/dashboard/styleregistrasi.css">
</head>

<body>
    <h1>Registrasi</h1>

    <!-- Menampilkan Notifikasi -->
    <?php if (isset($_SESSION['message'])): ?>
    <div class="notif-login <?php echo $_SESSION['message_type']; ?>">
        <p><?php echo $_SESSION['message']; ?></p>
        <a href="../perumnascafe/dashboard/index.php" class="notif-button">Kembali ke Halaman Utama</a>
        <!-- Tombol Kembali -->
        <?php
            unset($_SESSION['message']);  // Menghapus pesan setelah ditampilkan
            unset($_SESSION['message_type']);  // Menghapus tipe pesan
            ?>
    </div>
    <?php endif; ?>

    <!-- Form Registrasi -->
    <form action="registrasi.php" method="POST">
        <input type="text" name="employee_id" placeholder="Employee ID" required>
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="password" name="password" placeholder="Password" required>

        <label for="role">Role:</label>
        <select name="role" required>
            <option value="" disabled selected>Choose Role</option>
            <?php
            // Menampilkan pilihan role
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['role_name'] . "'>" . ucfirst($row['role_name']) . "</option>";
                }
            } else {
                echo "<option value=''>No roles available</option>";
            }
            ?>
        </select>

        <button type="submit">Register</button>
    </form>

</body>

</html>