<?php
// /penjual/detail_pesanan.php (Versi FINAL dengan perhitungan gaji kurir)
$page_title = "Detail Pesanan";
require_once '../includes/header_penjual.php';

if (!isset($_GET['id'])) { header("Location: pesanan_masuk.php"); exit(); }
$pesanan_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil detail pesanan utama
$query = "
    SELECT p.*, t.id as toko_id FROM pesanan p
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    JOIN produk pr ON dp.produk_id = pr.id
    JOIN toko t ON pr.toko_id = t.id
    WHERE p.id = ? AND t.user_id = ?
    GROUP BY p.id
    LIMIT 1
";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("ii", $pesanan_id, $user_id);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) { 
    echo "<div class='alert alert-danger'>Pesanan tidak ditemukan atau Anda tidak memiliki akses.</div>";
    require_once '../includes/footer_penjual.php';
    exit();
}

$toko_id = $pesanan['toko_id'];

// Ambil rincian produk
$query_items = "
    SELECT pr.nama_produk, pr.jenis_produk, dp.jumlah, dp.harga_satuan 
    FROM detail_pesanan dp 
    JOIN produk pr ON dp.produk_id = pr.id 
    WHERE dp.pesanan_id = ?
";
$stmt_items = $koneksi->prepare($query_items);
$stmt_items->bind_param("i", $pesanan_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items_array = [];
while ($item = $items_result->fetch_assoc()) {
    $items_array[] = $item;
}
$stmt_items->close();

// Ambil daftar kurir lokal jika diperlukan, beserta skema gajinya
$kurir_lokal_list = [];
if ($pesanan['kurir'] === 'lokal') {
    $stmt_kurir_list = $koneksi->prepare("SELECT id, nama_kurir, tipe_gaji, nilai_gaji FROM kurir_lokal WHERE toko_id = ? AND is_active = 1");
    $stmt_kurir_list->bind_param("i", $toko_id);
    $stmt_kurir_list->execute();
    $result_kurir_list = $stmt_kurir_list->get_result();
    while ($row = $result_kurir_list->fetch_assoc()) {
        $kurir_lokal_list[] = $row;
    }
    $stmt_kurir_list->close();
}
?>

<h3>Detail Pesanan #<?php echo $pesanan['id']; ?></h3>
<hr>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Rincian Barang Anda</span>
                <span class="badge bg-dark"><?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?></span>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>Produk</th><th class="text-center">Jumlah</th><th class="text-end">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($items_array as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                <td class="text-center">x <?php echo $item['jumlah']; ?></td>
                                <td class="text-end">Rp <?php echo number_format($item['harga_satuan'] * $item['jumlah']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Aksi</div>
            <div class="card-body">
                <p>Status Saat Ini: <span class="badge bg-primary fs-6"><?php echo ucfirst(str_replace('_', ' ', $pesanan['status_pesanan'])); ?></span></p>
                <hr>
                
                <?php if($pesanan['status_pesanan'] == 'diproses'): ?>
                    <form action="proses_update_pesanan.php" method="POST">
                        <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                        
                        <?php if ($pesanan['kurir'] === 'lokal'): ?>
                            <div class="mb-3">
                                <label for="kurir_lokal_id" class="form-label">Tugaskan Kurir Lokal</label>
                                <select name="kurir_lokal_id" id="kurir_lokal_id" class="form-select" required>
                                    <option value="">-- Pilih Kurir --</option>
                                    <?php foreach ($kurir_lokal_list as $kurir): ?>
                                        <option value="<?php echo $kurir['id']; ?>" 
                                                data-tipe-gaji="<?php echo $kurir['tipe_gaji']; ?>" 
                                                data-nilai-gaji="<?php echo $kurir['nilai_gaji']; ?>">
                                            <?php echo htmlspecialchars($kurir['nama_kurir']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if(empty($kurir_lokal_list)): ?>
                                    <div class="form-text text-danger">Anda belum memiliki kurir aktif. Silakan tambahkan di menu "Kelola Kurir Lokal".</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gaji_kurir" class="form-label">Gaji Kurir untuk Pesanan Ini (Rp)</label>
                                <input type="number" class="form-control" id="gaji_kurir" name="gaji_kurir" required>
                                <small id="gajiHelp" class="form-text text-muted">Gaji dihitung otomatis dari skema default kurir. Anda bisa mengubahnya jika perlu.</small>
                            </div>
                            <input type="hidden" name="nomor_resi" value="DIANTAR KURIR LOKAL">
                            <div class="d-grid">
                                <button type="submit" name="action" value="kirim" class="btn btn-success" <?php if(empty($kurir_lokal_list)) echo 'disabled'; ?>>Tugaskan & Kirim</button>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label for="nomor_resi" class="form-label">Masukkan Nomor Resi</label>
                                <input type="text" name="nomor_resi" id="nomor_resi" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="action" value="kirim" class="btn btn-success">Tandai Sebagai Dikirim</button>
                            </div>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <p class="text-muted">Tidak ada aksi yang diperlukan untuk status pesanan ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ongkirPesanan = <?php echo (float)$pesanan['biaya_ongkir']; ?>;
    const kurirSelect = document.getElementById('kurir_lokal_id');
    const gajiInput = document.getElementById('gaji_kurir');

    if (kurirSelect) {
        kurirSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value) {
                gajiInput.value = '';
                return;
            }

            const tipeGaji = selectedOption.getAttribute('data-tipe-gaji');
            const nilaiGaji = parseFloat(selectedOption.getAttribute('data-nilai-gaji'));
            let gajiFinal = 0;

            if (tipeGaji === 'flat') {
                gajiFinal = nilaiGaji;
            } else if (tipeGaji === 'persen') {
                gajiFinal = (ongkirPesanan * nilaiGaji) / 100;
            }
            
            gajiInput.value = Math.round(gajiFinal);
        });
    }
});
</script>

<?php 
$koneksi->close();
require_once '../includes/footer_penjual.php'; 
?>