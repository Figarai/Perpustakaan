<?php
// =============================================
// books/index.php - CRUD Buku
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

session_start();
require_once '../config.php';
cekLogin();

$success = '';
$error   = '';

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $id         = (int)($_POST['id'] ?? 0);
    $cat_id     = (int)$_POST['category_id'];
    $judul      = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $penulis    = mysqli_real_escape_string($conn, trim($_POST['penulis']));
    $tahun      = (int)$_POST['tahun_terbit'];
    $stok       = (int)$_POST['stok'];
    $cover_name = '';

    // Upload cover
    if (!empty($_FILES['cover']['name'])) {
        $ext      = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) {
            $error = 'Format cover tidak didukung. Gunakan JPG, PNG, atau GIF.';
        } else {
            $cover_name = 'cover_' . time() . '.' . $ext;
            $upload_dir = '../assets/covers/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            move_uploaded_file($_FILES['cover']['tmp_name'], $upload_dir . $cover_name);
        }
    }

    if (!$error) {
        if ($id > 0) {
            // UPDATE
            $cover_sql = $cover_name ? ", cover='$cover_name'" : '';
            mysqli_query($conn, "UPDATE books SET category_id=$cat_id, judul='$judul',
                penulis='$penulis', tahun_terbit=$tahun, stok=$stok $cover_sql WHERE id=$id");
            $success = 'Buku berhasil diperbarui!';
        } else {
            // INSERT
            mysqli_query($conn, "INSERT INTO books (category_id, judul, penulis, tahun_terbit, stok, cover)
                VALUES ($cat_id, '$judul', '$penulis', $tahun, $stok, '$cover_name')");
            $success = 'Buku berhasil ditambahkan!';
        }
    }
}

// DELETE
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM books WHERE id=$id");
    $success = 'Buku berhasil dihapus!';
}

// Data untuk form edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $r  = mysqli_query($conn, "SELECT * FROM books WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($r);
}

// READ
$books      = mysqli_query($conn, "SELECT b.*, c.category_name FROM books b
              LEFT JOIN categories c ON b.category_id = c.id ORDER BY b.id DESC");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header d-flex justify-between align-center">
                <div>
                    <h1>📚 Data Buku</h1>
                    <p>Kelola koleksi buku perpustakaan</p>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="?form=1" class="btn btn-primary">+ Tambah Buku</a>
                <?php endif; ?>
            </div>

            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <!-- Form Tambah / Edit (hanya admin) -->
            <?php if ($_SESSION['role'] === 'admin' && (isset($_GET['form']) || $edit_data)): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h3><?= $edit_data ? 'Edit Buku' : 'Tambah Buku Baru' ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="form-group">
                                <label>Judul Buku *</label>
                                <input type="text" name="judul" class="form-control" required
                                       value="<?= $edit_data ? htmlspecialchars($edit_data['judul']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Penulis *</label>
                                <input type="text" name="penulis" class="form-control" required
                                       value="<?= $edit_data ? htmlspecialchars($edit_data['penulis']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Kategori *</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php
                                    mysqli_data_seek($categories, 0);
                                    while ($cat = mysqli_fetch_assoc($categories)):
                                    ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ($edit_data && $edit_data['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tahun Terbit</label>
                                <input type="number" name="tahun_terbit" class="form-control"
                                       min="1900" max="<?= date('Y') ?>"
                                       value="<?= $edit_data ? $edit_data['tahun_terbit'] : date('Y') ?>">
                            </div>
                            <div class="form-group">
                                <label>Stok *</label>
                                <input type="number" name="stok" class="form-control" required min="0"
                                       value="<?= $edit_data ? $edit_data['stok'] : '1' ?>">
                            </div>
                            <div class="form-group">
                                <label>Cover Buku (JPG/PNG)</label>
                                <input type="file" name="cover" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <button type="submit" name="simpan" class="btn btn-primary">
                            <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Buku' ?>
                        </button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabel Buku -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Cover</th>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Kategori</th>
                                    <th>Tahun</th>
                                    <th>Stok</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($books)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <?php if ($row['cover'] && file_exists('../assets/covers/' . $row['cover'])): ?>
                                            <img src="../assets/covers/<?= $row['cover'] ?>" class="book-cover" alt="Cover">
                                        <?php else: ?>
                                            <div class="cover-placeholder">📖</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['judul']) ?></td>
                                    <td><?= htmlspecialchars($row['penulis']) ?></td>
                                    <td><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                                    <td><?= $row['tahun_terbit'] ?></td>
                                    <td>
                                        <span class="badge <?= $row['stok'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $row['stok'] ?>
                                        </span>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td>
                                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="#" onclick="konfirmasiHapus('?hapus=<?= $row['id'] ?>','<?= htmlspecialchars($row['judul']) ?>')"
                                           class="btn btn-sm btn-danger">Hapus</a>
                                    </td>
                                    <?php endif; ?>
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
</body>
</html>
