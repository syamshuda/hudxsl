<?php
$page_title = "Kelola Ongkos Kirim";
require_once '../includes/header_admin.php';
$result = mysqli_query($koneksi, "SELECT * FROM ongkos_kirim ORDER BY provinsi_asal, kota_asal, kecamatan_asal, kurir");
?>
<h1>Kelola Ongkos Kirim</h1>
<p>Tambahkan tarif pengiriman berdasarkan zona asal dan tujuan. Data ini akan menjadi satu-satunya pilihan alamat bagi penjual dan pembeli.</p>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header"><h5>Tambah Tarif Baru</h5></div>
            <div class="card-body">
                <form action="proses_ongkir.php" method="POST">
                    <input type="hidden" name="action" value="tambah">
                    <div class="mb-3"><label for="kurir" class="form-label">Kurir</label><select name="kurir" id="kurir" class="form-select" required><option value="jnt">J&T Express</option><option value="pos">POS Indonesia</option><option value="lokal">Kurir Lokal</option></select></div>
                    <hr><h6>ASAL PENGIRIMAN</h6>
                    <div class="mb-3"><label for="provinsi_asal" class="form-label">Provinsi Asal</label><input type="text" name="provinsi_asal" class="form-control" required placeholder="Contoh: JAWA TIMUR" onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label for="kota_asal" class="form-label">Kota/Kab. Asal</label><input type="text" name="kota_asal" class="form-control" required placeholder="Contoh: KABUPATEN BANGKALAN" onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label for="kecamatan_asal" class="form-label">Kecamatan Asal</label><input type="text" name="kecamatan_asal" class="form-control" required placeholder="Contoh: BANGKALAN" onkeyup="this.value = this.value.toUpperCase()"></div>
                    <hr><h6>TUJUAN PENGIRIMAN</h6>
                    <div class="mb-3"><label for="provinsi_tujuan" class="form-label">Provinsi Tujuan</label><input type="text" name="provinsi_tujuan" class="form-control" required placeholder="Contoh: JAWA TIMUR" onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label for="kota_tujuan" class="form-label">Kota/Kab. Tujuan</label><input type="text" name="kota_tujuan" class="form-control" required placeholder="Contoh: KOTA SURABAYA" onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label for="kecamatan_tujuan" class="form-label">Kecamatan Tujuan</label><input type="text" name="kecamatan_tujuan" class="form-control" required placeholder="Contoh: GUBENG" onkeyup="this.value = this.value.toUpperCase()"></div>
                    <hr>
                    <div class="mb-3"><label for="biaya" class="form-label">Biaya per KG (Rp)</label><input type="number" name="biaya" class="form-control" required min="0"></div>
                    <div class="d-grid"><button type="submit" class="btn btn-primary">Tambah Tarif</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header"><h5>Daftar Tarif Ongkir</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light"><tr><th>Kurir</th><th>Asal</th><th>Tujuan</th><th>Biaya</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($ongkir = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo strtoupper(htmlspecialchars($ongkir['kurir'])); ?></td>
                                        <td><?php echo htmlspecialchars($ongkir['kecamatan_asal']); ?></td>
                                        <td><?php echo htmlspecialchars($ongkir['kecamatan_tujuan']) . ', ' . htmlspecialchars($ongkir['kota_tujuan']); ?></td>
                                        <td>Rp <?php echo number_format($ongkir['biaya']); ?></td>
                                        <td>
                                            <a href="edit_ongkir.php?id=<?php echo $ongkir['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <form action="proses_ongkir.php" method="POST" onsubmit="return confirm('Yakin hapus?');" style="display:inline;"><input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?php echo $ongkir['id']; ?>"><button type="submit" class="btn btn-danger btn-sm">Hapus</button></form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">Belum ada data ongkos kirim.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer_admin.php'; ?>