<?php
// Pengaturan Database
define('DB_SERVER', 'Localhost');
define('DB_USERNAME', 'Danny'); 
define('DB_PASSWORD', '159753'); 
define('DB_NAME', 'pengumpulantugas');

// Membuat koneksi ke database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>