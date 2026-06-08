<?php
// =============================================
// report/index.php - Laporan Peminjaman
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

session_start();
require_once '../config.php';
cekAdmin();

// Filter tanggal
$dari   = isset($_GET['dari'])  ? $_GET['dari']  : date('Y-m-01');
$sampai = isset($_GET['sampai'])? $_GET['sampai']: date('Y-m-d');

$query = "SELECT p.*, u.name as nama_user, u.phone, b.judul, b.penulis
          FROM peminjaman p
          JOIN users u ON p.user_id = u.id
          JOIN books b ON p.book_id = b.id
          WHERE p.tanggal_pinjam BETWEEN '$dari' AND '$sampai'
          ORDER BY p.tanggal_pinjam DESC";
$result   = mysqli_query($conn, $query);
$total    = mysqli_num_rows($result);
$dipinjam = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as c FROM peminjaman WHERE status='dipinjam'
     AND tanggal_pinjam BETWEEN '$dari' AND '$sampai'"))['c'];
$kembali  = $total - $dipinjam;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header d-flex justify-between align-center">
                <div>
                    <h1>📊 Laporan Peminjaman</h1>
                    <p>Laporan periode: <?= date('d/m/Y', strtotime($dari)) ?> s/d <?= date('d/m/Y', strtotime($sampai)) ?></p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
                </div>
            </div>

            <!-- Filter Tanggal -->
            <div class="card mb-3 no-print">
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2 align-center">
                        <div class="form-group" style="margin:0">
                            <label style="font-size:0.85rem">Dari</label>
                            <input type="date" name="dari" class="form-control" value="<?= $dari ?>">
                        </div>
                        <div class="form-group" style="margin:0">
                            <label style="font-size:0.85rem">Sampai</label>
                            <input type="date" name="sampai" class="form-control" value="<?= $sampai ?>">
                        </div>
                        <button type="submit" class="btn btn-secondary" style="margin-top:20px">Filter</button>
                    </form>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card stat-blue">
                    <div class="stat-icon">📖</div>
                    <div class="stat-info">
                        <h3><?= $total ?></h3>
                        <p>Total Peminjaman</p>
                    </div>
                </div>
                <div class="stat-card stat-orange">
                    <div class="stat-icon">🕐</div>
                    <div class="stat-info">
                        <h3><?= $dipinjam ?></h3>
                        <p>Masih Dipinjam</p>
                    </div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?= $kembali ?></h3>
                        <p>Sudah Dikembalikan</p>
                    </div>
                </div>
            </div>

            <!-- Tabel Laporan -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Detail Peminjaman</h3>
                </div>
                <div class="card-body">
                    <!-- Header Print -->
                    <div class="print-header" style="display:none; text-align:center; margin-bottom:20px;">
                        <h2>📚 PERPUSTAKAAN SEKOLAH</h2>
                        <h3>LAPORAN PEMINJAMAN BUKU</h3>
                        <p>Periode: <?= date('d/m/Y', strtotime($dari)) ?> s/d <?= date('d/m/Y', strtotime($sampai)) ?></p>
                        <hr>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Peminjam</th>
                                    <th>No HP</th>
                                    <th>Judul Buku</th>
                                    <th>Penulis</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                mysqli_data_seek($result, 0);
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                    <td><?= htmlspecialchars($row['nama_user']) ?></td>
                                    <td><?= $row['phone'] ?: '-' ?></td>
                                    <td><?= htmlspecialchars($row['judul']) ?></td>
                                    <td><?= htmlspecialchars($row['penulis']) ?></td>
                                    <td><?= $row['qty'] ?></td>
                                    <td>
                                        <span class="badge <?= $row['status'] === 'dipinjam' ? 'badge-warning' : 'badge-success' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" style="font-weight:600;">Total</td>
                                    <td colspan="2" style="font-weight:600;"><?= $total ?> transaksi</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <style>
        @media print {
            .print-header { display: block !important; }
        }
    </style>
</body>
</html>
