<?php
// /trending.php
$page_title = "Produk Trending";
require_once 'includes/header.php';
require_once 'includes/functions.php'; // Panggil file fungsi terpusat

$query_produk = "
    SELECT 
            p.*, 
                    SUM(dp.jumlah) as total_terjual
                        FROM 
                                produk p
                                    JOIN 
                                            detail_pesanan dp ON p.id = dp.produk_id
                                                JOIN
                                                        toko t ON p.toko_id = t.id
                                                            WHERE 
                                                                    p.status_moderasi = 'disetujui' AND t.is_active = 1
                                                                        GROUP BY 
                                                                                p.id
                                                                                    ORDER BY 
                                                                                            total_terjual DESC
                                                                                                LIMIT 20
                                                                                                ";

                                                                                                $result_produk = mysqli_query($koneksi, $query_produk);

                                                                                                if ($result_produk && mysqli_num_rows($result_produk) > 0):
                                                                                                    while ($produk = mysqli_fetch_assoc($result_produk)):
                                                                                                            $promo_data = getEffectivePriceAndPromoStatus($produk); // Fungsi ini dari functions.php
                                                                                                            ?>
                                                                                                            <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" class="product-card" style="text-decoration: none; color: inherit;">
                                                                                                                <img src="/uploads/produk/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                                                                                                                    <div class="product-info">
                                                                                                                            <p class="product-name"><?php echo htmlspecialchars(substr($produk['nama_produk'], 0, 50)); ?></p>
                                                                                                                                    <?php if ($promo_data['is_promo'] || $promo_data['promo_text']): ?>
                                                                                                                                                <p class="product-price mb-0 text-danger fw-bold">Rp <?php echo number_format($promo_data['price'], 0, ',', '.'); ?></p>
                                                                                                                                                            <p class="product-price-old mb-1 text-muted"><del>Rp <?php echo number_format($promo_data['harga_normal'], 0, ',', '.'); ?></del></p>
                                                                                                                                                                        <?php if ($promo_data['promo_text']): ?>
                                                                                                                                                                                        <span class="badge <?php echo $promo_data['promo_badge_class']; ?>"><?php echo $promo_data['promo_text']; ?></span>
                                                                                                                                                                                                    <?php endif; ?>
                                                                                                                                                                                                            <?php else: ?>
                                                                                                                                                                                                                        <p class="product-price">Rp <?php echo number_format($promo_data['price'], 0, ',', '.'); ?></p>
                                                                                                                                                                                                                                <?php endif; ?>
                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                    </a>
                                                                                                                                                                                                                                    <?php 
                                                                                                                                                                                                                                        endwhile;
                                                                                                                                                                                                                                        else:
                                                                                                                                                                                                                                        ?>
                                                                                                                                                                                                                                        <div class="col-12">
                                                                                                                                                                                                                                            <div class="alert alert-info">Belum ada produk yang terjual untuk ditampilkan di sini.</div>
                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                            <?php
                                                                                                                                                                                                                                            endif;
                                                                                                                                                                                                                                            ?>
                                                                                                                                                                                                                                            </div>

                                                                                                                                                                                                                                            <?php
                                                                                                                                                                                                                                            require_once 'includes/footer.php';
                                                                                                                                                                                                                                            ?>