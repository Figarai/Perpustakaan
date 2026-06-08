<?php
// Tentukan base URL (support di root maupun subfolder)
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
$base  = str_repeat('../', max(0, $depth));
$current = basename($_SERVER['PHP_SELF']);
$folder  = basename(dirname($_SERVER['PHP_SELF']));

function isActive($check, $current, $folder) {
    if (is_array($check)) {
        return in_array($folder, $check) ? 'active' : '';
    }
    return ($folder === $check || $current === $check) ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?= $base ?>dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">
                🏠 Dashboard
            </a>
        </li>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li>
            <a href="<?= $base ?>categories/index.php" class="<?= isActive('categories', $current, $folder) ?>">
                🏷️ Kategori
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="<?= $base ?>books/index.php" class="<?= isActive('books', $current, $folder) ?>">
                📚 Buku
            </a>
        </li>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li>
            <a href="<?= $base ?>users/index.php" class="<?= isActive('users', $current, $folder) ?>">
                👤 Data User
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="<?= $base ?>peminjaman/index.php" class="<?= isActive('peminjaman', $current, $folder) ?>">
                📖 Peminjaman
            </a>
        </li>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li>
            <a href="<?= $base ?>report/index.php" class="<?= isActive('report', $current, $folder) ?>">
                📊 Report
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>
