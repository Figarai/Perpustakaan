<?php
// =============================================
// users/index.php - CRUD User
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

session_start();
require_once '../config.php';
cekAdmin();

$success = '';
$error   = '';

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $id       = (int)($_POST['id'] ?? 0);
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    $role     = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));

    // Validasi No HP: hanya angka
    if ($phone && !ctype_digit($phone)) {
        $error = 'No HP hanya boleh berisi angka!';
    } elseif ($id > 0) {
        // UPDATE (password opsional)
        $pass_sql = $password ? ", password=MD5('$password')" : '';
        mysqli_query($conn, "UPDATE users SET name='$name', username='$username',
            role='$role', phone='$phone' $pass_sql WHERE id=$id");
        $success = 'User berhasil diperbarui!';
    } else {
        // INSERT
        if (!$password) { $error = 'Password wajib diisi untuk user baru.'; }
        else {
            // Cek username duplikat
            $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username='$username'"));
            if ($cek) {
                $error = 'Username sudah digunakan!';
            } else {
                mysqli_query($conn, "INSERT INTO users (name, username, password, role, phone)
                    VALUES ('$name', '$username', MD5('$password'), '$role', '$phone')");
                $success = 'User berhasil ditambahkan!';
            }
        }
    }
}

// DELETE
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        $success = 'User berhasil dihapus!';
    } else {
        $error = 'Tidak dapat menghapus akun sendiri!';
    }
}

// Data edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $r  = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($r);
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role, name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Perpustakaan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="main-wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header d-flex justify-between align-center">
                <div>
                    <h1>👤 Data User</h1>
                    <p>Kelola akun pengguna perpustakaan</p>
                </div>
                <a href="?form=1" class="btn btn-primary">+ Tambah User</a>
            </div>

            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <?php if (isset($_GET['form']) || $edit_data): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h3><?= $edit_data ? 'Edit User' : 'Tambah User Baru' ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="form-group">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= $edit_data ? htmlspecialchars($edit_data['name']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Username *</label>
                                <input type="text" name="username" class="form-control" required
                                       value="<?= $edit_data ? htmlspecialchars($edit_data['username']) : '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Password <?= $edit_data ? '(kosongkan jika tidak diubah)' : '*' ?></label>
                                <input type="password" name="password" class="form-control"
                                       <?= !$edit_data ? 'required' : '' ?>>
                            </div>
                            <div class="form-group">
                                <label>No HP (angka saja)</label>
                                <input type="text" name="phone" class="form-control" pattern="[0-9]*"
                                       value="<?= $edit_data ? htmlspecialchars($edit_data['phone']) : '' ?>"
                                       oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                            </div>
                            <div class="form-group">
                                <label>Role *</label>
                                <select name="role" class="form-control" required>
                                    <option value="user" <?= ($edit_data && $edit_data['role'] === 'user') ? 'selected' : '' ?>>
                                        User / Siswa</option>
                                    <option value="admin" <?= ($edit_data && $edit_data['role'] === 'admin') ? 'selected' : '' ?>>
                                        Admin / Petugas</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="simpan" class="btn btn-primary">
                            <?= $edit_data ? 'Simpan Perubahan' : 'Tambah User' ?>
                        </button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabel User -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>No HP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td>
                                        <span class="badge <?= $row['role'] === 'admin' ? 'badge-info' : 'badge-success' ?>">
                                            <?= ucfirst($row['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= $row['phone'] ?: '-' ?></td>
                                    <td>
                                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="#" onclick="konfirmasiHapus('?hapus=<?= $row['id'] ?>','<?= htmlspecialchars($row['name']) ?>')"
                                           class="btn btn-sm btn-danger">Hapus</a>
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
</body>
</html>
