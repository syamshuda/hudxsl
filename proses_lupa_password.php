<?php
// /auth/proses_lupa_password.php (Versi Final dengan Konfigurasi Lengkap)

// Memanggil PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak sah');
    }

    $email = trim($_POST['email']);

    try {
        // Cek apakah email ada di database
            $stmt_user = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
                $stmt_user->bind_param("s", $email);
                    $stmt_user->execute();
                        $result_user = $stmt_user->get_result();
                            if ($result_user->num_rows === 0) {
                                    throw new Exception("Email tidak terdaftar di sistem kami.");
                                        }
                                            $stmt_user->close();

                                                // Buat token unik dan waktu kedaluwarsa (1 jam)
                                                    $token = bin2hex(random_bytes(32));
                                                        $expires_at = date('Y-m-d H:i:s', time() + 3600);

                                                            // Simpan token ke database
                                                                $stmt_insert = $koneksi->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                                                                    $stmt_insert->bind_param("sss", $email, $token, $expires_at);
                                                                        if (!$stmt_insert->execute()) {
                                                                                throw new Exception("Gagal menyimpan token reset.");
                                                                                    }
                                                                                        $stmt_insert->close();

                                                                                            // ================== KONFIGURASI PENGIRIMAN EMAIL ==================
                                                                                                $mail = new PHPMailer(true);

                                                                                                    // Konfigurasi Server SMTP (Menggunakan Akun Gmail Anda)
                                                                                                        $mail->isSMTP();
                                                                                                            $mail->Host       = 'smtp.gmail.com';
                                                                                                                $mail->SMTPAuth   = true;
                                                                                                                    $mail->Username   = 'officialsabaku@gmail.com'; // Email Anda
                                                                                                                        $mail->Password   = 'kkfmoiohonrljrrt';       // <-- SANDI APLIKASI BARU ANDA (tanpa spasi)
                                                                                                                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                                                                                                                $mail->Port       = 465;

                                                                                                                                    // Pengirim dan Penerima
                                                                                                                                        $mail->setFrom('officialsabaku@gmail.com', 'Admin Sabaku ID'); // Nama pengirim
                                                                                                                                            $mail->addAddress($email);

                                                                                                                                                // Konten Email
                                                                                                                                                    $reset_link = BASE_URL . "/auth/reset_password.php?token=" . $token;
                                                                                                                                                        $mail->isHTML(true);
                                                                                                                                                            $mail->Subject = 'Reset Password Akun Anda di Sabaku ID';
                                                                                                                                                                $mail->Body    = "Halo,<br><br>" .
                                                                                                                                                                                     "Anda menerima email ini karena ada permintaan untuk mereset password akun Anda.<br>" .
                                                                                                                                                                                                          "Silakan klik link di bawah ini untuk melanjutkan:<br>" .
                                                                                                                                                                                                                               "<a href='" . $reset_link . "'>Reset Password Saya</a><br><br>" .
                                                                                                                                                                                                                                                    "Link ini akan kedaluwarsa dalam 1 jam.<br>" .
                                                                                                                                                                                                                                                                         "Jika Anda tidak merasa meminta ini, abaikan saja email ini.<br><br>" .
                                                                                                                                                                                                                                                                                              "Terima kasih.";

                                                                                                                                                                                                                                                                                                  $mail->send();
                                                                                                                                                                                                                                                                                                      // ==============================================================

                                                                                                                                                                                                                                                                                                          header("Location: lupa_password.php?status=sukses&pesan=Link reset telah dikirim ke email Anda.");

                                                                                                                                                                                                                                                                                                          } catch (Exception $e) {
                                                                                                                                                                                                                                                                                                              // Pesan error dibuat lebih ramah pengguna
                                                                                                                                                                                                                                                                                                                  $error_message = "Gagal mengirim email. Pastikan konfigurasi SMTP sudah benar.";
                                                                                                                                                                                                                                                                                                                      // Untuk debugging, Anda bisa melihat error asli di log server
                                                                                                                                                                                                                                                                                                                          error_log("PHPMailer Error: " . $mail->ErrorInfo . " | General Error: " . $e->getMessage());
                                                                                                                                                                                                                                                                                                                              header("Location: lupa_password.php?status=gagal&pesan=" . urlencode($error_message));
                                                                                                                                                                                                                                                                                                                              } finally {
                                                                                                                                                                                                                                                                                                                                  if(isset($koneksi)) $koneksi->close();
                                                                                                                                                                                                                                                                                                                                      exit();
                                                                                                                                                                                                                                                                                                                                      }