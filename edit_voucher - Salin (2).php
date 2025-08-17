<?php
// /penjual/edit_voucher.php (Versi Final Diperbaiki)
$page_title = "Edit Voucher";
require_once '../includes/header_penjual.php';

// Validasi awal
if (!isset($_GET['id'])) {
    header("Location: kelola_voucher.php");
    exit();
}

$voucher_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil ID toko, pastikan toko ada
$stmt_toko = $koneksi->prepare("SELECT id FROM toko WHERE user_id = ?");
$stmt_toko->bind_param("i", $user_id);
$stmt_toko->execute();
$toko_result = $stmt_toko->get_result();
if ($toko_result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Profil toko Anda tidak ditemukan.</div>";
    require_once '../includes/footer_penjual.php';
    exit();
}
$toko_id = $toko_result->fetch_assoc()['id'];
$stmt_toko->close();

// Ambil data voucher, pastikan voucher ada dan milik toko ini
$stmt = $koneksi->prepare("SELECT * FROM voucher WHERE id = ? AND toko_id = ?");
$stmt->bind_param("ii", $voucher_id, $toko_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    echo "<div class='alert alert-danger'>Voucher tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.</div>";
    require_once '../includes/footer_penjual.php';
    exit();
}
$voucher = $result->fetch_assoc();
$stmt->close();
?>

<h1>Edit Voucher: <?php echo htmlspecialchars($voucher['kode']); ?></h1>
<hr>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="proses_voucher.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="voucher_id" value="<?php echo $voucher['id']; ?>">

            <div class="mb-3">
                <label for="kode" class="form-label">Kode Voucher</label>
                <input type="text" class="form-control" id="kode" name="kode" value="<?php echo htmlspecialchars($voucher['kode']); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jenis_voucher" class="form-label">Jenis Voucher</label>
                    <select class="form-select" id="jenis_voucher" name="jenis_voucher" required>
                        <option value="diskon" <?php if($voucher['jenis_voucher'] == 'diskon') echo 'selected'; ?>>Diskon Persentase (%)</option>
                        <option value="cashback" <?php if($voucher['jenis_voucher'] == 'cashback') echo 'selected'; ?>>Cashback (Rp)</option>
                        <option value="gratis_ongkir" <?php if($voucher['jenis_voucher'] == 'gratis_ongkir') echo 'selected'; ?>>Gratis Ongkir (Rp)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nilai" class="form-label" id="label_nilai">Nilai</label>
                    <input type="number" class="form-control" id="nilai" name="nilai" value="<?php echo $voucher['nilai']; ?>" min="1" step="0.01" required>
                    <small class="form-text text-muted" id="keterangan_nilai"></small>
                </div>
            </div>

            <div class="mb-3">
                <label for="min_pembelian" class="form-label">Minimum Pembelian (Rp) <small>(Opsional)</small></label>
                <input type="number" class="form-control" id="min_pembelian" name="min_pembelian" value="<?php echo $voucher['min_pembelian']; ?>" min="0">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tanggal_mulai" class="form-label">Berlaku Dari</label>
                    <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo date('Y-m-d\TH:i', strtotime($voucher['tanggal_mulai'])); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal_akhir" class="form-label">Berlaku Sampai</label>
                    <input type="datetime-local" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?php echo date('Y-m-d\TH:i', strtotime($voucher['tanggal_akhir'])); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jumlah_penggunaan_total" class="form-label">Kuota Penggunaan Total <small>(Opsional)</small></label>
                    <input type="number" class="form-control" id="jumlah_penggunaan_total" name="jumlah_penggunaan_total" value="<?php echo $voucher['jumlah_penggunaan_total']; ?>" min="1">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="limit_per_pembeli" class="form-label">Batas/Pembeli <small>(Opsional)</small></label>
                    <input type="number" class="form-control" id="limit_per_pembeli" name="limit_per_pembeli" value="<?php echo $voucher['limit_per_pembeli']; ?>" min="1">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Voucher</button>
            <a href="kelola_voucher.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenisVoucherSelect = document.getElementById('jenis_voucher');
    
    function updateNilaiLabel() {
        const labelNilai = document.getElementById('label_nilai');
        const inputNilai = document.getElementById('nilai');
        const keteranganNilai = document.getElementById('keterangan_nilai');

        if (jenisVoucherSelect.value === 'diskon') {
            labelNilai.textContent = 'Nilai Diskon (%)';
            inputNilai.max = 100;
            keteranganNilai.textContent = 'Contoh: 10 untuk 10%.';
        } else if (jenisVoucherSelect.value === 'cashback') {
            labelNilai.textContent = 'Nilai Cashback (Rp)';
            inputNilai.removeAttribute('max');
            keteranganNilai.textContent = 'Contoh: 5000.';
        } else if (jenisVoucherSelect.value === 'gratis_ongkir') {
            labelNilai.textContent = 'Potongan Ongkir Maksimal (Rp)';
            inputNilai.removeAttribute('max');
            keteranganNilai.textContent = 'Contoh: 20000.';
        }
    }
    jenisVoucherSelect.addEventListener('change', updateNilaiLabel);
    updateNilaiLabel();
});
</script>

<?php require_once '../includes/footer_penjual.php'; ?>