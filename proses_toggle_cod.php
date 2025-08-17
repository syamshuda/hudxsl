<?php
// /admin/proses_toggle_cod.php (Versi Penuh dan Lengkap)

require_once '../config/database.php';

// Pastikan hanya admin yang login yang dapat mengakses file ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        try {
            // Validasi input yang diterima dari form
                if (!isset($_POST['user_id']) || !isset($_POST['current_status'])) {
                        throw new Exception("Data yang dikirim tidak lengkap.");
                            }

                                $user_id_to_toggle = (int)$_POST['user_id'];
                                    $current_status = (int)$_POST['current_status'];
                                        
                                            // Toggle status (jika 1 menjadi 0, jika 0 menjadi 1)
                                                $new_status = ($current_status === 1) ? 0 : 1;

                                                    // Keamanan: Pastikan admin tidak bisa mengubah status COD untuk akunnya sendiri
                                                        if ($user_id_to_toggle == $_SESSION['user_id']) {
                                                                throw new Exception("Anda tidak dapat mengubah status COD untuk akun Anda sendiri.");
                                                                    }

                                                                        // Siapkan query UPDATE menggunakan prepared statement untuk mencegah SQL Injection
                                                                            $stmt = $koneksi->prepare("UPDATE users SET can_use_cod = ? WHERE id = ?");
                                                                                if ($stmt === false) {
                                                                                        throw new Exception("Gagal menyiapkan query database: " . $koneksi->error);
                                                                                            }

                                                                                                // Bind parameter dan eksekusi query
                                                                                                    $stmt->bind_param("ii", $new_status, $user_id_to_toggle);
                                                                                                        if (!$stmt->execute()) {
                                                                                                                throw new Exception("Gagal mengeksekusi perubahan status COD: " . $stmt->error);
                                                                                                                    }
                                                                                                                        
                                                                                                                            // Tutup statement dan koneksi
                                                                                                                                $stmt->close();
                                                                                                                                    $koneksi->close();

                                                                                                                                        // Arahkan kembali ke halaman kelola pengguna dengan pesan sukses
                                                                                                                                            header("Location: kelola_pengguna.php?status=sukses&pesan=Status COD pengguna berhasil diperbarui.");
                                                                                                                                                exit();

                                                                                                                                                } catch (Exception $e) {
                                                                                                                                                    // Jika terjadi error, catat error dan arahkan kembali dengan pesan yang jelas
                                                                                                                                                        error_log("Error in proses_toggle_cod.php: " . $e->getMessage());
                                                                                                                                                            header("Location: kelola_pengguna.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                                exit();
                                                                                                                                                                }
                                                                                                                                                                ?>