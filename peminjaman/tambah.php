<?php
session_start();
require_once '../config.php';
cekLogin();

$pageTitle  = 'Pinjam Buku';
$activeMenu = 'peminjaman';
$basePath   = '/perpustakaan';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id       = $_SESSION['role'] === 'admin'
                     ? (int)($_POST['user_id'] ?? 0)
                     : (int)$_SESSION['user_id'];
    $book_id       = (int)($_POST['book_id'] ?? 0);
    $tanggal_pinjam= bersihkan($conn, $_POST['tanggal_pinjam'] ?? date('Y-m-d'));
    $qty           = (int)($_POST['qty'] ?? 1);

    if (!$user_id)  $errors[] = 'Peminjam wajib dipilih.';
    if (!$book_id)  $errors[] = 'Buku wajib dipilih.';
    if ($qty < 1)   $errors[] = 'Jumlah minimal 1.';

    // Validasi stok
    if ($book_id) {
        $buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id = $book_id LIMIT 1"));
        if (!$buku) {
            $errors[] = 'Buku tidak ditemukan.';
        } elseif ($qty > $buku['stok']) {
            $errors[] = "Stok tidak mencukupi. Stok tersedia: {$buku['stok']}.";
        } elseif ($buku['stok'] == 0) {
            $errors[] = 'Buku sudah habis dipinjam.';
        }
    }

    if (empty($errors)) {
        mysqli_query($conn, "INSERT INTO peminjaman (user_id, book_id, tanggal_pinjam, qty, status)
                             VALUES ($user_id, $book_id, '$tanggal_pinjam', $qty, 'dipinjam')");
        // Kurangi stok
        mysqli_query($conn, "UPDATE books SET stok = stok - $qty WHERE id = $book_id");
        $_SESSION['msg']      = 'Peminjaman berhasil dicatat.';
        $_SESSION['msg_type'] = 'success';
        redirect('/perpustakaan/peminjaman/index.php');
    }
}

// Data untuk form
$bukuList = mysqli_query($conn, "SELECT * FROM books WHERE stok > 0 ORDER BY judul");
$userList = $_SESSION['role'] === 'admin'
            ? mysqli_query($conn, "SELECT * FROM users WHERE role = 'user' ORDER BY name")
            : null;

include '../assets/layout/header.php';
?>

<div style="max-width:550px;">
    <div class="card">
        <div class="card-header">
            <h6>➕ Pinjam Buku</h6>
            <a href="index.php" class="btn btn-sm btn-secondary">← Kembali</a>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($_SESSION['role'] === 'admin' && $userList): ?>
                <div class="form-group">
                    <label class="form-label">Peminjam <span style="color:red;">*</span></label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Pilih User --</option>
                        <?php while ($u = mysqli_fetch_assoc($userList)): ?>
                        <option value="<?= $u['id'] ?>" <?= ($_POST['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['name']) ?> (<?= $u['username'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    📚 Peminjam: <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Buku <span style="color:red;">*</span></label>
                    <select name="book_id" id="book_id" class="form-control" required>
                        <option value="">-- Pilih Buku --</option>
                        <?php while ($b = mysqli_fetch_assoc($bukuList)): ?>
                        <option value="<?= $b['id'] ?>"
                                data-stok="<?= $b['stok'] ?>"
                                <?= ($_POST['book_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['judul']) ?> (Stok: <?= $b['stok'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div id="stok-info" style="font-size:12px; margin-top:4px;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Pinjam <span style="color:red;">*</span></label>
                    <input type="date" name="tanggal_pinjam" class="form-control"
                           value="<?= $_POST['tanggal_pinjam'] ?? date('Y-m-d') ?>"
                           max="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Jumlah (Qty) <span style="color:red;">*</span></label>
                    <input type="number" name="qty" id="qty" class="form-control"
                           value="<?= $_POST['qty'] ?? 1 ?>" min="1" required>
                </div>

                <div class="alert alert-warning" style="font-size:12px;">
                    ⚠️ Pastikan jumlah tidak melebihi stok yang tersedia!
                </div>

                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary">💾 Simpan Peminjaman</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../assets/layout/footer.php'; ?>
