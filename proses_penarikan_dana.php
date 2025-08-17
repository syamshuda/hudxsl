<?php
// /penjual/proses_penarikan_dana.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        $user_id = $_SESSION['user_id'];

        $stmt_toko = $koneksi->prepare("SELECT id, saldo FROM toko WHERE user_id = ?");
        $stmt_toko->bind_param("i", $user_id);
        $stmt_toko->execute();
        $toko_data = $stmt_toko->get_result()->fetch_assoc();
        $toko_id = $toko_data['id'] ?? 0;
        $saldo_saat_ini = (float)($toko_data['saldo'] ?? 0);
        $stmt_toko->close();

        if ($toko_id === 0) {
            header("Location: saldo.php?status=gagal&pesan=" . urlencode("Profil toko tidak ditemukan."));
                exit();
                }

                $jumlah_penarikan = (float)$_POST['jumlah'];
                $deskripsi = trim($_POST['deskripsi_penarikan'] ?? 'Penarikan dana');

                // Validasi Keamanan di Sisi Server
                if ($jumlah_penarikan <= 0 || $jumlah_penarikan > $saldo_saat_ini) {
                    header("Location: saldo.php?status=gagal&pesan=" . urlencode("Jumlah penarikan tidak valid atau melebihi saldo."));
                        exit();
                        }

                        mysqli_begin_transaction($koneksi);

                        try {
                            // 1. Kurangi saldo toko
                                $stmt_saldo = $koneksi->prepare("UPDATE toko SET saldo = saldo - ? WHERE id = ?");
                                    $stmt_saldo->bind_param("di", $jumlah_penarikan, $toko_id);
                                        if (!$stmt_saldo->execute()) {
                                                throw new Exception("Gagal mengurangi saldo toko.");
                                                    }
                                                        $stmt_saldo->close();

                                                            // 2. Buat catatan permintaan penarikan
                                                                $stmt_req = $koneksi->prepare("INSERT INTO penarikan_dana (toko_id, jumlah, deskripsi) VALUES (?, ?, ?)");
                                                                    $stmt_req->bind_param("ids", $toko_id, $jumlah_penarikan, $deskripsi);
                                                                        if (!$stmt_req->execute()) {
                                                                                throw new Exception("Gagal membuat catatan permintaan penarikan.");
                                                                                    }
                                                                                        $penarikan_id_baru = $stmt_req->insert_id;
                                                                                            $stmt_req->close();

                                                                                                // 3. Catat transaksi keluar di riwayat keuangan
                                                                                                    $deskripsi_log = "Permintaan Penarikan Dana #" . $penarikan_id_baru;
                                                                                                        $stmt_log = $koneksi->prepare("INSERT INTO riwayat_transaksi_penjual (toko_id, penarikan_id, jenis_transaksi, jumlah, deskripsi) VALUES (?, ?, 'keluar', ?, ?)");
                                                                                                            $stmt_log->bind_param("iids", $toko_id, $penarikan_id_baru, $jumlah_penarikan, $deskripsi_log);
                                                                                                                if (!$stmt_log->execute()) {
                                                                                                                        throw new Exception("Gagal mencatat riwayat transaksi.");
                                                                                                                            }
                                                                                                                                $stmt_log->close();

                                                                                                                                    // Jika semua berhasil
                                                                                                                                        mysqli_commit($koneksi);
                                                                                                                                            header("Location: saldo.php?status=sukses&pesan=" . urlencode("Permintaan penarikan dana berhasil dibuat."));

                                                                                                                                            } catch (Exception $e) {
                                                                                                                                                mysqli_rollback($koneksi);
                                                                                                                                                    error_log("Error penarikan dana: " . $e->getMessage());
                                                                                                                                                        header("Location: saldo.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                        } finally {
                                                                                                                                                            if (isset($koneksi)) $koneksi->close();
                                                                                                                                                                exit();
                                                                                                                                                                }
                                                                                                                                                                ?>