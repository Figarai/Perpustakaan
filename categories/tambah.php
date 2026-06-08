<?php
session_start();
require_once '../config.php';
cekAdmin();

$pageTitle  = 'Tambah Kategori';
$activeMenu = 'categories';
$basePath   = '/perpustakaan';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = bersihkan($conn, $_POST['category_name'] ?? '');

    if (empty($category_name)) {
        $error = 'Nama kategori wajib diisi.';
    } else {
        // Cek duplikat
        $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE category_name = '$category_name' LIMIT 1"));
        if ($cek) {
            $error = 'Kategori dengan nama tersebut sudah ada.';
        } else {
            mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$category_name')");
            $_SESSION['msg']      = 'Kategori berhasil ditambahkan.';
            $_SESSION['msg_type'] = 'success';
            redirect('/perpustakaan/categories/index.php');
        }
    }
}

include '../assets/layout/header.php';
?>

<div style="max-width:500px;">
    <div class="card">
        <div class="card-header">
            <h6>➕ Tambah Kategori Buku</h6>
            <a href="index.php" class="btn btn-sm btn-secondary">← Kembali</a>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span style="color:red;">*</span></label>
                    <input type="text" name="category_name" class="form-control <?= $error ? 'is-invalid' : '' ?>"
                           placeholder="Contoh: Pemrograman, Matematika..."
                           value="<?= htmlspecialchars($_POST['category_name'] ?? '') ?>"
                           required>
                </div>

                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../assets/layout/footer.php'; ?>
