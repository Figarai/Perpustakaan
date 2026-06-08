<?php
session_start();
require_once '../config.php';
cekAdmin();

$pageTitle  = 'Tambah Buku';
$activeMenu = 'books';
$basePath   = '/perpustakaan';

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

    // Upload cover
    $coverName = null;
    if (!empty($_FILES['cover']['name'])) {
        $fileExt  = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowed)) {
            $errors[] = 'Format gambar tidak valid (jpg, jpeg, png, gif, webp).';
        } elseif ($_FILES['cover']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran gambar maksimal 2MB.';
        } else {
            $coverName = uniqid('cover_') . '.' . $fileExt;
            $uploadDir = '../assets/img/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (!move_uploaded_file($_FILES['cover']['tmp_name'], $uploadDir . $coverName)) {
                $errors[] = 'Gagal mengupload gambar.';
                $coverName = null;
            }
        }
    }

    if (empty($errors)) {
        $coverVal = $coverName ? "'$coverName'" : "NULL";
        mysqli_query($conn, "INSERT INTO books (category_id, judul, penulis, tahun_terbit, stok, cover)
                             VALUES ($category_id, '$judul', '$penulis', $tahun_terbit, $stok, $coverVal)");
        $_SESSION['msg']      = 'Buku berhasil ditambahkan.';
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
            <h6>➕ Tambah Buku</h6>
            <a href="index.php" class="btn btn-sm btn-secondary">← Kembali</a>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div>• <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Kategori <span style="color:red;">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($k = mysqli_fetch_assoc($kategoriList)): ?>
                        <option value="<?= $k['id'] ?>"
                            <?= ($_POST['category_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['category_name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Judul Buku <span style="color:red;">*</span></label>
                    <input type="text" name="judul" class="form-control"
                           value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>"
                           placeholder="Masukkan judul buku" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Penulis <span style="color:red;">*</span></label>
                    <input type="text" name="penulis" class="form-control"
                           value="<?= htmlspecialchars($_POST['penulis'] ?? '') ?>"
                           placeholder="Nama penulis" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" class="form-control"
                               value="<?= $_POST['tahun_terbit'] ?? date('Y') ?>"
                               min="1900" max="<?= date('Y') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" class="form-control"
                               value="<?= $_POST['stok'] ?? 0 ?>" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Cover Buku</label>
                    <input type="file" name="cover" id="cover" class="form-control"
                           accept="image/*">
                    <div style="font-size:11px; color:#94a3b8; margin-top:4px;">
                        Format: JPG, PNG, GIF, WEBP. Maks: 2MB
                    </div>
                    <div style="margin-top:8px;">
                        <img id="cover-preview" src="" alt="Preview"
                             style="display:none; max-height:120px; border-radius:6px; border:1px solid #e2e8f0;">
                    </div>
                </div>

                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../assets/layout/footer.php'; ?>
