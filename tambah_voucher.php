<?php
// /penjual/tambah_voucher.php (Versi Final Sempurna)
$page_title = "Tambah Voucher Baru";
require_once '../includes/header_penjual.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual') {
    header("Location: /auth/login.php");
    exit();
}
?>

<h1>Tambah Voucher Baru</h1>
<hr>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="proses_voucher.php" method="POST">
            <input type="hidden" name="action" value="tambah">

            <div class="mb-3">
                <label for="kode" class="form-label">Kode Voucher</label>
                <input type="text" class="form-control" id="kode" name="kode" required maxlength="50" placeholder="Contoh: PROMOAGUSTUS">
                <small class="form-text text-muted">Kode unik yang akan dimasukkan pembeli.</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jenis_voucher" class="form-label">Jenis Voucher</label>
                    <select class="form-select" id="jenis_voucher" name="jenis_voucher" required>
                        <option value="diskon">Diskon Persentase (%)</option>
                        <option value="cashback">Cashback (Rp)</option>
                        <option value="gratis_ongkir">Gratis Ongkir (Rp)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nilai" class="form-label" id="label_nilai">Nilai Diskon (%)</label>
                    <input type="number" class="form-control" id="nilai" name="nilai" min="1" step="0.01" required>
                    <small class="form-text text-muted" id="keterangan_nilai">Masukkan angka persentase, contoh: 10 untuk 10%.</small>
                </div>
            </div>

            <div class="mb-3">
                <label for="min_pembelian" class="form-label">Minimum Pembelian (Rp) <small class="text-muted">(Opsional)</small></label>
                <input type="number" class="form-control" id="min_pembelian" name="min_pembelian" min="0" placeholder="Contoh: 50000">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tanggal_mulai" class="form-label">Berlaku Dari</label>
                    <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal_akhir" class="form-label">Berlaku Sampai</label>
                    <input type="datetime-local" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jumlah_penggunaan_total" class="form-label">Kuota Penggunaan Total <small class="text-muted">(Opsional)</small></label>
                    <input type="number" class="form-control" id="jumlah_penggunaan_total" name="jumlah_penggunaan_total" min="1" placeholder="Contoh: 100">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="limit_per_pembeli" class="form-label">Batas/Pembeli <small class="text-muted">(Default: 1)</small></label>
                    <input type="number" class="form-control" id="limit_per_pembeli" name="limit_per_pembeli" value="1" min="1">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Voucher</button>
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
            keteranganNilai.textContent = 'Masukkan angka persentase, contoh: 10 untuk 10%.';
        } else if (jenisVoucherSelect.value === 'cashback') {
            labelNilai.textContent = 'Nilai Cashback (Rp)';
            inputNilai.removeAttribute('max');
            keteranganNilai.textContent = 'Masukkan jumlah nominal cashback, contoh: 5000.';
        } else if (jenisVoucherSelect.value === 'gratis_ongkir') {
            labelNilai.textContent = 'Potongan Ongkir Maksimal (Rp)';
            inputNilai.removeAttribute('max');
            keteranganNilai.textContent = 'Masukkan jumlah maksimal potongan ongkir, contoh: 20000.';
        }
    }

    jenisVoucherSelect.addEventListener('change', updateNilaiLabel);
    
    // Panggil fungsi saat halaman pertama kali dimuat untuk menyesuaikan tampilan awal
    updateNilaiLabel();
});
</script>

<?php require_once '../includes/footer_penjual.php'; ?>