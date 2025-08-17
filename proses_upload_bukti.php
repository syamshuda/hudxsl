<?php
// /proses_upload_bukti.php
require_once 'config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        $pesanan_id = (int)$_POST['pesanan_id'];
        $jumlah_bayar = (float)$_POST['jumlah_bayar'];
        $user_id = $_SESSION['user_id'];

        // Mulai transaksi
        mysqli_begin_transaction($koneksi);

        try {
            // Verifikasi kepemilikan pesanan
                $stmt_verify = $koneksi->prepare("SELECT id FROM pesanan WHERE id = ? AND pembeli_id = ? AND status_pesanan = 'menunggu_pembayaran'");
                    $stmt_verify->bind_param("ii", $pesanan_id, $user_id);
                        $stmt_verify->execute();
                            if ($stmt_verify->get_result()->num_rows !== 1) {
                                    throw new Exception("Pesanan tidak ditemukan, bukan milik Anda, atau statusnya tidak 'menunggu_pembayaran'.");
                                        }
                                            $stmt_verify->close();

                                                // Proses upload gambar
                                                    $bukti_pembayaran = $_FILES['bukti_pembayaran'];
                                                        $nama_gambar = "";
                                                            if (isset($bukti_pembayaran) && $bukti_pembayaran['error'] === UPLOAD_ERR_OK) {
                                                                    $target_dir = "uploads/bukti_pembayaran/";
                                                                            if (!is_dir($target_dir)) {
                                                                                        mkdir($target_dir, 0755, true);
                                                                                                }
                                                                                                        $nama_gambar = uniqid() . '-' . basename($bukti_pembayaran["name"]);
                                                                                                                $target_file = $target_dir . $nama_gambar;
                                                                                                                        
                                                                                                                                if (!move_uploaded_file($bukti_pembayaran["tmp_name"], $target_file)) {
                                                                                                                                            throw new Exception("Gagal mengunggah gambar bukti pembayaran.");
                                                                                                                                                    }
                                                                                                                                                        } else {
                                                                                                                                                                throw new Exception("Tidak ada gambar bukti pembayaran diunggah atau terjadi error.");
                                                                                                                                                                    }

                                                                                                                                                                        // Masukkan ke tabel pembayaran
                                                                                                                                                                            $stmt_bayar = $koneksi->prepare("INSERT INTO pembayaran (pesanan_id, jumlah_bayar, metode, bukti_pembayaran, status_konfirmasi) VALUES (?, ?, 'Transfer Bank', ?, 'menunggu')");
                                                                                                                                                                                $stmt_bayar->bind_param("ids", $pesanan_id, $jumlah_bayar, $nama_gambar);
                                                                                                                                                                                    if (!$stmt_bayar->execute()) {
                                                                                                                                                                                            throw new Exception("Gagal menyimpan data pembayaran: " . $stmt_bayar->error);
                                                                                                                                                                                                }
                                                                                                                                                                                                    $stmt_bayar->close();

                                                                                                                                                                                                        // ---- BAGIAN TAMBAHAN / PERUBAHAN UTAMA DI SINI ----
                                                                                                                                                                                                            // Setelah bukti pembayaran diunggah dan data pembayaran disimpan,
                                                                                                                                                                                                                // perbarui status pesanan di tabel 'pesanan' menjadi 'pending_konfirmasi' atau 'menunggu_konfirmasi'
                                                                                                                                                                                                                    // Nama status ini bisa disesuaikan, misal: 'pending_konfirmasi', 'menunggu_verifikasi', dll.
                                                                                                                                                                                                                        // Saya sarankan 'pending' atau 'menunggu_konfirmasi' agar konsisten dengan 'menunggu' di tabel pembayaran
                                                                                                                                                                                                                            
                                                                                                                                                                                                                                $new_pesanan_status = 'pending'; // Atau 'menunggu_konfirmasi'
                                                                                                                                                                                                                                    $stmt_update_pesanan = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id = ?");
                                                                                                                                                                                                                                        $stmt_update_pesanan->bind_param("si", $new_pesanan_status, $pesanan_id);
                                                                                                                                                                                                                                            if (!$stmt_update_pesanan->execute()) {
                                                                                                                                                                                                                                                    throw new Exception("Gagal memperbarui status pesanan: " . $stmt_update_pesanan->error);
                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                            $stmt_update_pesanan->close();

                                                                                                                                                                                                                                                                // Commit transaksi jika semua berhasil
                                                                                                                                                                                                                                                                    mysqli_commit($koneksi);
                                                                                                                                                                                                                                                                        header("Location: pesanan_saya.php?status=konfirmasisukses");
                                                                                                                                                                                                                                                                            exit();

                                                                                                                                                                                                                                                                            } catch (Exception $e) {
                                                                                                                                                                                                                                                                                // Rollback transaksi jika ada error
                                                                                                                                                                                                                                                                                    mysqli_rollback($koneksi);
                                                                                                                                                                                                                                                                                        // Hapus gambar yang mungkin sudah terunggah jika ada error setelahnya
                                                                                                                                                                                                                                                                                            if (!empty($nama_gambar) && file_exists("uploads/bukti_pembayaran/" . $nama_gambar)) {
                                                                                                                                                                                                                                                                                                    unlink("uploads/bukti_pembayaran/" . $nama_gambar);
                                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                                            // Redirect dengan pesan error lebih spesifik
                                                                                                                                                                                                                                                                                                                header("Location: detail_pesanan.php?id=$pesanan_id&error=" . urlencode($e->getMessage()));
                                                                                                                                                                                                                                                                                                                    exit();
                                                                                                                                                                                                                                                                                                                    } finally {
                                                                                                                                                                                                                                                                                                                        // Pastikan koneksi ditutup
                                                                                                                                                                                                                                                                                                                            if (isset($koneksi) && $koneksi instanceof mysqli) {
                                                                                                                                                                                                                                                                                                                                    $koneksi->close();
                                                                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                                                                        ?>