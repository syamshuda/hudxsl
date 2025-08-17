<?php
$page_title = "Tulis Ulasan";
require_once 'includes/header.php';

// Proteksi & Validasi
if (!isset($_SESSION['user_id']) || !isset($_GET['produk_id']) || !isset($_GET['pesanan_id'])) {
    die("Akses tidak valid.");
}
$produk_id = (int)$_GET['produk_id'];
$pesanan_id = (int)$_GET['pesanan_id'];
$pembeli_id = $_SESSION['user_id'];

// Ambil info produk
$produk_res = mysqli_query($koneksi, "SELECT nama_produk, gambar_produk FROM produk WHERE id = $produk_id");
if(mysqli_num_rows($produk_res) == 0) die("Produk tidak ditemukan.");
$produk = mysqli_fetch_assoc($produk_res);
?>

<style>
/* CSS untuk Rating Bintang */
.rating { display: inline-block; unicode-bidi: bidi-override; direction: rtl; }
.rating > input { display: none; }
.rating > label { display: inline-block; padding: 0; margin: 0; position: relative; width: 1.1em; cursor: pointer; color: #ccc; font-size: 2.5rem; }
.rating > label:hover,
.rating > label:hover ~ label,
.rating > input:checked ~ label { color: #f9d71c; }
</style>

<h3>Beri Ulasan untuk Produk</h3>
<hr>
<div class="card mb-3" style="max-width: 540px;">
  <div class="row g-0">
    <div class="col-md-4">
      <img src="/uploads/produk/<?php echo $produk['gambar_produk']; ?>" class="img-fluid rounded-start">
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($produk['nama_produk']); ?></h5>
      </div>
    </div>
  </div>
</div>

<form action="proses_tulis_ulasan.php" method="POST">
    <input type="hidden" name="produk_id" value="<?php echo $produk_id; ?>">
    <input type="hidden" name="pesanan_id" value="<?php echo $pesanan_id; ?>">
    
    <div class="mb-3">
        <label class="form-label">Rating Anda:</label>
        <div class="rating">
            <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" title="5 stars">★</label>
            <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">★</label>
            <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">★</label>
            <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">★</label>
            <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">★</label>
        </div>
    </div>

    <div class="mb-3">
        <label for="komentar" class="form-label">Tulis Ulasan Anda:</label>
        <textarea name="komentar" id="komentar" class="form-control" rows="5" placeholder="Bagaimana kualitas produk ini?"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
</form>

<?php require_once 'includes/footer.php'; ?>