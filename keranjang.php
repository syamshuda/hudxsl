<?php
// /keranjang.php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Logika untuk menangani aksi 'Beli Sekarang' dari detail produk dan 'Hapus' dari halaman ini
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit();
    }
    
    $produk_id = (int)$_POST['produk_id'];
    
    if ($_POST['action'] === 'buy_now') {
        $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;
        if ($jumlah > 0) {
            $_SESSION['buy_now_item'] = [
                'produk_id' => $produk_id,
                'jumlah' => $jumlah
            ];
            unset($_SESSION['keranjang']); 
        }
        header("Location: " . BASE_URL . "/checkout.php");
        exit();
    }
        
    if ($_POST['action'] === 'remove') {
        unset($_SESSION['keranjang'][$produk_id]);
        header("Location: " . BASE_URL . "/keranjang.php");
        exit();
    }
}

// Hapus 'buy_now_item' jika pengguna mengunjungi keranjang secara langsung
if(isset($_SESSION['buy_now_item'])) {
    unset($_SESSION['buy_now_item']);
}

$page_title = "Keranjang Belanja";
require_once 'includes/header.php';
?>

<div class="d-flex align-items-center mb-4">
    <a href="<?php echo BASE_URL; ?>/" class="text-dark text-decoration-none fs-4 me-3">
        <i class="bi bi-arrow-left-circle"></i>
    </a>
    <h1>Keranjang Belanja Anda</h1>
</div>

<?php if (empty($_SESSION['keranjang'])): ?>
    <div class="alert alert-info text-center">
        <h4>Keranjang Anda masih kosong.</h4>
        <a href="<?php echo BASE_URL; ?>/produk.php" class="btn btn-primary mt-2">Mulai Belanja</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col" colspan="2">Produk</th>
                    <th scope="col">Harga Satuan</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col" class="text-end">Subtotal</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="cart-items-body">
                <?php
                $total_harga_seluruh_keranjang = 0;
                $produk_di_keranjang_data = [];
                $ids = implode(',', array_map('intval', array_keys($_SESSION['keranjang'])));
                
                // Tambahkan validasi jika keranjang tidak kosong tapi $ids kosong
                if (!empty($ids)) {
                    $query = "SELECT id, nama_produk, harga, harga_diskon, promo_mulai, promo_akhir, gambar_produk, toko_id, stok FROM produk WHERE id IN ($ids)";
                    $result = mysqli_query($koneksi, $query);
                    
                    while ($produk_db = mysqli_fetch_assoc($result)):
                        $produk_id = $produk_db['id'];
                        $jumlah_di_keranjang = $_SESSION['keranjang'][$produk_id];
                        
                        $promo_data = getEffectivePriceAndPromoStatus($produk_db);
                        $harga_efektif = $promo_data['price'];
                        
                        $subtotal_item = $harga_efektif * $jumlah_di_keranjang;
                        $total_harga_seluruh_keranjang += $subtotal_item;
                ?>
                <tr data-product-id="<?php echo $produk_id; ?>" data-stok="<?php echo $produk_db['stok']; ?>">
                    <td style="width: 100px;"><img src="<?php echo BASE_URL; ?>/uploads/produk/<?php echo htmlspecialchars($produk_db['gambar_produk']); ?>" class="img-fluid"></td>
                    <td>
                        <a href="detail_produk.php?id=<?php echo $produk_id; ?>" class="text-dark text-decoration-none fw-bold"><?php echo htmlspecialchars($produk_db['nama_produk']); ?></a>
                        <?php if ($promo_data['is_promo']): ?>
                            <br><span class="badge <?php echo $promo_data['promo_badge_class']; ?>"><?php echo $promo_data['promo_text']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($promo_data['is_promo']): ?>
                            Rp <span class="item-price"><?php echo number_format($harga_efektif); ?></span>
                            <br><del class="text-muted small">Rp <?php echo number_format($promo_data['harga_normal']); ?></del>
                        <?php else: ?>
                            Rp <span class="item-price"><?php echo number_format($harga_efektif); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <button class="btn btn-outline-secondary btn-decrease" type="button" data-product-id="<?php echo $produk_id; ?>">-</button>
                            <input type="text" class="form-control text-center item-quantity" value="<?php echo $jumlah_di_keranjang; ?>" readonly data-product-id="<?php echo $produk_id; ?>">
                            <button class="btn btn-outline-secondary btn-increase" type="button" data-product-id="<?php echo $produk_id; ?>">+</button>
                        </div>
                    </td>
                    <td class="text-end item-subtotal">Rp <?php echo number_format($subtotal_item); ?></td>
                    <td class="text-center">
                        <form action="<?php echo BASE_URL; ?>/keranjang.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="produk_id" value="<?php echo $produk_id; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                } // End if !empty($ids)
                ?>
            </tbody>
        </table>
    </div>
    
    <div class="row justify-content-end mt-3">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ringkasan Belanja</h5>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total Harga:</span>
                        <span id="total-cart-price">Rp <?php echo number_format($total_harga_seluruh_keranjang); ?></span>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="<?php echo BASE_URL; ?>/checkout.php" class="btn btn-success btn-lg">Lanjutkan ke Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    
    // Fungsi untuk memformat angka menjadi format Rupiah
    function formatRupiah(angka) {
        return 'Rp ' + angka.toLocaleString('id-ID');
    }
    
    // Fungsi untuk menangani update kuantitas
    function updateQuantity(productId, newQuantity) {
        const row = $(`tr[data-product-id="${productId}"]`);
        
        // Menambahkan efek loading sederhana
        row.css('opacity', '0.5');

        $.ajax({
            url: '<?php echo BASE_URL; ?>/proses_update_keranjang.php',
            type: 'POST',
            data: {
                produk_id: productId,
                jumlah: newQuantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update tampilan jika berhasil
                    row.find('.item-subtotal').text(formatRupiah(response.item_subtotal));
                    $('#total-cart-price').text(formatRupiah(response.total_cart_price));
                } else {
                    // Jika gagal, kembalikan kuantitas ke nilai semula dan beri tahu pengguna
                    alert(response.message);
                    row.find('.item-quantity').val(response.new_quantity > 0 ? response.new_quantity : 1);
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                // Kembalikan kuantitas jika AJAX gagal
                const currentVal = parseInt(row.find('.item-quantity').val());
                if (newQuantity > currentVal) {
                    row.find('.item-quantity').val(currentVal);
                } else {
                     row.find('.item-quantity').val(currentVal);
                }
            },
            complete: function() {
                // Hapus efek loading
                row.css('opacity', '1');
            }
        });
    }

    // Event handler untuk tombol TAMBAH (+)
    $('.btn-increase').on('click', function() {
        const button = $(this);
        const productId = button.data('product-id');
        const row = button.closest('tr');
        const stok = parseInt(row.data('stok'));
        const quantityInput = row.find('.item-quantity');
        let currentQuantity = parseInt(quantityInput.val());
        
        if (currentQuantity < stok) {
            currentQuantity++;
            quantityInput.val(currentQuantity);
            updateQuantity(productId, currentQuantity);
        } else {
            alert('Jumlah pembelian sudah mencapai batas stok produk.');
        }
    });

    // Event handler untuk tombol KURANG (-)
    $('.btn-decrease').on('click', function() {
        const button = $(this);
        const productId = button.data('product-id');
        const row = button.closest('tr');
        const quantityInput = row.find('.item-quantity');
        let currentQuantity = parseInt(quantityInput.val());

        if (currentQuantity > 1) {
            currentQuantity--;
            quantityInput.val(currentQuantity);
            updateQuantity(productId, currentQuantity);
        }
        // Jika kuantitas sudah 1, tidak melakukan apa-apa.
        // Pengguna harus menggunakan tombol Hapus.
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>