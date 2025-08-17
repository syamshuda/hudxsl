<?php
// /admin/proses_toggle_toko.php

require_once '../config/database.php';

try {
    // Proteksi: Pastikan user adalah admin dan request adalah POST
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: /auth/login.php");
                        exit();
                            }

                                // Ambil dan validasi data yang dikirim dari form
                                    $toko_id = isset($_POST['toko_id']) ? (int)$_POST['toko_id'] : 0;
                                        $action = isset($_POST['action']) ? $_POST['action'] : '';

                                            if ($toko_id <= 0 || !in_array($action, ['activate', 'deactivate'])) {
                                                    throw new Exception("Aksi atau ID Toko tidak valid.");
                                                        }
                                                            
                                                                // Tentukan nilai status baru berdasarkan aksi yang dipilih
                                                                    // 1 untuk 'aktif', 0 untuk 'non-aktif'
                                                                        $new_status = ($action === 'activate') ? 1 : 0;

                                                                            // Siapkan query UPDATE menggunakan prepared statement untuk keamanan
                                                                                $stmt = $koneksi->prepare("UPDATE toko SET is_active = ? WHERE id = ?");
                                                                                    
                                                                                        if ($stmt === false) {
                                                                                                throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
                                                                                                    }

                                                                                                        $stmt->bind_param("ii", $new_status, $toko_id);

                                                                                                            // Eksekusi query
                                                                                                                if (!$stmt->execute()) {
                                                                                                                        throw new Exception("Gagal mengeksekusi perubahan status: " . $stmt->error);
                                                                                                                            }
                                                                                                                                
                                                                                                                                    $stmt->close();
                                                                                                                                        $koneksi->close();

                                                                                                                                            // Jika berhasil, arahkan kembali ke halaman kelola toko dengan notifikasi sukses
                                                                                                                                                header("Location: kelola_toko.php?status=sukses");
                                                                                                                                                    exit();

                                                                                                                                                    } catch (Exception $e) {
                                                                                                                                                        // Jika terjadi error, catat error dan arahkan kembali dengan notifikasi gagal
                                                                                                                                                            error_log("Error in proses_toggle_toko.php: " . $e->getMessage());
                                                                                                                                                                header("Location: kelola_toko.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                                    exit();
                                                                                                                                                                    }
                                                                                                                                                                    ?>