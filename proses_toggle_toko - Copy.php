<?php
// /admin/proses_toggle_toko.php (Revisi untuk Manajemen Status Toko)

require_once '../config/database.php';

try {
    // Proteksi: Pastikan user adalah admin dan request adalah POST
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: /auth/login.php");
                        exit();
                            }

                                $toko_id = isset($_POST['toko_id']) ? (int)$_POST['toko_id'] : 0;
                                    $action = isset($_POST['action']) ? $_POST['action'] : '';

                                        if ($toko_id <= 0 || !in_array($action, ['activate', 'deactivate'])) {
                                                throw new Exception("Aksi atau ID Toko tidak valid.");
                                                    }
                                                        
                                                            $new_status = ($action === 'activate') ? 1 : 0;

                                                                // Menargetkan kolom 'is_active' di tabel 'toko'
                                                                    $stmt = $koneksi->prepare("UPDATE toko SET is_active = ? WHERE id = ?");
                                                                        
                                                                            if ($stmt === false) {
                                                                                    throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
                                                                                        }

                                                                                            $stmt->bind_param("ii", $new_status, $toko_id);

                                                                                                if (!$stmt->execute()) {
                                                                                                        throw new Exception("Gagal mengeksekusi perubahan status: " . $stmt->error);
                                                                                                            }
                                                                                                                
                                                                                                                    $stmt->close();
                                                                                                                        $koneksi->close();

                                                                                                                            header("Location: kelola_toko.php?status=sukses");
                                                                                                                                exit();

                                                                                                                                } catch (Exception $e) {
                                                                                                                                    error_log("Error in proses_toggle_toko.php: " . $e->getMessage());
                                                                                                                                        header("Location: kelola_toko.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                            exit();
                                                                                                                                            }
                                                                                                                                            ?>