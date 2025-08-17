<?php
// /penjual/proses_update_rekening.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        $user_id = $_SESSION['user_id'];

        $stmt_toko = $koneksi->prepare("SELECT id FROM toko WHERE user_id = ?");
        $stmt_toko->bind_param("i", $user_id);
        $stmt_toko->execute();
        $toko_id = $stmt_toko->get_result()->fetch_assoc()['id'] ?? 0;
        $stmt_toko->close();

        if ($toko_id === 0) {
            header("Location: saldo.php?status=gagal&pesan=" . urlencode("Profil toko tidak ditemukan."));
                exit();
                }

                $nama_bank = trim($_POST['nama_bank']);
                $nomor_rekening = trim($_POST['nomor_rekening']);
                $nama_pemilik = trim($_POST['nama_pemilik_rekening']);

                if (empty($nama_bank) || empty($nomor_rekening) || empty($nama_pemilik)) {
                    header("Location: saldo.php?status=gagal&pesan=" . urlencode("Semua field rekening wajib diisi."));
                        exit();
                        }

                        $stmt_update = $koneksi->prepare("UPDATE toko SET nama_bank = ?, nomor_rekening = ?, nama_pemilik_rekening = ? WHERE id = ?");
                        $stmt_update->bind_param("sssi", $nama_bank, $nomor_rekening, $nama_pemilik, $toko_id);

                        if ($stmt_update->execute()) {
                            header("Location: saldo.php?status=sukses&pesan=" . urlencode("Informasi rekening berhasil diperbarui."));
                            } else {
                                header("Location: saldo.php?status=gagal&pesan=" . urlencode("Gagal memperbarui rekening."));
                                }

                                $stmt_update->close();
                                $koneksi->close();
                                ?>