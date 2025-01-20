<?php

include("dbconnection.php");
include("Users.php");
session_start(); // agar session nerjalan kalau tida maka tidak berjalan



$user = new Users();

if (isset($_POST['login'])) {

  if (strlen($_POST['nip']) <= 2 || strlen($_POST['password']) <= 2) {
    header("location:index.php?message=data yang anda input tidak valid");
  } else {
    $user->set_login_data($_POST['nip'], $_POST['password']);
    $id = $user->get_employee_id();
    $password = $_POST['password'];  // Ambil password dari form login

    // Query untuk mencari data pengguna berdasarkan employee_id
    $sql = "SELECT * FROM users WHERE employee_id = '$id'";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      // Verifikasi password dengan password_verify()
      if (password_verify($password, $row['password'])) {
        // Set session jika login berhasil
        $_SESSION['status'] = "login";
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['employee_id'] = $row['employee_id'];
        $_SESSION['role'] = $row['role'];

        header("location:dashboard/index.php");
      } else {
        header("location:index.php?message=data yang anda masukan salah");
      }
    } else {
      header("location:index.php?message=data yang anda masukan salah");
    }
  }
}