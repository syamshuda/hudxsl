<?php
// /toko.php (Versi Final dengan SEMUA Fitur Lengkap dan Fungsional)
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /index.php");
    exit();
}

$toko_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'] ?? 0;

// Ambil data toko, cek apakah user ini sudah mengikuti
$stmt_toko = $koneksi->prepare("
    SELECT t.*, u.id as user_id_penjual,
           (SELECT COUNT(*) FROM pengikut_toko WHERE toko_id = t.id AND user_id = ?) as sudah_mengikuti
    FROM toko t 
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ? AND t.is_active = 1
");
$stmt_toko->bind_param("ii", $user_id, $toko_id);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();
if ($result_toko->num_rows === 0) {
    $page_title = "Toko Tidak Ditemukan";
    require_once 'includes/header.php';
    echo "<div class='container my-5'><div class='alert alert-danger'>Toko tidak ditemukan atau sedang tidak aktif.</div></div>";
    require_once 'includes/footer.php';
    exit();
}
$toko = $result_toko->fetch_assoc();
$stmt_toko->close();

// Ambil voucher aktif milik toko ini
$stmt_voucher = $koneksi->prepare("SELECT v.*, (SELECT COUNT(*) FROM klaim_voucher WHERE voucher_id = v.id AND user_id = ?) as sudah_diklaim FROM voucher v WHERE v.toko_id = ? AND v.is_active = 1 AND v.tanggal_akhir > NOW() AND (v.jumlah_penggunaan_total IS NULL OR v.jumlah_digunakan_saat_ini < v.jumlah_penggunaan_total)");
$stmt_voucher->bind_param("ii", $user_id, $toko_id);
$stmt_voucher->execute();
$result_voucher = $stmt_voucher->get_result();

$page_title = htmlspecialchars($toko['nama_toko']);
require_once 'includes/header.php';
?>

<style>
    /* CSS Styling untuk halaman toko */
    #notification-container { position: fixed; top: 80px; right: 20px; z-index: 1055; width: 300px; }
    .store-header-container { position: relative; color: white; text-shadow: 1px 1px 3px rgba(0,0,0,0.7); border-radius: 0.5rem; overflow: hidden; margin-bottom: 1rem; }
    .store-banner { width: 100%; height: 200px; object-fit: cover; display: block; }
    .store-info-overlay { position: absolute; bottom: 0; left: 0; width: 100%; padding: 1rem; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); }
    .store-logo { width: 80px; height: 80px; border-radius: 50%; border: 3px solid white; object-fit: cover; box-shadow: 0 2px 4px rgba(0,0,0,0.5); }
    .voucher-card { border-left: 5px solid #EE4D2D; background-color: #fff8f6; }
    .product-card .product-info { display: flex; flex-direction: column; flex-grow: 1; padding: 0.5rem; }
    .product-card .product-price-section { margin-top: auto; }
</style>

<div id="notification-container"></div>

<div class="store-header-container">
    <img src="/uploads/banners/<?php echo htmlspecialchars($toko['banner_toko'] ?? 'default_banner.jpg'); ?>" class="store-banner" alt="Banner Toko">
    <div class="store-info-overlay d-flex align-items-center">
        <img src="/uploads/logo_toko/<?php echo htmlspecialchars($toko['logo_toko'] ?? 'default_logo.png'); ?>" class="store-logo me-3">
        <div class="flex-grow-1">
            <h3 class="mb-0"><?php echo htmlspecialchars($toko['nama_toko']); ?></h3>
            <p class="mb-0">
                <small>
                    <i class="bi bi-star-fill text-warning"></i> <?php echo number_format($toko['rating_toko'], 1); ?> | 
                    <span id="follower-count"><?php echo number_format($toko['jumlah_pengikut']); ?></span> Pengikut
                </small>
            </p>
        </div>
        <div>
            <button class="btn <?php echo $toko['sudah_mengikuti'] ? 'btn-light' : 'btn-outline-light'; ?>" id="btn-follow" data-toko-id="<?php echo $toko['id']; ?>">
                <?php echo $toko['sudah_mengikuti'] ? 'Mengikuti' : 'Ikuti'; ?>
            </button>
            <a href="/pesan.php?chat_with=<?php echo $toko['user_id_penjual']; ?>" class="btn btn-light"><i class="bi bi-chat-dots-fill"></i> Chat</a>
        </div>
    </div>
</div>

<ul class="nav nav-tabs nav-fill mb-4">
  <li class="nav-item"><a class="nav-link active" href="#">Toko</a></li>
  <li class="nav-item"><a class="nav-link" href="toko_produk.php?id=<?php echo $toko_id; ?>">Produk</a></li>
  <li class="nav-item"><a class="nav-link" href="toko_produk.php?id=<?php echo $toko_id; ?>&tab=kategori">Kategori</a></li>
</ul>

<?php if ($result_voucher->num_rows > 0): ?>
<h4 class="mb-3">Voucher Toko</h4>
<div class="row g-3">
    <?php while($voucher = $result_voucher->fetch_assoc()): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card voucher-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <?php 
                        $nilai_voucher = (float)$voucher['nilai'];
                        $teks_voucher = '';
                        if ($voucher['jenis_voucher'] == 'diskon') {
                            $teks_voucher = 'Diskon ' . $nilai_voucher . '%';
                        } elseif ($voucher['jenis_voucher'] == 'cashback') {
                            $teks_voucher = 'Cashback Rp' . number_format($nilai_voucher);
                        } elseif ($voucher['jenis_voucher'] == 'gratis_ongkir') {
                            $teks_voucher = 'Gratis Ongkir s/d Rp' . number_format($nilai_voucher);
                        }
                    ?>
                    <h6 class="card-title text-danger"><?php echo $teks_voucher; ?></h6>
                    <p class="card-text mb-0"><small>Min. Blj Rp<?php echo number_format($voucher['min_pembelian'] ?? 0); ?></small></p>
                    <p class="card-text text-muted"><small>Hingga <?php echo date('d M Y', strtotime($voucher['tanggal_akhir'])); ?></small></p>
                </div>
                <?php if ($voucher['sudah_diklaim'] > 0): ?>
                    <button class="btn btn-secondary" disabled>Terklaim</button>
                <?php else: ?>
                    <button class="btn btn-danger btn-klaim-voucher" data-voucher-id="<?php echo $voucher['id']; ?>">Klaim</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<hr class="my-4">
<?php endif; ?>

<h4 class="mb-3">Produk Unggulan</h4>
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
    <?php
    $stmt_produk = $koneksi->prepare("SELECT p.*, (SELECT AVG(rating) FROM ulasan WHERE produk_id = p.id) as avg_rating, (SELECT SUM(jumlah) FROM detail_pesanan dp JOIN pesanan ps ON dp.pesanan_id = ps.id WHERE dp.produk_id = p.id AND ps.status_pesanan = 'selesai') as total_terjual FROM produk p WHERE p.toko_id = ? AND p.status_moderasi = 'disetujui' ORDER BY total_terjual DESC LIMIT 8");
    $stmt_produk->bind_param("i", $toko_id);
    $stmt_produk->execute();
    $result_produk = $stmt_produk->get_result();
    if ($result_produk->num_rows > 0):
        while ($produk = $result_produk->fetch_assoc()):
            $promo_data = getEffectivePriceAndPromoStatus($produk);
    ?>
        <div class="col">
            <div class="card h-100 shadow-sm">
                <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                    <img src="/uploads/produk/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" style="aspect-ratio: 1 / 1; object-fit: cover;">
                    <div class="product-info">
                        <p class="product-name small"><?php echo htmlspecialchars(substr($produk['nama_produk'], 0, 50)); ?></p>
                        <div class="product-price-section">
                            <?php if ($promo_data['is_promo']): ?>
                                <p class="product-price mb-0 text-danger fw-bold">Rp <?php echo number_format($promo_data['price']); ?></p>
                                <p class="product-price-old text-muted small" style="line-height: 1;"><del>Rp <?php echo number_format($promo_data['harga_normal']); ?></del></p>
                            <?php else: ?>
                                <p class="product-price fw-bold">Rp <?php echo number_format($promo_data['price']); ?></p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.8rem;">
                                <span><?php if ($produk['avg_rating']): ?><i class="bi bi-star-fill text-warning"></i> <?php echo round($produk['avg_rating'], 1); ?><?php endif; ?></span>
                                <span><?php if ($produk['total_terjual']): ?><?php echo $produk['total_terjual']; ?> terjual<?php endif; ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    <?php
        endwhile;
    else:
        echo "<div class='col-12'><div class='alert alert-info'>Toko ini belum memiliki produk.</div></div>";
    endif;
    $stmt_produk->close();
    ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // === JAVASCRIPT UNTUK KLAIM VOUCHER ===
    $('.btn-klaim-voucher').on('click', function() {
        const button = $(this);
        const voucherId = button.data('voucher-id');
        button.prop('disabled', true).text('Memproses...');
        $.ajax({
            url: 'api_klaim_voucher.php',
            type: 'POST',
            data: { voucher_id: voucherId },
            dataType: 'json',
            success: function(response) {
                let alertType = response.success ? 'success' : 'danger';
                let notification = `<div class="alert alert-${alertType} alert-dismissible fade show" role="alert">${response.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                $('#notification-container').html(notification);
                if (response.success) {
                    button.removeClass('btn-danger').addClass('btn-secondary').text('Terklaim');
                } else {
                    button.prop('disabled', false).text('Klaim');
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                button.prop('disabled', false).text('Klaim');
            }
        });
    });

    // === JAVASCRIPT UNTUK IKUTI TOKO ===
    $('#btn-follow').on('click', function() {
        const button = $(this);
        const tokoId = button.data('toko-id');
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        $.ajax({
            url: 'api_ikuti_toko.php',
            type: 'POST',
            data: { toko_id: tokoId },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#follower-count').text(response.follower_count.toLocaleString('id-ID'));
                    if(response.is_following) {
                        button.removeClass('btn-outline-light').addClass('btn-light').text('Mengikuti');
                    } else {
                        button.removeClass('btn-light').addClass('btn-outline-light').text('Ikuti');
                    }
                } else {
                    alert(response.message);
                    button.text(button.hasClass('btn-light') ? 'Mengikuti' : 'Ikuti');
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                button.text(button.hasClass('btn-light') ? 'Mengikuti' : 'Ikuti');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>