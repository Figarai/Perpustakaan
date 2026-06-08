<?php
// =============================================
// dashboard.php - Halaman Dashboard
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

session_start();
require_once 'config.php';
cekLogin();

// Statistik
$total_buku      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM books"))['total'];
$total_kategori  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM categories"))['total'];
$total_user      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];
$total_pinjam    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="main-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>!</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card stat-blue">
                    <div class="stat-icon">📚</div>
                    <div class="stat-info">
                        <h3><?= $total_buku ?></h3>
                        <p>Total Buku</p>
                    </div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">🏷️</div>
                    <div class="stat-info">
                        <h3><?= $total_kategori ?></h3>
                        <p>Kategori</p>
                    </div>
                </div>
                <div class="stat-card stat-orange">
                    <div class="stat-icon">👤</div>
                    <div class="stat-info">
                        <h3><?= $total_user ?></h3>
                        <p>Siswa Terdaftar</p>
                    </div>
                </div>
                <div class="stat-card stat-red">
                    <div class="stat-icon">📖</div>
                    <div class="stat-info">
                        <h3><?= $total_pinjam ?></h3>
                        <p>Sedang Dipinjam</p>
                    </div>
                </div>
            </div>

            <!-- Tabel Peminjaman Terbaru -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Peminjaman Terbaru</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Peminjam</th>
                                    <th>Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.*, u.name as nama_user, b.judul
                                          FROM peminjaman p
                                          JOIN users u ON p.user_id = u.id
                                          JOIN books b ON p.book_id = b.id
                                          ORDER BY p.id DESC LIMIT 5";
                                $result = mysqli_query($conn, $query);
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_user']) ?></td>
                                    <td><?= htmlspecialchars($row['judul']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                    <td><?= $row['qty'] ?></td>
                                    <td>
                                        <span class="badge <?= $row['status'] === 'dipinjam' ? 'badge-warning' : 'badge-success' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
