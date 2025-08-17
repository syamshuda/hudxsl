<?php
// /admin/proses_toggle_toko.php (Versi Baru dengan Toggle Kurir Lokal)

require_once '../config/database.php';

try {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /auth/login.php");
                    exit();
                        }

                            $toko_id = isset($_POST['toko_id']) ? (int)$_POST['toko_id'] : 0;
                                $action = isset($_POST['action']) ? $_POST['action'] : '';

                                    if ($toko_id <= 0) {
                                            throw new Exception("ID Toko tidak valid.");
                                                }
                                                    
                                                        $query = "";
                                                            $pesan_sukses = "";

                                                                switch ($action) {
                                                                        case 'activate': // Ini untuk mengaktifkan toko (yang lama)
                                                                                    $query = "UPDATE toko SET is_active = 1 WHERE id = ?";
                                                                                                $pesan_sukses = "Status toko berhasil diaktifkan.";
                                                                                                            break;
                                                                                                                    case 'deactivate': // Ini untuk menonaktifkan toko (yang lama)
                                                                                                                                $query = "UPDATE toko SET is_active = 0 WHERE id = ?";
                                                                                                                                            $pesan_sukses = "Status toko berhasil dinonaktifkan.";
                                                                                                                                                        break;
                                                                                                                                                                case 'activate_local': // LOGIKA BARU
                                                                                                                                                                            $query = "UPDATE toko SET is_smkn1 = 1 WHERE id = ?";
                                                                                                                                                                                        $pesan_sukses = "Kurir Lokal berhasil diaktifkan untuk toko ini.";
                                                                                                                                                                                                    break;
                                                                                                                                                                                                            case 'deactivate_local': // LOGIKA BARU
                                                                                                                                                                                                                        $query = "UPDATE toko SET is_smkn1 = 0 WHERE id = ?";
                                                                                                                                                                                                                                    $pesan_sukses = "Kurir Lokal berhasil dinonaktifkan untuk toko ini.";
                                                                                                                                                                                                                                                break;
                                                                                                                                                                                                                                                        default:
                                                                                                                                                                                                                                                                    throw new Exception("Aksi tidak valid.");
                                                                                                                                                                                                                                                                        }

                                                                                                                                                                                                                                                                            $stmt = $koneksi->prepare($query);
                                                                                                                                                                                                                                                                                if ($stmt === false) {
                                                                                                                                                                                                                                                                                        throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                $stmt->bind_param("i", $toko_id);

                                                                                                                                                                                                                                                                                                    if (!$stmt->execute()) {
                                                                                                                                                                                                                                                                                                            throw new Exception("Gagal mengeksekusi perubahan status: " . $stmt->error);
                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                    
                                                                                                                                                                                                                                                                                                                        $stmt->close();
                                                                                                                                                                                                                                                                                                                            $koneksi->close();

                                                                                                                                                                                                                                                                                                                                header("Location: kelola_toko.php?status=sukses&pesan=" . urlencode($pesan_sukses));
                                                                                                                                                                                                                                                                                                                                    exit();

                                                                                                                                                                                                                                                                                                                                    } catch (Exception $e) {
                                                                                                                                                                                                                                                                                                                                        error_log("Error in proses_toggle_toko.php: " . $e->getMessage());
                                                                                                                                                                                                                                                                                                                                            header("Location: kelola_toko.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                                                                                                                                                                                                                exit();
                                                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                                                ?>