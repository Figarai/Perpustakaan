<?php
// =============================================
// peminjaman/index.php - Sistem Peminjaman Buku
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

session_start();
require_once '../config.php';
cekLogin();

$success = '';
$error   = '';

// PINJAM BUKU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pinjam'])) {
    $book_id  = (int)$_POST['book_id'];
    $user_id  = $_SESSION['role'] === 'admin' ? (int)$_POST['user_id'] : (int)$_SESSION['user_id'];
    $qty      = (int)$_POST['qty'];
    $tanggal  = date('Y-m-d');

    // Ambil stok buku
    $buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok, judul FROM books WHERE id=$book_id"));

    if (!$buku) {
        $error = 'Buku tidak ditemukan!';
    } elseif ($qty <= 0) {
        $error = 'Jumlah pinjam harus minimal 1!';
    } elseif ($qty > $buku['stok']) {
        $error = "Stok tidak mencukupi! Stok tersedia: {$buku['stok']}";
    } else {
        // Kurangi stok
        mysqli_query($conn, "UPDATE books SET stok = stok - $qty WHERE id=$book_id");
        // Catat peminjaman
        mysqli_query($conn, "INSERT INTO peminjaman (user_id, book_id, tanggal_pinjam, qty, status)
            VALUES ($user_id, $book_id, '$tanggal', $qty, 'dipinjam')");
        $success = "Buku \"{$buku['judul']}\" berhasil dipinjam!";
    }
}

// KEMBALIKAN BUKU
if (isset($_GET['kembali'])) {
    $id = (int)$_GET['kembali'];
    $p  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM peminjaman WHERE id=$id"));
    if ($p && $p['status'] === 'dipinjam') {
        // Kembalikan stok
        mysqli_query($conn, "UPDATE books SET stok = stok + {$p['qty']} WHERE id={$p['book_id']}");
        mysqli_query($conn, "UPDATE peminjaman SET status='dikembalikan' WHERE id=$id");
        $success = 'Buku berhasil dikembalikan!';
    }
}

// READ DATA
if ($_SESSION['role'] === 'admin') {
    $query_pinjam = "SELECT p.*, u.name as nama_user, b.judul FROM peminjaman p
                     JOIN users u ON p.user_id = u.id
                     JOIN books b ON p.book_id = b.id
                     ORDER BY p.id DESC";
} else {
    $uid = (int)$_SESSION['user_id'];
    $query_pinjam = "SELECT p.*, u.name as nama_user, b.judul FROM peminjaman p
                     JOIN users u ON p.user_id = u.id
                     JOIN books b ON p.book_id = b.id
                     WHERE p.user_id = $uid
                     ORDER BY p.id DESC";
}
$peminjaman = mysqli_query($conn, $query_pinjam);
$books      = mysqli_query($conn, "SELECT * FROM books WHERE stok > 0 ORDER BY judul");
$users      = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1>📖 Peminjaman Buku</h1>
                <p>Kelola peminjaman dan pengembalian buku</p>
            </div>

            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <!-- Form Pinjam -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Form Peminjaman Buku</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div style="display:grid; grid-template-columns:<?= $_SESSION['role'] === 'admin' ? '1fr 1fr 1fr' : '1fr 1fr' ?>; gap:16px;">
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <div class="form-group">
                                <label>Peminjam *</label>
                                <select name="user_id" class="form-control" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    <?php while ($u = mysqli_fetch_assoc($users)): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Pilih Buku *</label>
                                <select name="book_id" class="form-control" required onchange="updateStok(this)">
                                    <option value="">-- Pilih Buku --</option>
                                    <?php
                                    mysqli_data_seek($books, 0);
                                    while ($b = mysqli_fetch_assoc($books)):
                                    ?>
                                    <option value="<?= $b['id'] ?>" data-stok="<?= $b['stok'] ?>">
                                        <?= htmlspecialchars($b['judul']) ?> (Stok: <?= $b['stok'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Jumlah *  <span id="info-stok" style="color:#e74c3c; font-size:0.82rem;"></span></label>
                                <input type="number" name="qty" id="qty" class="form-control"
                                       min="1" value="1" required>
                            </div>
                        </div>
                        <button type="submit" name="pinjam" class="btn btn-primary">Pinjam Buku</button>
                    </form>
                </div>
            </div>

            <!-- Tabel Peminjaman -->
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Peminjaman</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?><th>Peminjam</th><?php endif; ?>
                                    <th>Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($peminjaman)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td><?= htmlspecialchars($row['nama_user']) ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($row['judul']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                    <td><?= $row['qty'] ?></td>
                                    <td>
                                        <span class="badge <?= $row['status'] === 'dipinjam' ? 'badge-warning' : 'badge-success' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'dipinjam'): ?>
                                        <a href="?kembali=<?= $row['id'] ?>" class="btn btn-sm btn-success"
                                           onclick="return confirm('Kembalikan buku ini?')">Kembalikan</a>
                                        <?php else: ?>
                                        <span style="color:#888; font-size:0.85rem;">✓ Selesai</span>
                                        <?php endif; ?>
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

    <script src="../assets/js/main.js"></script>
    <script>
        function updateStok(select) {
            const opt   = select.options[select.selectedIndex];
            const stok  = opt.getAttribute('data-stok');
            const info  = document.getElementById('info-stok');
            const qty   = document.getElementById('qty');
            if (stok) {
                info.textContent = '(Maks: ' + stok + ')';
                qty.max = stok;
            } else {
                info.textContent = '';
                qty.removeAttribute('max');
            }
        }
    </script>
</body>
</html>
