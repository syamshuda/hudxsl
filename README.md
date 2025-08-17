# SmeksabaShop - E-Commerce Multi-Role Sederhana

Proyek website e-commerce yang dibuat menggunakan PHP Native dan MySQL, dirancang agar ringan dan dapat berjalan di hosting gratis seperti InfinityFree.

---

## 🔧 Fitur Utama

- **Tiga Level Pengguna**: Admin, Penjual, dan Pembeli.
- **Dashboard Khusus**: Setiap peran memiliki dashboard dan hak akses masing-masing.
- **Manajemen Produk**: Penjual dapat melakukan operasi CRUD pada produk mereka.
- **Proses Pesanan**: Pembeli dapat memesan produk dan mengunggah bukti pembayaran.
- **Admin Kontrol**: Admin dapat mengelola pengguna, produk, dan pesanan.
- **Responsif**: Dibuat dengan Bootstrap 5.

---

## ⚙️ Cara Instalasi di InfinityFree

1.  **Download File**: Unduh semua file dari proyek ini.
2.  **Buat Database**:
    -   Masuk ke cPanel InfinityFree Anda.
    -   Buka menu "MySQL Databases" dan buat database baru.
    -   Catat **Host Name**, **Database Name**, **Database User**, dan **Password**.
3.  **Import Database**:
    -   Buka "phpMyAdmin" dari cPanel.
    -   Pilih database yang baru Anda buat.
    -   Klik tab "Import", pilih file `smeksabashop.sql`, dan klik "Go".
4.  **Konfigurasi Koneksi**:
    -   Buka file `config/database.php`.
    -   Ubah nilai variabel `$host`, `$user`, `$pass`, dan `$db` sesuai dengan detail database yang Anda catat pada langkah 2.
5.  **Upload File**:
    -   Gunakan "File Manager" di cPanel atau FTP client seperti FileZilla.
    -   Upload semua file dan folder ke dalam direktori `/htdocs/`. Pastikan struktur folder tetap sama.
6.  **Selesai!**: Buka nama domain Anda untuk melihat website.

---

## 🔑 Akun Dummy untuk Testing

Gunakan akun berikut untuk login dan mencoba fitur dari setiap peran.

-   **Password untuk semua akun**: `password123`

-   **Admin**:
    -   **Username**: `admin`

-   **Penjual**:
    -   **Username**: `tokobudi`

-   **Pembeli**:
    -   **Username**: `anita_pembeli`

---
## 🛑 Batasan

-   Sistem pembayaran masih manual (upload bukti transfer).
-   Fitur chat dan notifikasi real-time belum diimplementasikan.
-   Fokus pada fungsionalitas inti, bukan desain yang kompleks.