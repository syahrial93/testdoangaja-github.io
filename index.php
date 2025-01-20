<!-- index.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css" />
    <title>Login</title>
</head>

<body>
    <div class="container">
        <section class="wrapper">
            <h3 class="title">E-kasir</h3>

            <!-- notifikasi -->
            <?php
            if (isset($_GET['message'])) {
                $msg = $_GET['message'];
                echo "<div class='notif-login'>$msg</div>";
            }
            ?>

            <div>
                <form action="login.php" method="POST" class="form-login">
                    <label>Username</label>
                    <input placeholder="nip" name="nip" type="number" class="input-login" required />

                    <label>Password</label>
                    <input placeholder="******" name="password" type="password" class="input-login" required />

                    <button type="submit" class="button-login" name="login">login</button>
                </form>

                <!-- Link lupa password -->
                <div class="forgot-password">
                    <a href="forgot_password.php">Lupa Password?</a>
                </div>
            </div>
        </section>
    </div>
</body>

</html>