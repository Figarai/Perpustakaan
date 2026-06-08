<?php
// =============================================
// config.php - Koneksi Database
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

$conn = mysqli_connect("localhost", "root", "", "perpustakaan");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper: cek login
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('../login.php');
    }
}

// Helper: cek role admin
function cekAdmin() {
    cekLogin();
    if ($_SESSION['role'] !== 'admin') {
        redirect('../dashboard.php');
    }
}
?>
