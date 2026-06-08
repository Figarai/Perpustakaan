#!/bin/bash
# =============================================
# install.sh - Installer Aplikasi Perpustakaan
# Source: https://github.com/Figarai/Perpustakaan
# Cara pakai: paste perintah ini di terminal VSCode
# =============================================

BASE_URL="https://raw.githubusercontent.com/Figarai/Perpustakaan/main"
TARGET="C:/xampp/htdocs/perpustakaan"

echo "========================================"
echo "  Installer Aplikasi Perpustakaan UKK"
echo "========================================"

# Buat folder
echo "[1/3] Membuat struktur folder..."
mkdir -p "$TARGET/assets/css"
mkdir -p "$TARGET/assets/js"
mkdir -p "$TARGET/assets/covers"
mkdir -p "$TARGET/includes"
mkdir -p "$TARGET/books"
mkdir -p "$TARGET/categories"
mkdir -p "$TARGET/users"
mkdir -p "$TARGET/peminjaman"
mkdir -p "$TARGET/report"

# Download semua file
echo "[2/3] Mendownload file dari GitHub..."

FILES=(
    "config.php"
    "login.php"
    "logout.php"
    "dashboard.php"
    "database.sql"
    "README.md"
    "assets/css/style.css"
    "assets/js/main.js"
    "includes/navbar.php"
    "includes/sidebar.php"
    "books/index.php"
    "categories/index.php"
    "users/index.php"
    "peminjaman/index.php"
    "report/index.php"
)

for FILE in "${FILES[@]}"; do
    echo "  Downloading: $FILE"
    curl -fsSL "$BASE_URL/$FILE" -o "$TARGET/$FILE"
done

echo "[3/3] Selesai!"
echo ""
echo "========================================"
echo "  LANGKAH SELANJUTNYA:"
echo "  1. Buka phpMyAdmin: http://localhost/phpmyadmin"
echo "  2. Import file: $TARGET/database.sql"
echo "  3. Buka browser: http://localhost/perpustakaan/login.php"
echo ""
echo "  Login Admin  : admin / admin123"
echo "  Login Siswa  : siswa1 / siswa123"
echo "========================================"
