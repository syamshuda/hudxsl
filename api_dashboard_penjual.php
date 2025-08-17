<?php
// /penjual/api_dashboard_penjual.php
require_once '../config/database.php';
header('Content-Type: application/json');

// Proteksi API
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual') {
    http_response_code(403);
        echo json_encode(['error' => 'Akses ditolak']);
            exit();
            }

            $user_id = $_SESSION['user_id'];

            // Ambil toko_id dari user_id session
            $stmt_toko = $koneksi->prepare("SELECT id FROM toko WHERE user_id = ?");
            $stmt_toko->bind_param("i", $user_id);
            $stmt_toko->execute();
            $result_toko = $stmt_toko->get_result();
            $toko = $result_toko->fetch_assoc();
            $stmt_toko->close();

            if (!$toko) {
                http_response_code(404);
                    echo json_encode(['error' => 'Toko tidak ditemukan']);
                        exit();
                        }
                        $toko_id = $toko['id'];

                        // Data yang akan dikirim kembali sebagai JSON
                        $response_data = [
                            'statistik' => [
                                    'total_produk' => 0,
                                            'pesanan_perlu_diproses' => 0,
                                                    'saldo' => 'Rp 0'
                                                        ]
                                                        ];

                                                        // 1. Ambil data statistik terbaru
                                                        $response_data['statistik']['total_produk'] = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(id) as total FROM produk WHERE toko_id = $toko_id"))['total'];
                                                        $response_data['statistik']['pesanan_perlu_diproses'] = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT p.id) as total FROM pesanan p JOIN detail_pesanan dp ON p.id = dp.pesanan_id JOIN produk pr ON dp.produk_id = pr.id WHERE pr.toko_id = $toko_id AND p.status_pesanan = 'diproses'"))['total'];
                                                        $saldo_db = (float)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT saldo FROM toko WHERE id = $toko_id"))['saldo'];
                                                        $response_data['statistik']['saldo'] = 'Rp ' . number_format($saldo_db, 2, ',', '.');


                                                        // Kembalikan data dalam format JSON
                                                        echo json_encode($response_data);

                                                        $koneksi->close();
                                                        ?>