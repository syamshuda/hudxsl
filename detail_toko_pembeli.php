<?php
// /detail_toko_pembeli.php
$page_title = "Etalase Toko";
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$toko_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'] ?? null;

// Ambil data toko
$stmt_toko = $koneksi->prepare("SELECT * FROM toko WHERE id = ? AND is_active = 1");
$stmt_toko->bind_param("i", $toko_id);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();
if ($result_toko->num_rows === 0) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Toko tidak ditemukan atau sedang tidak aktif.</div></div>";
    require_once 'includes/footer.php';
    exit();
}
$toko = $result_toko->fetch_assoc();
$stmt_toko->close();

// Cek apakah pembeli sudah mengikuti toko ini
$is_followed = false;
if ($user_id) {
    $stmt_follow = $koneksi->prepare("SELECT id FROM pengikut_toko WHERE pembeli_id = ? AND toko_id = ?");
    $stmt_follow->bind_param("ii", $user_id, $toko_id);
    $stmt_follow->execute();
    if ($stmt_follow->get_result()->num_rows > 0) {
        $is_followed = true;
    }
    $stmt_follow->close();
}

// Ambil voucher toko yang masih aktif
$stmt_voucher = $koneksi->prepare("SELECT * FROM voucher WHERE toko_id = ? AND is_active = 1 AND tanggal_mulai <= NOW() AND tanggal_akhir >= NOW() ORDER BY nilai_diskon DESC");
$stmt_voucher->bind_param("i", $toko_id);
$stmt_voucher->execute();
$vouchers = $stmt_voucher->get_result();
$stmt_voucher->close();

// Ambil semua produk dari toko ini
$stmt_produk = $koneksi->prepare("SELECT id, nama_produk, harga, harga_diskon, promo_mulai, promo_akhir, gambar_produk FROM produk WHERE toko_id = ? AND status_moderasi = 'disetujui' ORDER BY created_at DESC");
$stmt_produk->bind_param("i", $toko_id);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
?>

<style>
    /* CSS untuk tata letak etalase toko */
    .store-header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .store-info {
        display: flex;
        align-items: center;
    }

    .store-logo {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid #e0e0e0;
        margin-right: 1rem;
    }

    .store-details h4 {
        margin-bottom: 0.25rem;
        font-weight: bold;
    }

    .store-details p {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .store-actions .btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }

    .voucher-card {
        border: 1px dashed #e0e0e0;
        border-radius: 8px;
        background-color: #f8f9fa;
    }

    .voucher-card h6 {
        color: #EE4D2D;
        font-weight: bold;
    }
</style>

<div class="store-header-container">
    <div class="store-info">
        <img src="/uploads/logo_toko/<?php echo htmlspecialchars($toko['logo_toko']); ?>" alt="Logo Toko" class="store-logo">
        <div class="store-details">
            <h4><?php echo htmlspecialchars($toko['nama_toko']); ?></h4>
            <p><?php echo htmlspecialchars($toko['deskripsi']); ?></p>
        </div>
    </div>
    <div class="store-actions">
        <?php if ($user_id): ?>
            <button id="follow-btn" class="btn btn-primary" data-toko-id="<?php echo $toko_id; ?>">
                <i class="bi bi-person-plus"></i> <?php echo $is_followed ? 'Diikuti' : 'Ikuti'; ?>
            </button>
            <a href="/pesan.php?chat_with=<?php echo $toko['user_id']; ?>" class="btn btn-outline-primary">
                <i class="bi bi-chat-dots"></i> Chat
            </a>
        <?php else: ?>
            <a href="/auth/login.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Ikuti</a>
            <a href="/auth/login.php" class="btn btn-outline-primary"><i class="bi bi-chat-dots"></i> Chat</a>
        <?php endif; ?>
    </div>
</div>

<hr>

<?php if ($vouchers->num_rows > 0): ?>
    <h4 class="mt-4">Voucher Toko</h4>
    <div class="row g-2 mb-4">
        <?php while($voucher = $vouchers->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card voucher-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Diskon <?php echo $voucher['nilai_diskon']; ?>%</h6>
                                <p class="small text-muted mb-0">Min. Blj Rp <?php echo number_format($voucher['min_pembelian']); ?></p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary klaim-voucher-btn" data-kode-voucher="<?php echo htmlspecialchars($voucher['kode']); ?>">Klaim</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<h4 class="mt-4">Etalase Toko</h4>
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php if ($result_produk->num_rows > 0): ?>
        <?php while ($produk = $result_produk->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <a href="detail_produk.php?id=<?php echo $produk['id']; ?>">
                        <img src="/uploads/produk/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" style="height: 200px; object-fit: cover;">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" class="text-decoration-none text-dark stretched-link">
                                <?php echo htmlspecialchars(substr($produk['nama_produk'], 0, 50)); ?>
                            </a>
                        </h5>
                        <h6 class="card-subtitle mb-2 text-danger fw-bold">
                            Rp <?php echo number_format($produk['harga']); ?>
                        </h6>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">Toko ini belum memiliki produk.</div>
        </div>
    <?php endif; ?>
</div>

<?php
mysqli_free_result($result_produk);
require_once 'includes/footer.php';
?>

<script>
$(document).ready(function() {
    // Logika untuk tombol "Ikuti"
    $('#follow-btn').on('click', function() {
        const btn = $(this);
        const tokoId = btn.data('toko-id');
        const isFollowed = btn.text().trim() === 'Diikuti';
        const action = isFollowed ? 'unfollow' : 'follow';
        
        $.ajax({
            url: 'proses_ikuti_toko.php',
            method: 'POST',
            data: { toko_id: tokoId, action: action },
            success: function(response) {
                if (response.success) {
                    if (action === 'follow') {
                        btn.html('<i class="bi bi-person-check"></i> Diikuti');
                    } else {
                        btn.html('<i class="bi bi-person-plus"></i> Ikuti');
                    }
                }
            }
        });
    });

    // Logika untuk tombol "Klaim Voucher"
    $('.klaim-voucher-btn').on('click', function() {
        const btn = $(this);
        const kodeVoucher = btn.data('kode-voucher');
        // Arahkan ke halaman voucher saya dengan kode yang sudah diisi
        window.location.href = `/saya.php?tab=voucher&kode=${kodeVoucher}`;
    });
});
</script>