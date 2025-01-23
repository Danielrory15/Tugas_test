<?php
$servername = "localhost"; // Nama server, biasanya localhost
$username = "root";        // Username database MySQL Anda
$password = "";            // Password MySQL Anda (kosong jika default)
$dbname = "user_management"; // Nama database

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
