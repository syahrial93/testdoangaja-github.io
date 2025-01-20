<?php

$hostname = "localhost";
$username = "root";
$password = "";
$db_name = "perumnascafe";


$db = new mysqli($hostname, $username, $password, $db_name);

// Mengecek koneksi
if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}