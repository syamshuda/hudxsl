<?php
// /admin/proses_verifikasi.php

require_once '../config/database.php';

// Proteksi: Pastikan user adalah admin dan request adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        mysqli_begin_transaction($koneksi);

        try {
            // Ambil data yang dikirim dari form
                $toko_id = isset($_POST['toko_id']) ? (int)$_POST['toko_id'] : 0;
                    $action = isset($_POST['action']) ? $_POST['action'] : '';

                        if ($toko_id <= 0 || !in_array($action, ['setujui', 'tolak'])) {
                                throw new Exception("Aksi atau ID Toko tidak valid.");
                                    }

                                        // Ambil user_id yang terkait dengan toko ini untuk mengaktifkan akunnya nanti
                                            $stmt_get_user = $koneksi->prepare("SELECT user_id FROM toko WHERE id = ?");
                                                $stmt_get_user->bind_param("i", $toko_id);
                                                    $stmt_get_user->execute();
                                                        $result_user = $stmt_get_user->get_result();
                                                            if ($result_user->num_rows === 0) {
                                                                    throw new Exception("Toko tidak ditemukan.");
                                                                        }
                                                                            $user_id_penjual = $result_user->fetch_assoc()['user_id'];
                                                                                $stmt_get_user->close();

                                                                                    // Tentukan status baru berdasarkan aksi
                                                                                        $status_verifikasi_baru = ($action === 'setujui') ? 'disetujui' : 'ditolak';

                                                                                            // 1. Update status verifikasi di tabel 'toko'
                                                                                                $stmt_update_toko = $koneksi->prepare("UPDATE toko SET status_verifikasi = ? WHERE id = ?");
                                                                                                    $stmt_update_toko->bind_param("si", $status_verifikasi_baru, $toko_id);
                                                                                                        if (!$stmt_update_toko->execute()) {
                                                                                                                throw new Exception("Gagal memperbarui status verifikasi toko.");
                                                                                                                    }
                                                                                                                        $stmt_update_toko->close();

                                                                                                                            // 2. Jika disetujui, aktifkan akun penjual di tabel 'users'
                                                                                                                                if ($action === 'setujui') {
                                                                                                                                        $stmt_activate_user = $koneksi->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                                                                                                                                                $stmt_activate_user->bind_param("i", $user_id_penjual);
                                                                                                                                                        if (!$stmt_activate_user->execute()) {
                                                                                                                                                                    throw new Exception("Gagal mengaktifkan akun pengguna.");
                                                                                                                                                                            }
                                                                                                                                                                                    $stmt_activate_user->close();
                                                                                                                                                                                        }
                                                                                                                                                                                            
                                                                                                                                                                                                // Jika semua berhasil, commit transaksi
                                                                                                                                                                                                    mysqli_commit($koneksi);
                                                                                                                                                                                                        
                                                                                                                                                                                                            $pesan_sukses = ($action === 'setujui') ? 'Penjual telah disetujui dan akunnya telah diaktifkan.' : 'Penjual telah ditolak.';
                                                                                                                                                                                                                header("Location: kelola_verifikasi.php?status=sukses&pesan=" . urlencode($pesan_sukses));
                                                                                                                                                                                                                    exit();

                                                                                                                                                                                                                    } catch (Exception $e) {
                                                                                                                                                                                                                        // Jika ada error, batalkan semua perubahan
                                                                                                                                                                                                                            mysqli_rollback($koneksi);
                                                                                                                                                                                                                                error_log("Error di proses_verifikasi.php: " . $e->getMessage());
                                                                                                                                                                                                                                    header("Location: kelola_verifikasi.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                                                                                                        exit();
                                                                                                                                                                                                                                        } finally {
                                                                                                                                                                                                                                            if (isset($koneksi)) {
                                                                                                                                                                                                                                                    $koneksi->close();
                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                        ?>