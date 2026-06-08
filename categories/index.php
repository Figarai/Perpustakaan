<?php
// =============================================
// categories/index.php - CRUD Kategori
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

session_start();
require_once '../config.php';
cekAdmin();

$success = '';
$error   = '';

// CREATE
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['category_name']));
    if ($nama !== '') {
        mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$nama')");
        $success = 'Kategori berhasil ditambahkan!';
    } else {
        $error = 'Nama kategori tidak boleh kosong.';
    }
}

// UPDATE
if (isset($_POST['edit'])) {
    $id   = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn, trim($_POST['category_name']));
    if ($nama !== '') {
        mysqli_query($conn, "UPDATE categories SET category_name='$nama' WHERE id=$id");
        $success = 'Kategori berhasil diperbarui!';
    } else {
        $error = 'Nama kategori tidak boleh kosong.';
    }
}

// DELETE
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    $success = 'Kategori berhasil dihapus!';
}

// Ambil data edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM categories WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($result);
}

// READ semua
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1>🏷️ Manajemen Kategori</h1>
            </div>

            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <!-- Form Tambah / Edit -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3><?= $edit_data ? 'Edit Kategori' : 'Tambah Kategori' ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>
                        <div class="d-flex gap-2">
                            <input type="text" name="category_name" class="form-control"
                                   placeholder="Nama Kategori"
                                   value="<?= $edit_data ? htmlspecialchars($edit_data['category_name']) : '' ?>"
                                   required style="max-width: 320px;">
                            <button type="submit" name="<?= $edit_data ? 'edit' : 'tambah' ?>"
                                    class="btn btn-<?= $edit_data ? 'warning' : 'primary' ?>">
                                <?= $edit_data ? 'Simpan Perubahan' : 'Tambah' ?>
                            </button>
                            <?php if ($edit_data): ?>
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Kategori -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kategori</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($categories)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                                    <td>
                                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="#" onclick="konfirmasiHapus('?hapus=<?= $row['id'] ?>','<?= htmlspecialchars($row['category_name']) ?>')"
                                           class="btn btn-sm btn-danger">Hapus</a>
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
</body>
</html>
