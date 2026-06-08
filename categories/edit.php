<?php
session_start();
require_once '../config.php';
cekAdmin();

$pageTitle  = 'Edit Kategori';
$activeMenu = 'categories';
$basePath   = '/perpustakaan';

$id  = (int)($_GET['id'] ?? 0);
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE id = $id LIMIT 1"));

if (!$row) {
    $_SESSION['msg'] = 'Kategori tidak ditemukan.';
    $_SESSION['msg_type'] = 'danger';
    redirect('/perpustakaan/categories/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = bersihkan($conn, $_POST['category_name'] ?? '');

    if (empty($category_name)) {
        $error = 'Nama kategori wajib diisi.';
    } else {
        // Cek duplikat (kecuali diri sendiri)
        $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE category_name = '$category_name' AND id != $id LIMIT 1"));
        if ($cek) {
            $error = 'Kategori dengan nama tersebut sudah ada.';
        } else {
            mysqli_query($conn, "UPDATE categories SET category_name = '$category_name' WHERE id = $id");
            $_SESSION['msg']      = 'Kategori berhasil diperbarui.';
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
            <h6>✏️ Edit Kategori</h6>
            <a href="index.php" class="btn btn-sm btn-secondary">← Kembali</a>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span style="color:red;">*</span></label>
                    <input type="text" name="category_name"
                           class="form-control <?= $error ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($_POST['category_name'] ?? $row['category_name']) ?>"
                           required>
                </div>

                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button type="submit" class="btn btn-primary">💾 Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../assets/layout/footer.php'; ?>
