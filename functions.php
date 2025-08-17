<?php
// /includes/functions.php (Versi FINAL dengan perbaikan Fatal Error pada logika cashback)

if (!function_exists('getEffectivePriceAndPromoStatus')) {
    function getEffectivePriceAndPromoStatus($product) {
        $harga_normal = (float)$product['harga'];
        $harga_diskon = isset($product['harga_diskon']) ? (float)$product['harga_diskon'] : null;
        $promo_mulai = $product['promo_mulai'];
        $promo_akhir = $product['promo_akhir'];
        $now = new DateTime();
        $effective_price = $harga_normal;
        $is_promo_active = false;
        $promo_text = '';
        $promo_badge_class = '';

        if ($harga_diskon !== null && $harga_diskon < $harga_normal) {
            if ($promo_mulai && $promo_akhir) {
                $start_date = new DateTime($promo_mulai);
                $end_date = new DateTime($promo_akhir);
                if ($now >= $start_date && $now <= $end_date) {
                    $effective_price = $harga_diskon;
                    $is_promo_active = true;
                    $persen_diskon = round((($harga_normal - $harga_diskon) / $harga_normal) * 100);
                    $promo_text = 'Diskon ' . $persen_diskon . '%';
                    $promo_badge_class = 'bg-warning text-dark';
                }
            } else {
                $effective_price = $harga_diskon;
                $is_promo_active = true;
                $persen_diskon = round((($harga_normal - $harga_diskon) / $harga_normal) * 100);
                $promo_text = 'Diskon ' . $persen_diskon . '%';
                $promo_badge_class = 'bg-primary';
            }
        }
        return [
            'price' => $effective_price,
            'is_promo' => $is_promo_active,
            'promo_text' => $promo_text,
            'promo_badge_class' => $promo_badge_class,
            'harga_normal' => $harga_normal
        ];
    }
}

if (!function_exists('selesaikanPesananDanTransferDana')) {
    function selesaikanPesananDanTransferDana($pesanan_id, $koneksi) {
        try {
            $stmt_pesanan = $koneksi->prepare(
                "SELECT pembeli_id, metode_pembayaran, biaya_ongkir, nilai_diskon_voucher, voucher_kode_digunakan, admin_konfirmasi_id 
                 FROM pesanan WHERE id = ?"
            );
            $stmt_pesanan->bind_param("i", $pesanan_id);
            $stmt_pesanan->execute();
            $pesanan_data = $stmt_pesanan->get_result()->fetch_assoc();
            $stmt_pesanan->close();

            if (!$pesanan_data) {
                throw new Exception("Data pesanan #{$pesanan_id} tidak ditemukan.");
            }
            
            $pembeli_id = $pesanan_data['pembeli_id'];
            $metode_pembayaran = $pesanan_data['metode_pembayaran'];
            $biaya_ongkir_total = (float)($pesanan_data['biaya_ongkir'] ?? 0);
            $diskon_voucher_langsung = (float)($pesanan_data['nilai_diskon_voucher'] ?? 0);
            $kode_voucher_digunakan = $pesanan_data['voucher_kode_digunakan'];
            $admin_id = $pesanan_data['admin_konfirmasi_id'] ?? 1;

            $result_komisi = mysqli_query($koneksi, "SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'persentase_komisi'");
            $persen_komisi = (float)(mysqli_fetch_assoc($result_komisi)['nilai_pengaturan'] ?? 0);

            $query_items = "SELECT t.id as toko_id, SUM(dp.jumlah * dp.harga_satuan) as total_produk_toko 
                            FROM detail_pesanan dp 
                            JOIN produk pr ON dp.produk_id = pr.id 
                            JOIN toko t ON pr.toko_id = t.id 
                            WHERE dp.pesanan_id = ? 
                            GROUP BY t.id";
            $stmt_items = $koneksi->prepare($query_items);
            $stmt_items->bind_param("i", $pesanan_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            
            $total_komisi_admin_dari_pesanan = 0;

            while($toko = $result_items->fetch_assoc()) {
                $toko_id = $toko['toko_id'];
                $total_produk_toko = (float)$toko['total_produk_toko'];
                $jumlah_cashback = 0;
                $potongan_ongkir = 0;
                
                if (!empty($kode_voucher_digunakan)) {
                    $stmt_voucher = $koneksi->prepare("SELECT jenis_voucher, nilai FROM voucher WHERE kode = ? AND toko_id = ?");
                    $stmt_voucher->bind_param("si", $kode_voucher_digunakan, $toko_id);
                    $stmt_voucher->execute();
                    $voucher = $stmt_voucher->get_result()->fetch_assoc();
                    $stmt_voucher->close();

                    if ($voucher) {
                        if ($voucher['jenis_voucher'] === 'cashback') {
                            $jumlah_cashback = (float)$voucher['nilai'];
                        }
                        if ($voucher['jenis_voucher'] === 'gratis_ongkir') {
                            $potongan_ongkir = min($biaya_ongkir_total, (float)$voucher['nilai']);
                        }
                    }
                }

                $pendapatan_bersih_sebelum_komisi = $total_produk_toko - $diskon_voucher_langsung - $jumlah_cashback;
                $komisi_admin = $pendapatan_bersih_sebelum_komisi * ($persen_komisi / 100);
                $total_komisi_admin_dari_pesanan += $komisi_admin;
                
                if ($metode_pembayaran !== 'COD') {
                    $pendapatan_final_penjual = ($pendapatan_bersih_sebelum_komisi - $komisi_admin) + ($biaya_ongkir_total - $potongan_ongkir);
                    $perubahan_saldo = max(0, $pendapatan_final_penjual);
                    
                    $deskripsi_log = sprintf(
                        "Pendapatan Pesanan #%d (Produk: Rp %s, Ongkir: Rp %s, Diskon: -Rp %s, Cashback: -Rp %s, Pot. Ongkir: -Rp %s, Komisi: -Rp %s)",
                        $pesanan_id, number_format($total_produk_toko), number_format($biaya_ongkir_total), number_format($diskon_voucher_langsung), number_format($jumlah_cashback), number_format($potongan_ongkir), number_format($komisi_admin, 2)
                    );
                    
                    $stmt_update_saldo = $koneksi->prepare("UPDATE toko SET saldo = saldo + ? WHERE id = ?");
                    $stmt_update_saldo->bind_param("di", $perubahan_saldo, $toko_id);
                    if (!$stmt_update_saldo->execute()) throw new Exception("Gagal update saldo toko #{$toko_id}.");
                    $stmt_update_saldo->close();

                    $stmt_log_penjual = $koneksi->prepare("INSERT INTO riwayat_transaksi_penjual (toko_id, pesanan_id, jenis_transaksi, jumlah, deskripsi) VALUES (?, ?, 'masuk', ?, ?)");
                    $stmt_log_penjual->bind_param("iids", $toko_id, $pesanan_id, $perubahan_saldo, $deskripsi_log); 
                    if (!$stmt_log_penjual->execute()) throw new Exception("Gagal mencatat riwayat transaksi penjual.");
                    $stmt_log_penjual->close();
                }

                if ($jumlah_cashback > 0) {
                    $stmt_saldo_sebelum = $koneksi->prepare("SELECT saldo FROM users WHERE id = ?");
                    $stmt_saldo_sebelum->bind_param("i", $pembeli_id);
                    $stmt_saldo_sebelum->execute();
                    $saldo_sebelum = (float)$stmt_saldo_sebelum->get_result()->fetch_assoc()['saldo'];
                    $stmt_saldo_sebelum->close();
                    
                    $saldo_sesudah = $saldo_sebelum + $jumlah_cashback;

                    $stmt_update_saldo_pembeli = $koneksi->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
                    $stmt_update_saldo_pembeli->bind_param("di", $jumlah_cashback, $pembeli_id);
                    if (!$stmt_update_saldo_pembeli->execute()) throw new Exception("Gagal menambahkan cashback.");
                    $stmt_update_saldo_pembeli->close();
                    
                    $deskripsi_cashback = "Cashback dari Pesanan #" . $pesanan_id;
                    
                    // ================== PERBAIKAN FATAL ERROR DI SINI ==================
                    // Memastikan tipe data yang benar untuk 6 variabel: i, i, d, d, d, s
                    $stmt_log_cashback = $koneksi->prepare("INSERT INTO riwayat_saldo_pembeli (user_id, pesanan_id, jenis_transaksi, jumlah, saldo_sebelum, saldo_sesudah, deskripsi) VALUES (?, ?, 'masuk', ?, ?, ?, ?)");
                    $stmt_log_cashback->bind_param("iiddds", $pembeli_id, $pesanan_id, $jumlah_cashback, $saldo_sebelum, $saldo_sesudah, $deskripsi_cashback);
                    // ================== AKHIR PERBAIKAN ==================
                    
                    if (!$stmt_log_cashback->execute()) throw new Exception("Gagal mencatat riwayat cashback: " . $stmt_log_cashback->error);
                    $stmt_log_cashback->close();
                }
            }
            $stmt_items->close();

            if ($total_komisi_admin_dari_pesanan > 0) {
                $deskripsi_komisi_admin = "Komisi {$persen_komisi}% dari Pesanan #" . $pesanan_id;
                $stmt_log_admin = $koneksi->prepare("INSERT INTO riwayat_transaksi_admin (admin_user_id, jenis_transaksi, referensi_id, jumlah, deskripsi) VALUES (?, 'komisi_masuk', ?, ?, ?)");
                $stmt_log_admin->bind_param("iids", $admin_id, $pesanan_id, $total_komisi_admin_dari_pesanan, $deskripsi_komisi_admin);
                if (!$stmt_log_admin->execute()) throw new Exception("Gagal mencatat komisi admin.");
                $stmt_log_admin->close();
                
                $stmt_update_admin_saldo = $koneksi->prepare("UPDATE pengaturan SET nilai_pengaturan = nilai_pengaturan + ? WHERE nama_pengaturan = 'saldo_admin'");
                $stmt_update_admin_saldo->bind_param("d", $total_komisi_admin_dari_pesanan);
                if (!$stmt_update_admin_saldo->execute()) throw new Exception("Gagal update saldo admin.");
                $stmt_update_admin_saldo->close();
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in selesaikanPesananDanTransferDana for order #{$pesanan_id}: " . $e->getMessage());
            throw $e;
        }
    }
}
?>