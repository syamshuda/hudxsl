<?php
// /admin/api_dashboard.php (Versi Perbaikan Final)
require_once '../config/database.php';
header('Content-Type: application/json');

// Proteksi API: Pastikan hanya admin yang login yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Kode error 'Forbidden'
        echo json_encode(['error' => 'Akses ditolak']);
            exit();
            }

            // Inisialisasi struktur data yang akan dikirim
            $response_data = [
                'pesanan_baru' => [],
                    'statistik' => [
                            'produk_perlu_moderasi' => 0,
                                    'total_pesanan' => 0,
                                            'pesanan_baru_count' => 0
                                                ]
                                                ];

                                                try {
                                                    // 1. Ambil data pesanan baru (status 'pending')
                                                        // PERBAIKAN: Mengganti 'p.peli_id' menjadi 'p.pembeli_id'
                                                            $query_pesanan = "
                                                                    SELECT p.id, u.nama_lengkap, p.total_dengan_kode, p.tanggal_pesanan
                                                                            FROM pesanan p
                                                                                    JOIN users u ON p.pembeli_id = u.id
                                                                                            WHERE p.status_pesanan = 'pending'
                                                                                                    ORDER BY p.tanggal_pesanan DESC
                                                                                                        ";
                                                                                                            $result_pesanan = $koneksi->query($query_pesanan);
                                                                                                                
                                                                                                                    if ($result_pesanan === false) {
                                                                                                                            // Jika query gagal, lempar exception untuk ditangani di blok catch
                                                                                                                                    throw new Exception("Query pesanan baru gagal: " . $koneksi->error);
                                                                                                                                        }
                                                                                                                                            
                                                                                                                                                $pesanan_baru = [];
                                                                                                                                                    while ($row = $result_pesanan->fetch_assoc()) {
                                                                                                                                                            $pesanan_baru[] = [
                                                                                                                                                                        'id' => $row['id'],
                                                                                                                                                                                    'nama_pembeli' => htmlspecialchars($row['nama_lengkap']),
                                                                                                                                                                                                'total' => 'Rp ' . number_format($row['total_dengan_kode']),
                                                                                                                                                                                                            'waktu' => date('d M Y, H:i', strtotime($row['tanggal_pesanan']))
                                                                                                                                                                                                                    ];
                                                                                                                                                                                                                        }
                                                                                                                                                                                                                            $response_data['pesanan_baru'] = $pesanan_baru;
                                                                                                                                                                                                                                $response_data['statistik']['pesanan_baru_count'] = count($pesanan_baru);

                                                                                                                                                                                                                                    // 2. Ambil data statistik yang diperlukan
                                                                                                                                                                                                                                        $response_data['statistik']['produk_perlu_moderasi'] = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(id) as total FROM produk WHERE status_moderasi = 'ditinjau'"))['total'];
                                                                                                                                                                                                                                            $response_data['statistik']['total_pesanan'] = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(id) as total FROM pesanan WHERE status_pesanan != 'menunggu_pembayaran'"))['total'];
                                                                                                                                                                                                                                                
                                                                                                                                                                                                                                                } catch (Exception $e) {
                                                                                                                                                                                                                                                    // Jika ada error di mana pun dalam blok try, kirim response error
                                                                                                                                                                                                                                                        http_response_code(500); // Kode error 'Internal Server Error'
                                                                                                                                                                                                                                                            $response_data['error'] = "Terjadi kesalahan di server: " . $e->getMessage();
                                                                                                                                                                                                                                                            }


                                                                                                                                                                                                                                                            // Kembalikan data dalam format JSON
                                                                                                                                                                                                                                                            echo json_encode($response_data);

                                                                                                                                                                                                                                                            $koneksi->close();
                                                                                                                                                                                                                                                            ?>