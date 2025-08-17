<?php
// /wishlist.php
$page_title = "Wishlist Saya";
require_once 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: /auth/login.php");
    exit();
}
$pembeli_id = $_SESSION['user_id'];

$query = "
    SELECT p.* FROM produk p
    JOIN wishlist w ON p.id = w.produk_id
    WHERE w.pembeli_id = ?
";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $pembeli_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1>Wishlist Saya</h1>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
    <?php if($result->num_rows > 0): ?>
        <?php while($produk = $result->fetch_assoc()): ?>
             <div class="col">
                <div class="card h-100 shadow-sm">
                    <a href="detail_produk.php?id=<?php echo $produk['id']; ?>">
                        <img src="/uploads/produk/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" style="height: 200px; object-fit: cover;">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($produk['nama_produk']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-danger">Rp <?php echo number_format($produk['harga']); ?></h6>
                    </div>
                    <div class="card-footer bg-white">
                         <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" class="btn btn-primary btn-sm">Lihat</a>
                         <a href="hapus_wishlist.php?id=<?php echo $produk['id']; ?>" class="btn btn-outline-danger btn-sm">Hapus</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12"><div class="alert alert-info">Wishlist Anda masih kosong.</div></div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>