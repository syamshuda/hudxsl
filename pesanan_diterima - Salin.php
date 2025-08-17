<?php
// /pesanan_diterima.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php'; // Panggil fungsi yang baru dibuat

if (!isset($_SESSION['user_id']) || !isset($_POST['pesanan_id'])) {
    header("Location: /auth/login.php");
        exit();
        }

        $user_id = $_SESSION['user_id'];
        $pesanan_id = (int)$_POST['pesanan_id'];

        mysqli_begin_transaction($koneksi);

        try {
            // 1. Update status pesanan menjadi 'selesai'
                $stmt_update = $koneksi->prepare("UPDATE pesanan SET status_pesanan = 'selesai' WHERE id = ? AND pembeli_id = ? AND status_pesanan = 'dikirim'");
                    $stmt_update->bind_param("ii", $pesanan_id, $user_id);
                        if (!$stmt_update->execute() || $stmt_update->affected_rows == 0) {
                                throw new Exception("Pesanan tidak ditemukan atau statusnya bukan 'dikirim'.");
                                    }
                                        $stmt_update->close();

                                            // 2. Panggil fungsi terpusat untuk proses finansial
                                                selesaikanPesananDanTransferDana($pesanan_id, $koneksi);

                                                    // 3. Commit transaksi
                                                        mysqli_commit($koneksi);
                                                            header("Location: detail_pesanan.php?id=" . $pesanan_id);

                                                            } catch (Exception $e) {
                                                                mysqli_rollback($koneksi);
                                                                    error_log("Error in pesanan_diterima.php: " . $e->getMessage());
                                                                        header("Location: detail_pesanan.php?id=" . $pesanan_id . "&error=" . urlencode("Terjadi kesalahan sistem: " . $e->getMessage()));
                                                                        } finally {
                                                                            if (isset($koneksi) && $koneksi instanceof mysqli) {
                                                                                    $koneksi->close();
                                                                                        }
                                                                                            exit();
                                                                                            }
                                                                                            ?>