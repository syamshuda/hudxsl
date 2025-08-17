<?php
// /includes/footer.php (Versi Final dengan 2 Footer dan Perbaikan JS)
?>
</main> 
<footer class="main-footer">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>Tentang Sabaku ID</h5>
                <ul class="list-unstyled">
                    <li><a href="/tentang_kami.php">Tentang Kami</a></li>
                    <li><a href="/kontak.php">Kontak</a></li>
                    <li><a href="/kebijakan_pengembalian.php">Kebijakan Pengembalian</a></li>
                    <li><a href="/faq.php">FAQ</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Ikuti Kami</h5>
                <p>Dapatkan info terbaru mengenai produk dan promo kami.</p>
                <div>
                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-tiktok"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Kontak</h5>
                <p class="mb-1"><strong>SMKN 1 Bangkalan</strong></p>
                <p class="mb-1">Jl. HOS. Cokroaminoto No. 1, Pangeranan, Bangkalan</p>
                <p class="mb-1">Email: officialsabaku@gmail.com</p>
            </div>
        </div>
        <hr>
        <div class="copyright-section">
            <img src="/assets/img/logo_sabaku.png" alt="Logo Sabaku ID" class="copyright-logo">
            <img src="/assets/img/logo_smkn1.png" alt="Logo SMKN 1 Bangkalan" class="copyright-logo">
            <p class="mb-0">&copy; 2025 Sabaku.ID – SMKN 1 Bangkalan</p>
        </div>
    </div>
</footer>

<footer class="mobile-nav-footer">
    <a href="/" class="mobile-nav-item">
        <span>🏠</span><br>Beranda
    </a>
    <a href="/trending.php" class="mobile-nav-item">
        <span>🔥</span><br>Trending
    </a>
    <a href="/notifikasi.php" class="mobile-nav-item">
        <span>🔔</span><br>Notifikasi
    </a>
    <a href="/saya.php" class="mobile-nav-item">
        <span>👤</span><br>Saya
    </a>
</footer>

<style>
    /* Footer Utama (yang berisi info kontak) */
    .main-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #e7e7e7;
        color: #555;
    }
    .main-footer h5 { font-weight: bold; }
    .main-footer a { text-decoration: none; color: #555; }
    .social-icon { font-size: 1.5rem; margin-right: 1rem; }
    .copyright-section { display: flex; justify-content: center; align-items: center; }
    .copyright-logo { height: 30px; width: auto; margin: 0 5px; }

    /* Pengaturan Responsif */
    @media (min-width: 768px) {
        /* Di layar besar (desktop), sembunyikan navigasi mobile */
        .mobile-nav-footer {
            display: none;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>