<?php
session_start();
require_once '../config.php';
cekAdmin();

$pageTitle  = 'Tambah User';
$activeMenu = 'users';
$basePath   = '/perpustakaan';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = bersihkan($conn, $_POST['name'] ?? '');
    $username = bersihkan($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
    $phone    = bersihkan($conn, $_POST['phone'] ?? '');

    if (empty($name))     $errors[] = 'Nama wajib diisi.';
    if (empty($username)) $errors[] = 'Username wajib diisi.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if (!empty($phone) && !preg_match('/^[0-9]+$/', $phone)) $errors[] = 'No HP hanya boleh angka.';

    // Cek username duplikat
    if (!$errors) {
        $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' LIMIT 1"));
        if ($cek) $errors[] = 'Username sudah digunakan.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (name, username, password, role, phone)
                             VALUES ('$name', '$username', '$hash', '$role', '$phone')");
        $_SESSION['msg']      = 'User berhasil ditambahkan.';
        $_SESSION['msg_type'] = 'success';
        redirect('/perpustakaan/users/index.php');
    }
}

include '../assets/layout/header.php';
?>

<div style="max-width:500px;">
    <div class="card">
        <div class="card-header">
            <h6>➕ Tambah User</h6>
            <a href="index.php" class="btn btn-sm btn-secondary">← Kembali</a>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:red;">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Nama lengkap" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Username <span style="color:red;">*</span></label>
                    <input type="text" name="username" class="form-control"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Username untuk login" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span style="color:red;">*</span></label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Minimal 6 karakter" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="user" <?= ($_POST['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User / Siswa</option>
                        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin / Petugas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">No HP</label>
                    <input type="text" name="phone" class="form-control phone-only"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           placeholder="Contoh: 08123456789">
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
