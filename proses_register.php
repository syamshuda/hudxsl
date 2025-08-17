<?php
// /auth/proses_register.php (Versi Perbaikan Status Penjual)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';

// Fungsi bantuan untuk menangani upload file
function upload_gambar_verifikasi($file_input, $target_dir) {
    if (!isset($file_input) || $file_input['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Gagal mengunggah gambar. Pastikan Anda memilih file.");
                }
                    if (!is_dir($target_dir)) {
                            if (!mkdir($target_dir, 0755, true)) {
                                        throw new Exception("Gagal membuat direktori upload.");
                                                }
                                                    }
                                                        $nama_gambar = uniqid() . '-' . basename($file_input["name"]);
                                                            $target_file = $target_dir . $nama_gambar;
                                                                if (move_uploaded_file($file_input["tmp_name"], $target_file)) {
                                                                        return $nama_gambar;
                                                                            } else {
                                                                                    throw new Exception("Gagal memindahkan file yang diunggah.");
                                                                                        }
                                                                                        }

                                                                                        // Ambil data dari form
                                                                                        $username = trim($_POST['username']);
                                                                                        $email = trim($_POST['email']);
                                                                                        $nama_lengkap = trim($_POST['nama_lengkap']);
                                                                                        $password = $_POST['password'];
                                                                                        $role = $_POST['role'];

                                                                                        // Validasi dasar
                                                                                        if (empty($username) || empty($email) || empty($password) || empty($role) || empty($nama_lengkap)) {
                                                                                            header("Location: register.php?error=Semua field wajib diisi");
                                                                                                exit();
                                                                                                }
                                                                                                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                                                                                    header("Location: register.php?error=Format email tidak valid");
                                                                                                        exit();
                                                                                                        }

                                                                                                        // Cek duplikasi username atau email
                                                                                                        $stmt_check = $koneksi->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                                                                                                        $stmt_check->bind_param("ss", $username, $email);
                                                                                                        $stmt_check->execute();
                                                                                                        if ($stmt_check->get_result()->num_rows > 0) {
                                                                                                            header("Location: register.php?error=Username atau email sudah terdaftar");
                                                                                                                exit();
                                                                                                                }
                                                                                                                $stmt_check->close();

                                                                                                                // Hash password
                                                                                                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                                                                                                                // --- PERUBAHAN UTAMA DI SINI ---
                                                                                                                // Tentukan status aktif berdasarkan peran (role)
                                                                                                                $is_active_status = ($role === 'pembeli') ? 1 : 0; // Pembeli langsung aktif, Penjual tidak aktif (0)
                                                                                                                // --- AKHIR PERUBAHAN ---

                                                                                                                mysqli_begin_transaction($koneksi);

                                                                                                                try {
                                                                                                                    // Masukkan data pengguna baru dengan status is_active yang sudah ditentukan
                                                                                                                        $stmt_insert = $koneksi->prepare("INSERT INTO users (username, email, nama_lengkap, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                                                                                                                            $stmt_insert->bind_param("sssssi", $username, $email, $nama_lengkap, $hashed_password, $role, $is_active_status);
                                                                                                                                
                                                                                                                                    if (!$stmt_insert->execute()) {
                                                                                                                                            throw new Exception("Terjadi kesalahan saat mendaftarkan pengguna.");
                                                                                                                                                }
                                                                                                                                                    $user_id = $stmt_insert->insert_id;
                                                                                                                                                        $stmt_insert->close();

                                                                                                                                                            // Jika yang mendaftar adalah penjual, proses data toko dan verifikasi
                                                                                                                                                                if ($role === 'penjual') {
                                                                                                                                                                        $target_dir_verifikasi = "../uploads/verifikasi/";
                                                                                                                                                                                $nama_file_ktp = upload_gambar_verifikasi($_FILES['foto_ktp'], $target_dir_verifikasi);
                                                                                                                                                                                        $nama_file_wajah = upload_gambar_verifikasi($_FILES['foto_wajah'], $target_dir_verifikasi);
                                                                                                                                                                                                
                                                                                                                                                                                                        $nama_toko_default = "Toko " . $nama_lengkap;
                                                                                                                                                                                                                $deskripsi_default = "Selamat datang di " . $nama_toko_default;
                                                                                                                                                                                                                        
                                                                                                                                                                                                                                $stmt_toko = $koneksi->prepare("INSERT INTO toko (user_id, nama_toko, deskripsi, foto_ktp, foto_wajah) VALUES (?, ?, ?, ?, ?)");
                                                                                                                                                                                                                                        $stmt_toko->bind_param("issss", $user_id, $nama_toko_default, $deskripsi_default, $nama_file_ktp, $nama_file_wajah);
                                                                                                                                                                                                                                                
                                                                                                                                                                                                                                                        if (!$stmt_toko->execute()) {
                                                                                                                                                                                                                                                                    if (file_exists($target_dir_verifikasi . $nama_file_ktp)) unlink($target_dir_verifikasi . $nama_file_ktp);
                                                                                                                                                                                                                                                                                if (file_exists($target_dir_verifikasi . $nama_file_wajah)) unlink($target_dir_verifikasi . $nama_file_wajah);
                                                                                                                                                                                                                                                                                            throw new Exception("Terjadi kesalahan saat membuat profil toko.");
                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                            $stmt_toko->close();
                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                    
                                                                                                                                                                                                                                                                                                                        mysqli_commit($koneksi);

                                                                                                                                                                                                                                                                                                                            // Pesan sukses yang berbeda untuk penjual dan pembeli
                                                                                                                                                                                                                                                                                                                                $pesan_sukses = ($role === 'penjual') 
                                                                                                                                                                                                                                                                                                                                        ? "Registrasi penjual berhasil! Akun Anda akan aktif setelah diverifikasi oleh admin."
                                                                                                                                                                                                                                                                                                                                                : "Registrasi berhasil! Silakan login.";
                                                                                                                                                                                                                                                                                                                                                        
                                                                                                                                                                                                                                                                                                                                                            header("Location: login.php?success=" . urlencode($pesan_sukses));

                                                                                                                                                                                                                                                                                                                                                            } catch (Exception $e) {
                                                                                                                                                                                                                                                                                                                                                                mysqli_rollback($koneksi);
                                                                                                                                                                                                                                                                                                                                                                    header("Location: register.php?error=" . urlencode($e->getMessage()));
                                                                                                                                                                                                                                                                                                                                                                    } finally {
                                                                                                                                                                                                                                                                                                                                                                        if (isset($koneksi)) {
                                                                                                                                                                                                                                                                                                                                                                                $koneksi->close();
                                                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                                                        exit();
                                                                                                                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                                                                                                                        ?>