<?php
// /toko_produk.php (Halaman untuk menampilkan semua produk & kategori toko)
$page_title = "Produk Toko"; // Judul default
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /index.php");
    exit();
}

$toko_id = (int)$_GET['id'];
$active_tab = isset($_GET['tab']) && $_GET['tab'] == 'kategori' ? 'kategori' : 'produk';

// Ambil data toko untuk header
$stmt_toko = $koneksi->prepare("SELECT nama_toko FROM toko WHERE id = ? AND is_active = 1");
$stmt_toko->bind_param("i", $toko_id);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();
if ($result_toko->num_rows === 0) {
    $page_title = "Toko Tidak Ditemukan";
    require_once 'includes/header.php';
    echo "<div class='container my-5'><div class='alert alert-danger'>Toko tidak ditemukan.</div></div>";
    require_once 'includes/footer.php';
    exit();
}
$toko = $result_toko->fetch_assoc();
$page_title = ($active_tab == 'produk' ? "Semua Produk" : "Kategori") . " - " . htmlspecialchars($toko['nama_toko']);
$stmt_toko->close();
require_once 'includes/header.php';
?>

<style>
    /* Menambahkan style untuk product-card agar konsisten */
    .product-card .product-info { display: flex; flex-direction: column; flex-grow: 1; padding: 0.5rem; }
    .product-card .product-price-section { margin-top: auto; }
</style>

<a href="toko.php?id=<?php echo $toko_id; ?>" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali ke Halaman Utama Toko</a>
<h3 class="mb-3"><?php echo htmlspecialchars($toko['nama_toko']); ?></h3>

<ul class="nav nav-tabs nav-fill mb-4">
  <li class="nav-item"><a class="nav-link" href="toko.php?id=<?php echo $toko_id; ?>">Toko</a></li>
  <li class="nav-item"><a class="nav-link <?php if($active_tab == 'produk') echo 'active'; ?>" href="toko_produk.php?id=<?php echo $toko_id; ?>">Produk</a></li>
  <li class="nav-item"><a class="nav-link <?php if($active_tab == 'kategori') echo 'active'; ?>" href="toko_produk.php?id=<?php echo $toko_id; ?>&tab=kategori">Kategori</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade <?php if($active_tab == 'produk') echo 'show active'; ?>" id="produk">
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            <?php
            $stmt_produk = $koneksi->prepare("SELECT p.*, (SELECT AVG(rating) FROM ulasan WHERE produk_id = p.id) as avg_rating, (SELECT SUM(jumlah) FROM detail_pesanan dp JOIN pesanan ps ON dp.pesanan_id = ps.id WHERE dp.produk_id = p.id AND ps.status_pesanan = 'selesai') as total_terjual FROM produk p WHERE p.toko_id = ? AND p.status_moderasi = 'disetujui' ORDER BY p.created_at DESC");
            $stmt_produk->bind_param("i", $toko_id);
            $stmt_produk->execute();
            $result_produk = $stmt_produk->get_result();
            if ($result_produk->num_rows > 0):
                while ($produk = $result_produk->fetch_assoc()):
                    $promo_data = getEffectivePriceAndPromoStatus($produk);
            ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" class="text-decoration-none text-dark">
                            <img src="/uploads/produk/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" style="aspect-ratio: 1 / 1; object-fit: cover;">
                            <div class="product-info">
                                <p class="product-name small"><?php echo htmlspecialchars(substr($produk['nama_produk'], 0, 50)); ?></p>
                                <div class="product-price-section">
                                    <?php if ($promo_data['is_promo']): ?>
                                        <p class="product-price mb-0 text-danger fw-bold">Rp <?php echo number_format($promo_data['price']); ?></p>
                                        <p class="product-price-old text-muted small"><del>Rp <?php echo number_format($promo_data['harga_normal']); ?></del></p>
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
    </div>
    
    <div class="tab-pane fade <?php if($active_tab == 'kategori') echo 'show active'; ?>" id="kategori">
         <div class="list-group">
            <?php
            $stmt_kat = $koneksi->prepare("SELECT DISTINCT k.id, k.nama_kategori FROM kategori k JOIN produk p ON k.id = p.kategori_id WHERE p.toko_id = ? ORDER BY k.nama_kategori ASC");
            $stmt_kat->bind_param("i", $toko_id);
            $stmt_kat->execute();
            $result_kat = $stmt_kat->get_result();
            if ($result_kat->num_rows > 0):
                while ($kat = $result_kat->fetch_assoc()):
            ?>
                <a href="kategori.php?id=<?php echo $kat['id']; ?>" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                </a>
            <?php
                endwhile;
            else:
                echo "<div class='alert alert-info'>Toko ini belum mengkategorikan produknya.</div>";
            endif;
            $stmt_kat->close();
            ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>