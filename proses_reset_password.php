<?php
// /auth/proses_reset_password.php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak sah');
    }

    $token = $_POST['token'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    mysqli_begin_transaction($koneksi);

    try {
        if ($password_baru !== $konfirmasi_password) {
                throw new Exception("Konfirmasi password tidak cocok.");
                    }
                        if (strlen($password_baru) < 6) {
                                throw new Exception("Password minimal harus 6 karakter.");
                                    }

                                        // Validasi token sekali lagi
                                            $stmt_token = $koneksi->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
                                                $stmt_token->bind_param("s", $token);
                                                    $stmt_token->execute();
                                                        $result_token = $stmt_token->get_result();
                                                            if ($result_token->num_rows === 0) {
                                                                    throw new Exception("Token tidak valid atau kedaluwarsa.");
                                                                        }
                                                                            $email = $result_token->fetch_assoc()['email'];
                                                                                $stmt_token->close();

                                                                                    // Update password di tabel users
                                                                                        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                                                                                            $stmt_update = $koneksi->prepare("UPDATE users SET password = ? WHERE email = ?");
                                                                                                $stmt_update->bind_param("ss", $hashed_password, $email);
                                                                                                    if (!$stmt_update->execute()) {
                                                                                                            throw new Exception("Gagal memperbarui password.");
                                                                                                                }
                                                                                                                    $stmt_update->close();

                                                                                                                        // Hapus token agar tidak bisa digunakan lagi
                                                                                                                            $stmt_delete = $koneksi->prepare("DELETE FROM password_resets WHERE email = ?");
                                                                                                                                $stmt_delete->bind_param("s", $email);
                                                                                                                                    $stmt_delete->execute();
                                                                                                                                        $stmt_delete->close();
                                                                                                                                            
                                                                                                                                                mysqli_commit($koneksi);
                                                                                                                                                    header("Location: login.php?success=" . urlencode("Password Anda berhasil diubah. Silakan login."));

                                                                                                                                                    } catch (Exception $e) {
                                                                                                                                                        mysqli_rollback($koneksi);
                                                                                                                                                            // Redirect kembali ke halaman reset dengan error
                                                                                                                                                                header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode($e->getMessage()));
                                                                                                                                                                } finally {
                                                                                                                                                                    if(isset($koneksi)) $koneksi->close();
                                                                                                                                                                        exit();
                                                                                                                                                                        }