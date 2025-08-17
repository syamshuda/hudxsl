<?php
// /penjual/hapus_produk.php
require_once '../config/database.php';

// Proteksi: Pastikan user adalah penjual dan ID produk ada
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || !isset($_GET['id'])) {
    header("Location: /auth/login.php");
        exit();
        }

        $produk_id = (int)$_GET['id'];
        $user_id = $_SESSION['user_id'];

        // Mulai transaksi untuk memastikan konsistensi data
        mysqli_begin_transaction($koneksi);

        try {
            // 1. Ambil nama file gambar dan verifikasi kepemilikan produk dalam satu query
                $stmt_select = $koneksi->prepare(
                        "SELECT p.gambar_produk 
                                 FROM produk p 
                                          JOIN toko t ON p.toko_id = t.id 
                                                   WHERE p.id = ? AND t.user_id = ?"
                                                       );
                                                           $stmt_select->bind_param("ii", $produk_id, $user_id);
                                                               $stmt_select->execute();
                                                                   $result = $stmt_select->get_result();

                                                                       if ($result->num_rows !== 1) {
                                                                               // Jika produk tidak ditemukan atau bukan milik penjual ini
                                                                                       throw new Exception("Produk tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.");
                                                                                           }
                                                                                               
                                                                                                   $produk = $result->fetch_assoc();
                                                                                                       $gambar_produk = $produk['gambar_produk'];
                                                                                                           $stmt_select->close();

                                                                                                               // 2. Hapus produk dari database
                                                                                                                   $stmt_delete = $koneksi->prepare("DELETE FROM produk WHERE id = ?");
                                                                                                                       $stmt_delete->bind_param("i", $produk_id);
                                                                                                                           
                                                                                                                               if (!$stmt_delete->execute()) {
                                                                                                                                       throw new Exception("Gagal menghapus produk dari database.");
                                                                                                                                           }
                                                                                                                                               $stmt_delete->close();

                                                                                                                                                   // 3. Hapus file gambar dari server jika penghapusan dari database berhasil
                                                                                                                                                       if (!empty($gambar_produk)) {
                                                                                                                                                               $file_path = "../uploads/produk/" . $gambar_produk;
                                                                                                                                                                       if (file_exists($file_path)) {
                                                                                                                                                                                   unlink($file_path); // Hapus file gambar
                                                                                                                                                                                           }
                                                                                                                                                                                               }

                                                                                                                                                                                                   // 4. Jika semua langkah berhasil, commit transaksi
                                                                                                                                                                                                       mysqli_commit($koneksi);

                                                                                                                                                                                                           // Redirect dengan pesan sukses
                                                                                                                                                                                                               header("Location: produk_saya.php?status=sukses&pesan=Produk berhasil dihapus.");
                                                                                                                                                                                                                   exit();

                                                                                                                                                                                                                   } catch (Exception $e) {
                                                                                                                                                                                                                       // Jika terjadi error, batalkan semua perubahan
                                                                                                                                                                                                                           mysqli_rollback($koneksi);

                                                                                                                                                                                                                               // Redirect dengan pesan error
                                                                                                                                                                                                                                   error_log("Gagal hapus produk: " . $e->getMessage()); // Catat error untuk developer
                                                                                                                                                                                                                                       header("Location: produk_saya.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                                                                                                           exit();
                                                                                                                                                                                                                                               
                                                                                                                                                                                                                                               } finally {
                                                                                                                                                                                                                                                   if (isset($koneksi)) {
                                                                                                                                                                                                                                                           $koneksi->close();
                                                                                                                                                                                                                                                               }
                                                                                                                                                                                                                                                               }
                                                                                                                                                                                                                                                               ?>