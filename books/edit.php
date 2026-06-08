<?php
session_start();
require_once '../config.php';
cekAdmin();

$pageTitle  = 'Edit Buku';
$activeMenu = 'books';
$basePath   = '/perpustakaan';

$id  = (int)($_GET['id'] ?? 0);
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id = $id LIMIT 1"));

if (!$row) {
    $_SESSION['msg'] = 'Buku tidak ditemukan.';
    $_SESSION['msg_type'] = 'danger';
    redirect('/perpustakaan/books/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id  = (int)($_POST['category_id'] ?? 0);
    $judul        = bersihkan($conn, $_POST['judul'] ?? '');
    $penulis      = bersihkan($conn, $_POST['penulis'] ?? '');
    $tahun_terbit = (int)($_POST['tahun_terbit'] ?? date('Y'));
    $stok         = (int)($_POST['stok'] ?? 0);

    if (!$category_id) $errors[] = 'Kategori wajib dipilih.';
    if (empty($judul)) $errors[] = 'Judul buku wajib diisi.';
    if (empty($penulis)) $errors[] = 'Penulis wajib diisi.';
    if ($stok < 0) $errors[] = 'Stok tidak boleh negatif.';

    // Upload cover baru
    $coverName = $row['cover'];
    if (!empty($_FILES['cover']['name'])) {
        $fileExt = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowed)) {
            $errors[] = 'Format gambar tidak valid.';
        } elseif ($_FILES['cover']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran gambar maksimal 2MB.';
        } else {
            $newCover  = uniqid('cover_') . '.' . $fileExt;
            $uploadDir = '../assets/img/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($_FILES['cover']['tmp_name'], $uploadDir . $newCover)) {
                // Hapus cover lama
                if ($row['cover'] && file_exists($uploadDir . $row['cover'])) {
                    unlink($uploadDir . $row['cover']);
                }
                $coverName = $newCover;
            } else {
                $errors[] = 'Gagal mengupload gambar.';
            }
        }
    }

    if (empty($errors)) {
        $coverVal = $coverName ? "'$coverName'" : "NULL";
        mysqli_query($conn, "UPDATE books SET
            category_id = $category_id,
            judul       = '$judul',
            penulis     = '$penulis',
            tahun_terbit= $tahun_terbit,
            stok        = $stok,
            cover       = $coverVal
            WHERE id = $id");
        $_SESSION['msg']      = 'Buku berhasil diperbarui.';
        $_SESSION['msg_type'] = 'success';
        redirect('/perpustakaan/books/index.php');
    }
}

$kategoriList = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
include '../assets/layout/header.php';
?>

<div style="max-width:600px;">
    <div class="card">
        <div class="card-header">
            <h6>✏️ Edit Buku</h6>
            <a href="index.php" class="btn btn-sm btn-secondary">← Kembali</a>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Kategori <span style="color:red;">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($k = mysqli_fetch_assoc($kategoriList)): ?>
                        <option value="<?= $k['id'] ?>"
                            <?= (($_POST['category_id'] ?? $row['category_id']) == $k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['category_name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Judul Buku <span style="color:red;">*</span></label>
                    <input type="text" name="judul" class="form-control"
                           value="<?= htmlspecialchars($_POST['judul'] ?? $row['judul']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Penulis <span style="color:red;">*</span></label>
                    <input type="text" name="penulis" class="form-control"
                           value="<?= htmlspecialchars($_POST['penulis'] ?? $row['penulis']) ?>" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" class="form-control"
                               value="<?= $_POST['tahun_terbit'] ?? $row['tahun_terbit'] ?>"
                               min="1900" max="<?= date('Y') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" class="form-control"
                               value="<?= $_POST['stok'] ?? $row['stok'] ?>" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Cover Buku</label>
                    <?php if ($row['cover'] && file_exists("../assets/img/" . $row['cover'])): ?>
                    <div style="margin-bottom:8px;">
                        <img src="/perpustakaan/assets/img/<?= htmlspecialchars($row['cover']) ?>"
                             id="cover-preview"
                             style="max-height:100px; border-radius:6px; border:1px solid #e2e8f0;">
                    </div>
                    <?php else: ?>
                        <img id="cover-preview" src="" style="display:none; max-height:100px; border-radius:6px;">
                    <?php endif; ?>
                    <input type="file" name="cover" id="cover" class="form-control" accept="image/*">
                    <div style="font-size:11px; color:#94a3b8; margin-top:4px;">Kosongkan jika tidak ingin mengubah cover</div>
                </div>

                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary">💾 Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../assets/layout/footer.php'; ?>
