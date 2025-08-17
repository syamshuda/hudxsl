<?php
// /penjual/proses_edit_produk.php
require_once '../config/database.php';

try {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /auth/login.php");
                    exit();
                        }

                            // Ambil data form
                                $produk_id = (int)$_POST['produk_id'];
                                    $nama_produk = trim($_POST['nama_produk']);
                                        $deskripsi = trim($_POST['deskripsi']);
                                            $harga = (float)$_POST['harga'];
                                                $stok = (int)$_POST['stok'];
                                                    $kategori_id = (int)$_POST['kategori_id'];
                                                        $jenis_produk = $_POST['jenis_produk'];
                                                            $berat = ($jenis_produk === 'fisik') ? (int)$_POST['berat'] : 0;
                                                                $link_digital = ($jenis_produk === 'digital') ? trim($_POST['link_digital']) : NULL;
                                                                    $gambar_lama = $_POST['gambar_lama'];
                                                                        $user_id = $_SESSION['user_id'];
                                                                            
                                                                                $harga_diskon = isset($_POST['harga_diskon']) && $_POST['harga_diskon'] !== '' ? (float)$_POST['harga_diskon'] : NULL;
                                                                                    $promo_mulai = isset($_POST['promo_mulai']) && $_POST['promo_mulai'] !== '' ? $_POST['promo_mulai'] : NULL;
                                                                                        $promo_akhir = isset($_POST['promo_akhir']) && $_POST['promo_akhir'] !== '' ? $_POST['promo_akhir'] : NULL;

                                                                                            // Validasi
                                                                                                if ($jenis_produk === 'fisik' && $berat <= 0) {
                                                                                                        throw new Exception("Berat untuk produk fisik wajib diisi dan harus lebih dari 0.");
                                                                                                            }
                                                                                                                 if ($jenis_produk === 'digital' && empty($link_digital)) {
                                                                                                                         throw new Exception("Link Google Drive wajib diisi untuk produk digital.");
                                                                                                                             }
                                                                                                                                 if ($harga_diskon !== NULL && $harga_diskon >= $harga) {
                                                                                                                                         throw new Exception("Harga diskon harus lebih rendah dari harga normal.");
                                                                                                                                             }

                                                                                                                                                 // Verifikasi kepemilikan
                                                                                                                                                     $stmt_verify = $koneksi->prepare("SELECT p.id FROM produk p JOIN toko t ON p.toko_id = t.id WHERE p.id = ? AND t.user_id = ?");
                                                                                                                                                         $stmt_verify->bind_param("ii", $produk_id, $user_id);
                                                                                                                                                             $stmt_verify->execute();
                                                                                                                                                                 if ($stmt_verify->get_result()->num_rows !== 1) {
                                                                                                                                                                         throw new Exception("Produk tidak ditemukan atau Anda tidak memiliki izin.");
                                                                                                                                                                             }
                                                                                                                                                                                 $stmt_verify->close();

                                                                                                                                                                                     // Proses upload gambar baru jika ada
                                                                                                                                                                                         $nama_gambar_baru = $gambar_lama;
                                                                                                                                                                                             if (isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] === UPLOAD_ERR_OK) {
                                                                                                                                                                                                     $target_dir = "../uploads/produk/";
                                                                                                                                                                                                             $nama_gambar_baru = uniqid() . '-' . basename($_FILES['gambar_produk']["name"]);
                                                                                                                                                                                                                     $target_file = $target_dir . $nama_gambar_baru;
                                                                                                                                                                                                                             if (move_uploaded_file($_FILES['gambar_produk']["tmp_name"], $target_file)) {
                                                                                                                                                                                                                                         if (!empty($gambar_lama) && file_exists($target_dir . $gambar_lama)) {
                                                                                                                                                                                                                                                         unlink($target_dir . $gambar_lama);
                                                                                                                                                                                                                                                                     }
                                                                                                                                                                                                                                                                             } else {
                                                                                                                                                                                                                                                                                         $nama_gambar_baru = $gambar_lama;
                                                                                                                                                                                                                                                                                                 }
                                                                                                                                                                                                                                                                                                     }

                                                                                                                                                                                                                                                                                                         // Update data di database
                                                                                                                                                                                                                                                                                                             $stmt_update = $koneksi->prepare("UPDATE produk SET kategori_id=?, jenis_produk=?, nama_produk=?, deskripsi=?, harga=?, harga_diskon=?, promo_mulai=?, promo_akhir=?, stok=?, berat=?, gambar_produk=?, link_digital=?, status_moderasi='ditinjau' WHERE id=?");
                                                                                                                                                                                                                                                                                                                 $stmt_update->bind_param("isssddssiissi", $kategori_id, $jenis_produk, $nama_produk, $deskripsi, $harga, $harga_diskon, $promo_mulai, $promo_akhir, $stok, $berat, $nama_gambar_baru, $link_digital, $produk_id);

                                                                                                                                                                                                                                                                                                                     if ($stmt_update->execute()) {
                                                                                                                                                                                                                                                                                                                             header("Location: produk_saya.php?status=sukses");
                                                                                                                                                                                                                                                                                                                                 } else {
                                                                                                                                                                                                                                                                                                                                         throw new Exception("Gagal menyimpan perubahan: " . $stmt_update->error);
                                                                                                                                                                                                                                                                                                                                             }
                                                                                                                                                                                                                                                                                                                                                 $stmt_update->close();

                                                                                                                                                                                                                                                                                                                                                 } catch (Exception $e) {
                                                                                                                                                                                                                                                                                                                                                     header("Location: edit_produk.php?id=$produk_id&status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                                                                                                                                                                                                                                                                     } finally {
                                                                                                                                                                                                                                                                                                                                                         if (isset($koneksi)) $koneksi->close();
                                                                                                                                                                                                                                                                                                                                                             exit();
                                                                                                                                                                                                                                                                                                                                                             }
                                                                                                                                                                                                                                                                                                                                                             ?>