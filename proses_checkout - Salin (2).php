<?php
// /proses_checkout.php (Versi Final)
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

$is_buy_now = isset($_POST['is_buy_now']) && $_POST['is_buy_now'] == '1';
$items_to_process = $is_buy_now ? [$_SESSION['buy_now_item']['produk_id'] => $_SESSION['buy_now_item']['jumlah']] : ($_SESSION['keranjang'] ?? []);

if (empty($items_to_process)) {
    header("Location: " . BASE_URL . "/keranjang.php?status=kosong");
    exit();
}

$user_id = $_SESSION['user_id'];
mysqli_begin_transaction($koneksi);

try {
    // 1. Hitung subtotal, berat, dan jenis produk dari server
    $total_harga_produk_server = 0;
    $total_berat = 0;
    $ada_produk_fisik_server = false;
    $kecamatan_asal_toko = '';
    $ids_to_fetch = array_keys($items_to_process);
    $produk_data_map = [];

    if (!empty($ids_to_fetch)) {
        $ids_string = implode(',', array_map('intval', $ids_to_fetch));
        $query_produk_detail = "SELECT p.*, t.kecamatan as toko_kecamatan FROM produk p JOIN toko t ON p.toko_id = t.id WHERE p.id IN ($ids_string)";
        $result_produk_detail = mysqli_query($koneksi, $query_produk_detail);
        while ($prod_data = mysqli_fetch_assoc($result_produk_detail)) {
            $produk_data_map[$prod_data['id']] = $prod_data;
            if ($prod_data['jenis_produk'] === 'fisik') {
                $ada_produk_fisik_server = true;
                if(empty($kecamatan_asal_toko)) $kecamatan_asal_toko = $prod_data['toko_kecamatan'];
                $total_berat += $prod_data['berat'] * $items_to_process[$prod_data['id']];
            }
            $harga_efektif_item = getEffectivePriceAndPromoStatus($prod_data)['price'];
            $total_harga_produk_server += $harga_efektif_item * $items_to_process[$prod_data['id']];
        }
    }

    // 2. Ambil data dari form
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'Transfer Bank';
    $catatan_pembeli = trim($_POST['catatan_pembeli'] ?? '');
    $email_pengiriman_digital = trim($_POST['email_pengiriman_digital'] ?? '');
    
    // Inisialisasi variabel alamat
    $nama_penerima = ''; $no_telepon = ''; $alamat_lengkap = ''; $kecamatan_tujuan = ''; $kota_tujuan = ''; $provinsi_tujuan = ''; $kelurahan_desa = ''; $kode_pos = ''; $kurir = null;

    if ($ada_produk_fisik_server) {
        $kurir = $_POST['kurir'] ?? null;
        $nama_penerima = trim($_POST['nama_penerima'] ?? '');
        $no_telepon = trim($_POST['no_telepon'] ?? '');
        $alamat_lengkap = trim($_POST['alamat_lengkap'] ?? '');
        $kecamatan_tujuan = trim(strtoupper($_POST['kecamatan_tujuan'] ?? ''));
        $kota_tujuan = trim(strtoupper($_POST['kota_tujuan'] ?? ''));
        $provinsi_tujuan = trim(strtoupper($_POST['provinsi_tujuan'] ?? ''));
        $kelurahan_desa = trim($_POST['kelurahan_desa'] ?? '');
        $kode_pos = trim($_POST['kode_pos'] ?? '');
        if(empty($kurir) || empty($kecamatan_tujuan) || empty($nama_penerima) || empty($no_telepon)) {
            throw new Exception("Alamat dan kurir harus diisi lengkap.");
        }
    }
    
    // 3. Kalkulasi Ulang Ongkir di Server (Validasi Wajib)
    $biaya_ongkir = 0;
    if ($ada_produk_fisik_server) {
        $stmt_ongkir = $koneksi->prepare("SELECT biaya FROM ongkos_kirim WHERE kurir = ? AND kecamatan_asal = ? AND kecamatan_tujuan = ? LIMIT 1");
        $stmt_ongkir->bind_param("sss", $kurir, $kecamatan_asal_toko, $kecamatan_tujuan);
        $stmt_ongkir->execute();
        $result_ongkir = $stmt_ongkir->get_result();
        if($result_ongkir->num_rows > 0) {
            $biaya_dasar = (float)$result_ongkir->fetch_assoc()['biaya'];
            $kelipatan_berat = ceil(($total_berat > 0 ? $total_berat : 1) / 1000);
            $biaya_ongkir = $biaya_dasar * $kelipatan_berat;
        } else {
            throw new Exception("Tarif pengiriman ke tujuan ini tidak tersedia.");
        }
        $stmt_ongkir->close();
    }
    
    // 4. Proses voucher dan kalkulasi total akhir
    $nilai_diskon_voucher = (float)($_POST['nilai_diskon_voucher'] ?? 0);
    $kode_voucher_digunakan = trim($_POST['kode_voucher_digunakan'] ?? '');
    if (empty($kode_voucher_digunakan)) $kode_voucher_digunakan = NULL;

    $total_harga_untuk_db = $total_harga_produk_server + $biaya_ongkir - $nilai_diskon_voucher;
    $total_harga_untuk_db = max(0, $total_harga_untuk_db);
    $kode_unik_tambahan = ($metode_pembayaran !== 'COD') ? rand(100, 999) : 0;
    $total_dengan_kode_unik = $total_harga_untuk_db + $kode_unik_tambahan;

    // 5. Simpan ke database
    $status_pesanan = ($metode_pembayaran === 'COD') ? 'diproses' : 'menunggu_pembayaran';
    
    $stmt_pesanan = $koneksi->prepare("INSERT INTO pesanan (pembeli_id, tanggal_pesanan, total_harga, biaya_ongkir, kode_unik, total_dengan_kode, nama_penerima, no_telepon, alamat_lengkap, kecamatan, kota, provinsi, kelurahan_desa, kode_pos, kurir, catatan_pembeli, email_pengiriman_digital, status_pesanan, voucher_kode_digunakan, nilai_diskon_voucher, metode_pembayaran) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt_pesanan->bind_param("iddidsssssssssssssds", 
        $user_id, $total_harga_produk_server, $biaya_ongkir, $kode_unik_tambahan, $total_dengan_kode_unik, 
        $nama_penerima, $no_telepon, $alamat_lengkap, $kecamatan_tujuan, $kota_tujuan, $provinsi_tujuan, $kelurahan_desa, $kode_pos, 
        $kurir, $catatan_pembeli, $email_pengiriman_digital, $status_pesanan, $kode_voucher_digunakan, $nilai_diskon_voucher, $metode_pembayaran
    );
    if (!$stmt_pesanan->execute()) throw new Exception("Gagal menyimpan pesanan: " . $stmt_pesanan->error);
    
    $pesanan_id = $stmt_pesanan->insert_id;
    $stmt_pesanan->close();
    $_SESSION['last_pesanan_id'] = $pesanan_id;

    $stmt_detail = $koneksi->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
    $stmt_stok = $koneksi->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
    
    foreach ($items_to_process as $produk_id_item => $jumlah_item) {
        if (isset($produk_data_map[$produk_id_item])) {
            $harga_satuan_efektif = getEffectivePriceAndPromoStatus($produk_data_map[$produk_id_item])['price'];
            $stmt_detail->bind_param("iiid", $pesanan_id, $produk_id_item, $jumlah_item, $harga_satuan_efektif);
            if(!$stmt_detail->execute()) throw new Exception("Gagal menyimpan detail pesanan.");
            
            $stmt_stok->bind_param("ii", $jumlah_item, $produk_id_item);
            if(!$stmt_stok->execute()) throw new Exception("Gagal mengurangi stok produk.");
        }
    }
    $stmt_detail->close();
    $stmt_stok->close();

    // 6. Kosongkan session keranjang/buy_now dan commit
    if ($is_buy_now) {
        unset($_SESSION['buy_now_item']);
    } else {
        unset($_SESSION['keranjang']);
    }
    
    mysqli_commit($koneksi);
    header("Location: " . BASE_URL . "/pesanan_sukses.php?id=" . $pesanan_id);
    exit();

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    error_log("Checkout Error: " . $e->getMessage());
    header("Location: " . BASE_URL . "/checkout.php?status=gagal&pesan=" . urlencode($e->getMessage()));
    exit();
}
?>