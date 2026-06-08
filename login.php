<?php
// =============================================
// login.php - Halaman Login
// Bootcamp UKK RPL - Aplikasi Peminjaman Buku
// =============================================

session_start();
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';
$login_attempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek jika percobaan login sudah 3x
    if ($login_attempts >= 3) {
        $error = 'Akun dikunci! Terlalu banyak percobaan login yang salah.';
    } else {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $password = MD5(trim($_POST['password']));

        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['name']     = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['login_attempts'] = 0;
            redirect('dashboard.php');
        } else {
            $_SESSION['login_attempts'] = $login_attempts + 1;
            $remaining = 3 - $_SESSION['login_attempts'];
            if ($remaining > 0) {
                $error = "Username atau password salah! Sisa percobaan: $remaining";
            } else {
                $error = 'Akun dikunci! Terlalu banyak percobaan login yang salah.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-logo">📚</div>
            <h2>Aplikasi Perpustakaan</h2>
            <p class="login-subtitle">Silakan login untuk melanjutkan</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                           placeholder="Masukkan username" required
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Masukkan password" required>
                        <span class="toggle-password" onclick="togglePassword()">👁</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
