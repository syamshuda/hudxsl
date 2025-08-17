<?php
session_start(); // PASTIKAN INI ADA DI BARIS PALING PERTAMA
// /admin/proses_toggle_user.php

// AKTIFKAN INI SEMENTARA UNTUK DEBUGGING. HAPUS KETIKA SELESAI.
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';

try {
    // Proteksi (tetap di dalam try-catch, tetapi juga bisa di luar jika ingin redirect duluan)
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: /auth/login.php");
                        exit();
                            }

                                $user_id = (int)$_POST['user_id'];
                                    $action = $_POST['action']; // 'activate' atau 'deactivate'

                                        // Validasi dasar
                                            if (!in_array($action, ['activate', 'deactivate'])) {
                                                    throw new Exception("Aksi tidak valid.");
                                                        }
                                                            if ($user_id <= 0) {
                                                                    throw new Exception("ID pengguna tidak valid.");
                                                                        }

                                                                            $new_is_active_status = ($action === 'activate') ? 1 : 0;

                                                                                // Tambahkan pengecekan agar admin tidak bisa menonaktifkan dirinya sendiri
                                                                                    if ($user_id == $_SESSION['user_id'] && $action === 'deactivate') {
                                                                                            throw new Exception("Anda tidak dapat menonaktifkan akun Anda sendiri.");
                                                                                                }

                                                                                                    // Siapkan dan jalankan query UPDATE
                                                                                                        $stmt = $koneksi->prepare("UPDATE users SET is_active = ? WHERE id = ?");
                                                                                                            if ($stmt === false) {
                                                                                                                    throw new Exception("Sistem Error: Gagal menyiapkan query update pengguna: " . $koneksi->error);
                                                                                                                        }
                                                                                                                            $stmt->bind_param("ii", $new_is_active_status, $user_id);

                                                                                                                                if (!$stmt->execute()) {
                                                                                                                                        throw new Exception("Gagal mengeksekusi update status pengguna: " . $stmt->error);
                                                                                                                                            }
                                                                                                                                                $stmt->close();

                                                                                                                                                    // Jika berhasil
                                                                                                                                                        header("Location: kelola_pengguna.php?status=sukses");
                                                                                                                                                            exit();

                                                                                                                                                            } catch (Exception $e) {
                                                                                                                                                                // Tangkap semua exception dan arahkan kembali dengan pesan error
                                                                                                                                                                    error_log("Error in proses_toggle_user.php: " . $e->getMessage()); // Log error ke file log server
                                                                                                                                                                        header("Location: kelola_pengguna.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                                            exit();
                                                                                                                                                                            } finally {
                                                                                                                                                                                // Pastikan koneksi ditutup
                                                                                                                                                                                    if (isset($koneksi) && $koneksi instanceof mysqli) {
                                                                                                                                                                                            $koneksi->close();
                                                                                                                                                                                                }
                                                                                                                                                                                                }
                                                                                                                                                                                                ?>