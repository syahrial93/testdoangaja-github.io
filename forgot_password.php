<?php
include("dbconnection.php");

if (isset($_POST['submit'])) {
    $username = $_POST['username'];  // Menggunakan username atau employee_id

    // Query untuk mencari username di database
    $sql = "SELECT * FROM users WHERE employee_id = '$username'";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        // Jika username ditemukan, redirect ke halaman reset password
        header("Location: reset_password.php?username=$username");
    } else {
        echo "<p>Username tidak ditemukan.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../perumnascafe/dashboard/styleforgotpass.css">
    <title>Lupa Password</title>
</head>

<body>
    <h2>Lupa Password</h2>
    <form action="forgot_password.php" method="POST">
        <label for="username">Username (Employee ID):</label>
        <input type="text" name="username" required />
        <button type="submit" name="submit">Lanjutkan</button>
    </form>
</body>

</html>