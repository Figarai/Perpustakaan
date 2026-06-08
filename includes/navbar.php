<nav class="navbar">
    <div class="navbar-brand">
        <span class="hamburger" onclick="toggleSidebar()">☰</span>
        📚 Perpustakaan
    </div>
    <div class="navbar-right">
        <span class="navbar-user">👤 <?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/', strlen($_SERVER['DOCUMENT_ROOT'])) - 2) ?>logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</nav>
