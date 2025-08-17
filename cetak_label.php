<?php
// /penjual/cetak_label.php (Versi dengan Barcode Generator yang Stabil)
require_once '../config/database.php';

// ================== GENERATOR BARCODE BARU & STABIL ==================
class Barcode128 {
    private $codes = array(
        '111221', '121121', '121211', '112211', '122111', '122211', '111212', '121112', '121212', '211112', '211211', '221111', '112112', '121122', '112212', '122112',
        '122211', '111222', '121122', '121221', '112212', '122112', '122211', '112122', '112221', '121122', '122121', '121211', '112111', '121111', '111111', '212121',
        '211121', '211221', '112121', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111',
        '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111',
        '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111',
        '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111', '112111',
        '112111', '212112', '211212', '211212', '112121', '112121', '112121', '112121', '112121', '112121', '112121', '112121'
    );
    private $char_set = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
    private $start_set = array('B' => 104, 'C' => 105);
    private $stop_code = '2111212';
    private $stop_checksum = 106;

    public function generate($text, $widthFactor = 2, $height = 60) {
        if (empty(trim($text))) { return null; }
        
        $barcode_len = 0;
        $barcode_array = array();
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $char_code = strpos($this->char_set, $char);
            if ($char_code === false) continue;
            $barcode_array[] = $char_code;
        }

        if (empty($barcode_array)) return null;

        $sum_check = $this->start_set['B'];
        $barcode_str = $this->codes[$this->start_set['B']];
        foreach ($barcode_array as $key => $code) {
            $sum_check += $code * ($key + 1);
            $barcode_str .= $this->codes[$code];
        }
        $checksum = $sum_check % 103;
        $barcode_str .= $this->codes[$checksum];
        $barcode_str .= $this->stop_code;

        $img_width = strlen($barcode_str) * $widthFactor;
        $image = imagecreate($img_width, $height);
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        $px = 0;
        for ($i = 0; $i < strlen($barcode_str); $i++) {
            $bar_width = (int)$barcode_str[$i] * $widthFactor;
            if ($i % 2 == 0) { // Bar
                imagefilledrectangle($image, $px, 0, $px + $bar_width - 1, $height, $black);
            }
            $px += $bar_width;
        }

        ob_start();
        imagepng($image);
        $image_data = ob_get_clean();
        imagedestroy($image);
        return $image_data;
    }
}
// ======================================================================

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

if (!isset($_GET['id'])) { die("ID Pesanan tidak ditemukan."); }

$pesanan_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Query untuk mengambil semua data yang dibutuhkan untuk resi
$stmt = $koneksi->prepare("
    SELECT
        p.id AS pesanan_id, p.tanggal_pesanan, p.metode_pembayaran, p.total_dengan_kode, p.kurir, p.nomor_resi, p.catatan_pembeli,
        p.nama_penerima, p.no_telepon, p.alamat_lengkap, p.kecamatan, p.kota, p.provinsi, p.kode_pos,
        t.nama_toko, t.alamat_toko AS alamat_pengirim
    FROM pesanan p
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    JOIN produk pr ON dp.produk_id = pr.id
    JOIN toko t ON pr.toko_id = t.id
    WHERE p.id = ? AND t.user_id = ?
    GROUP BY p.id
");
$stmt->bind_param("ii", $pesanan_id, $user_id);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) { die("Pesanan tidak ditemukan atau Anda tidak memiliki akses."); }

// Ambil detail item dalam pesanan
$stmt_items = $koneksi->prepare("SELECT pr.nama_produk, dp.jumlah FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = ?");
$stmt_items->bind_param("i", $pesanan_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

// Ambil logo & nama website
$nama_website = 'ShopMax';
$logo_website = BASE_URL . '/uploads/logo/default_logo.png'; 
$result_pengaturan = mysqli_query($koneksi, "SELECT nama_pengaturan, nilai_pengaturan FROM pengaturan WHERE nama_pengaturan IN ('nama_website', 'website_logo')");
while ($row = mysqli_fetch_assoc($result_pengaturan)) {
    if ($row['nama_pengaturan'] == 'nama_website') { $nama_website = $row['nilai_pengaturan']; }
    if ($row['nama_pengaturan'] == 'website_logo') { $logo_website = BASE_URL . $row['nilai_pengaturan']; }
}

// LOGIKA BARCODE DINAMIS
$barcode_text = !empty($pesanan['nomor_resi']) ? $pesanan['nomor_resi'] : (string)$pesanan['pesanan_id'];
$generator = new Barcode128();
$barcode_image_data = $generator->generate($barcode_text);
$barcode_image = $barcode_image_data ? base64_encode($barcode_image_data) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label #<?php echo $pesanan['pesanan_id']; ?></title>
    <link href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f0f2f5; }
        .page { width: 105mm; height: 148mm; padding: 5mm; margin: 10mm auto; border: 1px #D3D3D3 solid; border-radius: 5px; background: white; box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); box-sizing: border-box; display: flex; flex-direction: column; }
        .header, .footer { text-align: center; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 5px; }
        .header img { max-height: 40px; max-width: 120px; object-fit: contain; }
        .barcode { text-align: center; padding: 5px 0; }
        .barcode img { height: 60px; max-width: 100%; }
        .details { display: flex; justify-content: space-between; border-bottom: 1px dashed #000; padding: 5px 0; font-size: 12px; }
        .details .sender, .details .receiver { width: 50%; }
        .details .sender { border-right: 1px dashed #000; padding-right: 10px; }
        .details .receiver { padding-left: 10px; }
        .details h5 { font-size: 14px; font-weight: bold; margin-top: 0; margin-bottom: 5px; }
        .details p { margin: 0; line-height: 1.4; word-wrap: break-word; }
        .order-info { display: flex; justify-content: space-between; font-size: 12px; padding: 5px 0; border-bottom: 2px solid #000; }
        .order-info div { font-weight: bold; }
        .order-items { flex-grow: 1; padding-top: 5px; font-size: 11px; }
        .order-items ol { padding-left: 20px; margin: 0; }
        .notes { font-size: 11px; border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; word-wrap: break-word;}
        .footer { font-size: 10px; color: #777; margin-top: auto; }
        .no-print { text-align: center; margin: 20px; }
        @media print {
            .no-print { display: none; }
            body, .page { margin: 0; box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Cetak Label</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="page">
        <div class="header">
            <img src="<?php echo htmlspecialchars($logo_website); ?>" alt="Logo">
            <h4><?php echo strtoupper(htmlspecialchars($pesanan['kurir'] ?? 'EKSPEDISI')); ?></h4>
        </div>

        <div class="barcode">
            <?php if ($barcode_image): ?>
                <img src="data:image/png;base64,<?php echo $barcode_image; ?>" alt="barcode">
            <?php endif; ?>
            <p style="letter-spacing: 2px; margin: 0; font-weight: bold; font-size: 14px;"><?php echo htmlspecialchars($barcode_text); ?></p>
        </div>

        <div class="details">
            <div class="sender">
                <h5>Pengirim:</h5>
                <p><strong><?php echo htmlspecialchars($pesanan['nama_toko']); ?></strong></p>
                <p><?php echo htmlspecialchars($pesanan['alamat_pengirim'] ?? 'Alamat belum diatur'); ?></p>
            </div>
            <div class="receiver">
                <h5>Penerima:</h5>
                <p><strong><?php echo htmlspecialchars($pesanan['nama_penerima']); ?></strong></p>
                <p><?php echo htmlspecialchars($pesanan['no_telepon']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($pesanan['alamat_lengkap'])); ?></p>
                <p><?php echo htmlspecialchars($pesanan['kecamatan']); ?>, <?php echo htmlspecialchars($pesanan['kota']); ?>, <?php echo htmlspecialchars($pesanan['provinsi']); ?> <?php echo htmlspecialchars($pesanan['kode_pos']); ?></p>
            </div>
        </div>

        <div class="order-info">
            <div><strong>No. Pesanan:</strong> #<?php echo htmlspecialchars($pesanan['pesanan_id']); ?></div>
            <?php if ($pesanan['metode_pembayaran'] === 'COD'): ?>
                <div><strong style="font-size: 14px;">COD: Rp <?php echo number_format($pesanan['total_dengan_kode'], 0, ',', '.'); ?></strong></div>
            <?php else: ?>
                 <div><strong>NON-COD</strong></div>
            <?php endif; ?>
        </div>

        <div class="order-items">
            <strong>Isi Paket:</strong>
            <ol>
                <?php while($item = $items_result->fetch_assoc()): ?>
                    <li>(<?php echo $item['jumlah']; ?>x) <?php echo htmlspecialchars($item['nama_produk']); ?></li>
                <?php endwhile; ?>
            </ol>
        </div>

        <?php if (!empty($pesanan['catatan_pembeli'])): ?>
        <div class="notes">
            <strong>Catatan Pembeli:</strong>
            <p><?php echo nl2br(htmlspecialchars($pesanan['catatan_pembeli'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="footer">
            Terima kasih telah berbelanja di <?php echo htmlspecialchars($nama_website); ?>
        </div>
    </div>
</body>
</html>