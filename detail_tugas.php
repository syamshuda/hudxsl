<?php
// /kurir/detail_tugas.php (Versi FINAL dengan perbaikan warning)
$page_title = "Detail Tugas Pengantaran";
require_once 'includes/header_kurir.php';

$kurir_id = $_SESSION['kurir_id'];
$pesanan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pesanan_id <= 0) {
    echo "<div class='alert alert-danger'>ID Pesanan tidak valid.</div>";
    require_once 'includes/footer_kurir.php';
    exit();
}

// Ambil detail pesanan, pastikan pesanan ini ditugaskan ke kurir yang sedang login
// dan gabungkan dengan info toko & user penjual
$stmt = $koneksi->prepare("
    SELECT p.*, 
           t.nama_toko, t.alamat_lengkap as alamat_toko, t.provinsi as provinsi_toko, t.kota as kota_toko, t.kecamatan as kecamatan_toko, t.kode_pos as kode_pos_toko,
           u_penjual.no_telepon as no_telepon_penjual
    FROM pesanan p
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    JOIN produk pr ON dp.produk_id = pr.id
    JOIN toko t ON pr.toko_id = t.id
    JOIN users u_penjual ON t.user_id = u_penjual.id
    WHERE p.id = ? AND p.kurir_lokal_id = ?
    GROUP BY p.id
");
$stmt->bind_param("ii", $pesanan_id, $kurir_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<div class='alert alert-danger'>Tugas tidak ditemukan atau bukan untuk Anda.</div>";
    require_once 'includes/footer_kurir.php';
    exit();
}
$pesanan = $result->fetch_assoc();
$stmt->close();
?>

<div class="d-flex align-items-center mb-3">
    <a href="index.php" class="btn btn-light me-3"><i class="bi bi-arrow-left"></i></a>
    <h4>Detail Tugas Pesanan #<?php echo $pesanan['id']; ?></h4>
</div>

<?php 
if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-box-arrow-up"></i> Informasi Pengambilan</h5>
            </div>
            <div class="card-body">
                <strong>Nama Toko:</strong><p><?php echo htmlspecialchars($pesanan['nama_toko']); ?></p>
                <strong>Alamat Toko:</strong>
                <p>
                    <?php echo htmlspecialchars($pesanan['alamat_toko'] ?? ''); ?><br>
                    <?php echo htmlspecialchars($pesanan['kecamatan_toko'] ?? ''); ?>, <?php echo htmlspecialchars($pesanan['kota_toko'] ?? ''); ?><br>
                    <?php echo htmlspecialchars($pesanan['provinsi_toko'] ?? ''); ?>, <?php echo htmlspecialchars($pesanan['kode_pos_toko'] ?? ''); ?>
                </p>
                <strong>Telepon Penjual:</strong><p><?php echo htmlspecialchars($pesanan['no_telepon_penjual'] ?? 'Tidak ada'); ?></p>
                </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-house-door-fill"></i> Informasi Pengantaran</h5>
            </div>
            <div class="card-body">
                <strong>Nama Penerima:</strong><p><?php echo htmlspecialchars($pesanan['nama_penerima']); ?></p>
                <strong>Alamat Penerima:</strong>
                <p>
                    <a href="https://maps.google.com/?q=<?php echo urlencode($pesanan['alamat_lengkap'] . ',' . $pesanan['kecamatan']); ?>" target="_blank" class="btn btn-sm btn-outline-primary float-end"><i class="bi bi-geo-alt-fill"></i> Buka Peta</a>
                    <?php echo htmlspecialchars($pesanan['alamat_lengkap']); ?><br>
                    <?php echo htmlspecialchars($pesanan['kecamatan']); ?>, <?php echo htmlspecialchars($pesanan['kota']); ?><br>
                    <?php echo htmlspecialchars($pesanan['provinsi']); ?>, <?php echo htmlspecialchars($pesanan['kode_pos']); ?>
                </p>
                <strong>Telepon Penerima:</strong>
                <p><a href="tel:<?php echo htmlspecialchars($pesanan['no_telepon']); ?>"><?php echo htmlspecialchars($pesanan['no_telepon']); ?></a></p>
                
                <?php if ($pesanan['metode_pembayaran'] === 'COD'): ?>
                    <div class="alert alert-danger mt-3">
                        <h5 class="alert-heading">TUGAS COD</h5>
                        <p class="mb-0">Harap tagih uang tunai sejumlah <strong>Rp <?php echo number_format($pesanan['total_dengan_kode']); ?></strong> dari penerima.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Laporan Pengantaran</h5>
            </div>
            <div class="card-body">
                <?php if ($pesanan['status_pesanan'] == 'dikirim'): ?>
                    <form action="proses_lapor.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="pesanan_id" value="<?php echo $pesanan_id; ?>">
                        
                        <div class="mb-3">
                            <label for="status_pengantaran" class="form-label">Status Pengantaran</label>
                            <select name="status_pengantaran" id="status_pengantaran" class="form-select" required>
                                <option value="selesai">Berhasil Terkirim</option>
                                <option value="gagal">Gagal Terkirim</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kurir_foto_bukti" class="form-label">Upload Foto Bukti (Wajib)</label>
                            <input type="file" class="form-control" name="kurir_foto_bukti" id="kurir_foto_bukti" accept="image/*" required>
                            <small class="form-text text-muted">Contoh: Foto paket di depan rumah penerima.</small>
                        </div>

                        <?php if ($pesanan['metode_pembayaran'] === 'COD'): ?>
                        <div class="mb-3">
                            <label for="kurir_jumlah_cod" class="form-label">Jumlah Uang COD Diterima (Rp)</label>
                            <input type="number" class="form-control" name="kurir_jumlah_cod" id="kurir_jumlah_cod" value="<?php echo (int)$pesanan['total_dengan_kode']; ?>" required>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="kurir_catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea name="kurir_catatan" id="kurir_catatan" class="form-control" rows="3" placeholder="Contoh: Paket dititipkan ke tetangga, Ibu Ani."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Kirim Laporan</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success">
                        <h5 class="alert-heading">Tugas Selesai</h5>
                        <p>Anda telah melaporkan pengantaran untuk pesanan ini.</p>
                        <hr>
                        <p class="mb-0">Silakan kembali ke dasbor untuk melihat tugas lainnya.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_kurir.php';
?>