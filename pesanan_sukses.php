<?php
// /pesanan_sukses.php (Versi Final dengan Rincian Lengkap & Voucher)
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

$page_title = "Pesanan Berhasil Dibuat";
require_once 'includes/header.php';

$pesanan_id = isset($_GET['id']) ? (int)$_GET['id'] : ($_SESSION['last_pesanan_id'] ?? 0);

if ($pesanan_id === 0) {
    echo "<div class='container my-5'><div class='alert alert-danger'>ID Pesanan tidak valid atau sesi telah berakhir.</div></div>";
    require_once 'includes/footer.php';
    exit();
}

// Ambil semua data pesanan dari database
$stmt_pesanan = $koneksi->prepare("SELECT * FROM pesanan WHERE id = ? AND pembeli_id = ?");
$stmt_pesanan->bind_param("ii", $pesanan_id, $_SESSION['user_id']);
$stmt_pesanan->execute();
$result_pesanan = $stmt_pesanan->get_result();
$pesanan = $result_pesanan->fetch_assoc();
$stmt_pesanan->close();

if (!$pesanan) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Pesanan tidak ditemukan.</div></div>";
    require_once 'includes/footer.php';
    exit();
}

// Hitung subtotal produk dari tabel detail_pesanan untuk ditampilkan
$subtotal_produk = 0;
$query_items = "SELECT SUM(jumlah * harga_satuan) as total FROM detail_pesanan WHERE pesanan_id = ?";
$stmt_items = $koneksi->prepare($query_items);
$stmt_items->bind_param("i", $pesanan_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result()->fetch_assoc();
$subtotal_produk = $result_items['total'] ?? 0;
$stmt_items->close();

// Ambil pengaturan pembayaran (Bank & QRIS) dari database
$pengaturan_pembayaran = [];
$result_pengaturan = mysqli_query($koneksi, "SELECT nama_pengaturan, nilai_pengaturan FROM pengaturan WHERE nama_pengaturan IN ('nama_bank', 'nomor_rekening', 'nama_pemilik_rekening', 'qris_image')");
while ($row = mysqli_fetch_assoc($result_pengaturan)) {
    $pengaturan_pembayaran[$row['nama_pengaturan']] = $row['nilai_pengaturan'];
}

$total_final = $pesanan['total_dengan_kode'];
?>

<div class="container my-5">
    <div class="card shadow-sm text-center">
        <div class="card-body p-4">

            <?php // --- KONDISIONAL TAMPILAN BERDASARKAN METODE PEMBAYARAN --- ?>

            <?php if ($pesanan['metode_pembayaran'] === 'COD'): ?>
                
                <h2 class="card-title text-success mb-3">Pesanan Anda Akan Segera Diproses!</h2>
                <p class="card-text lead">Terima kasih telah memilih metode pembayaran di tempat (COD).</p>
                <p class="card-text">Nomor Pesanan Anda: <strong class="text-primary fs-4">#<?php echo $pesanan_id; ?></strong></p>

                <div class="alert alert-success mt-4">
                    <h5 class="alert-heading"><i class="bi bi-cash-coin"></i> Siapkan Uang Tunai</h5>
                    <p>Penjual telah menerima pesanan Anda dan akan segera mengemasnya. Mohon siapkan uang tunai pas sejumlah:</p>
                    
                    <h4 class="text-success fw-bold my-3">
                        Rp <?php echo number_format($total_final, 0, ',', '.'); ?>
                    </h4>
                    
                    <p class="mb-0">Uang akan ditagihkan oleh kurir saat paket tiba di alamat Anda.</p>
                </div>

                <p class="mt-4">Anda dapat memantau status pesanan Anda di halaman "Pesanan Saya".</p>
                <a href="<?php echo BASE_URL; ?>/pesanan_saya.php?status=diproses" class="btn btn-primary btn-lg">Lihat Status Pesanan</a>

            <?php else: // Tampilan untuk Transfer Bank (Default) ?>
            
                <h2 class="card-title text-success mb-3">Pesanan Berhasil Dibuat!</h2>
                <p class="card-text lead">Terima kasih telah berbelanja di <?php echo htmlspecialchars($nama_website); ?>.</p>
                <p class="card-text">Nomor Pesanan Anda: <strong class="text-primary fs-4">#<?php echo $pesanan_id; ?></strong></p>

                <div class="alert alert-info mt-4">
                    <h5 class="alert-heading">Informasi Pembayaran</h5>
                    <p>Silakan lakukan pembayaran melalui salah satu metode di bawah ini:</p>
                    
                    <div class="row justify-content-center g-4">
                        <div class="col-md-6 text-start">
                            <h6>Transfer Bank</h6>
                            <ul class="list-unstyled mx-auto" style="max-width: 300px;">
                                <li><strong>Bank:</strong> <?php echo htmlspecialchars($pengaturan_pembayaran['nama_bank'] ?? 'N/A'); ?></li>
                                <li><strong>No. Rekening:</strong> <?php echo htmlspecialchars($pengaturan_pembayaran['nomor_rekening'] ?? 'N/A'); ?></li>
                                <li><strong>Atas Nama:</strong> <?php echo htmlspecialchars($pengaturan_pembayaran['nama_pemilik_rekening'] ?? 'N/A'); ?></li>
                            </ul>
                        </div>
                        <?php if (!empty($pengaturan_pembayaran['qris_image'])): ?>
                        <div class="col-md-6">
                            <h6>Scan QRIS</h6>
                            <img src="<?php echo BASE_URL . htmlspecialchars($pengaturan_pembayaran['qris_image']); ?>" alt="QRIS" class="img-fluid rounded" style="max-width: 180px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    <hr>
                    
                    <h6 class="text-start">Rincian Pembayaran:</h6>
                    <div class="text-start w-100 mx-auto" style="max-width: 400px;">
                        <div class="d-flex justify-content-between"><span>Subtotal Produk</span><span>Rp <?php echo number_format($subtotal_produk, 0, ',', '.'); ?></span></div>
                        <?php if ($pesanan['biaya_ongkir'] > 0): ?>
                        <div class="d-flex justify-content-between"><span>Biaya Ongkir</span><span>+ Rp <?php echo number_format($pesanan['biaya_ongkir'], 0, ',', '.'); ?></span></div>
                        <?php endif; ?>
                        <?php if ($pesanan['nilai_diskon_voucher'] > 0): ?>
                        <div class="d-flex justify-content-between text-success"><span>Diskon Voucher (<small><?php echo htmlspecialchars($pesanan['voucher_kode_digunakan']); ?></small>)</span><span>- Rp <?php echo number_format($pesanan['nilai_diskon_voucher'], 0, ',', '.'); ?></span></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between"><span>Biaya Verifikasi (Kode Unik)</span><span>+ Rp <?php echo number_format($pesanan['kode_unik'], 0, ',', '.'); ?></span></div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-bold"><span>Total Transfer</span><span>Rp <?php echo number_format($total_final, 0, ',', '.'); ?></span></div>
                    </div>
                    
                    <h4 class="text-primary fw-bold my-3">
                        Rp <span id="jumlahBayar"><?php echo number_format($total_final, 0, ',', '.'); ?></span>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?php echo $total_final; ?>')">Salin</button>
                    </h4>
                    <p class="fw-bold text-danger">PENTING: Mohon transfer dengan jumlah yang tepat untuk verifikasi otomatis.</p>
                </div>

                <p class="mt-4">Setelah transfer, segera konfirmasi pembayaran Anda melalui:</p>
                <a href="<?php echo BASE_URL; ?>/detail_pesanan.php?id=<?php echo $pesanan_id; ?>" class="btn btn-primary btn-lg">Konfirmasi Pembayaran</a>

            <?php endif; ?>
            <?php // --- AKHIR DARI KONDISIONAL --- ?>

        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert("Jumlah pembayaran " + text + " berhasil disalin.");
    }, function(err) {
        alert('Gagal menyalin teks.');
    });
}
</script>

<?php
if (isset($_SESSION['last_pesanan_id'])) {
    unset($_SESSION['last_pesanan_id']);
}
require_once 'includes/footer.php';
?>