<?php
include("dbconnection.php");

if (isset($_GET['username'])) {
    $username = $_GET['username'];  // Mendapatkan username yang sudah dikirimkan

    if (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Pastikan password dan konfirmasi password cocok
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password di database
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE employee_id = '$username'";
            if ($db->query($update_query)) {
                // Menampilkan pesan sukses dan redirect ke halaman login setelah 3 detik
                echo "<p>Password berhasil direset. Anda akan diarahkan ke halaman login.</p>";

                // Redirect ke halaman login setelah 3 detik
                header("refresh:3;url=index.php");
            } else {
                echo "<p>Gagal mereset password. Silakan coba lagi.</p>";
            }
        } else {
            echo "<p>Password dan konfirmasi password tidak cocok.</p>";
        }
    }
} else {
    echo "<p>Halaman ini tidak dapat diakses secara langsung.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../perumnascafe/dashboard/styleresetpass.css">
    <title>Reset Password</title>
</head>

<body>
    <h2>Setel Password Baru</h2>
    <form action="reset_password.php?username=<?php echo $_GET['username']; ?>" method="POST">
        <label for="new_password">Password Baru:</label>
        <input type="password" name="new_password" required />

        <label for="confirm_password">Konfirmasi Password Baru:</label>
        <input type="password" name="confirm_password" required />

        <button type="submit" name="reset_password">Setel Password</button>
    </form>
</body>

</html>