<?php
session_start();

// Arahkan pengguna kembali ke halaman login
session_unset();  // Menghapus semua variabel sesi
session_destroy();  // Menghancurkan sesi
header("Location: ../auth/index.php");  // Halaman login
exit();

// Jika pengguna ingin logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hapus session
    session_destroy();
    header("Location: ../auth/index.php"); // Arahkan ke halaman login
    exit();
}
?>

<?php 
ob_start();
session_start();
require_once '../database/koneksi.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/index.php");
    exit();
}
?>