<?php
// /penjual/edit_kurir.php (Versi Final dengan Skema Gaji)
$page_title = "Edit Kurir Lokal";
require_once '../includes/header_penjual.php';

if (!isset($_GET['id'])) {
    header("Location: kelola_kurir.php");
    exit();
}

$kurir_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt_toko = $koneksi->prepare("SELECT id FROM toko WHERE user_id = ?");
$stmt_toko->bind_param("i", $user_id);
$stmt_toko->execute();
$toko_id = $stmt_toko->get_result()->fetch_assoc()['id'];
$stmt_toko->close();

$stmt_kurir = $koneksi->prepare("SELECT * FROM kurir_lokal WHERE id = ? AND toko_id = ?");
$stmt_kurir->bind_param("ii", $kurir_id, $toko_id);
$stmt_kurir->execute();
$result_kurir = $stmt_kurir->get_result();

if ($result_kurir->num_rows !== 1) {
    echo "<div class='alert alert-danger'>Kurir tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.</div>";
    require_once '../includes/footer_penjual.php';
    exit();
}
$kurir = $result_kurir->fetch_assoc();
$stmt_kurir->close();
?>

<h1>Edit Akun & Gaji Kurir</h1>
<p>Perbarui informasi atau ganti password untuk kurir: <strong><?php echo htmlspecialchars($kurir['nama_kurir']); ?></strong></p>
<hr>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="proses_kurir.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="kurir_id" value="<?php echo $kurir['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="nama_kurir" class="form-label">Nama Lengkap Kurir</label>
                        <input type="text" class="form-control" id="nama_kurir" name="nama_kurir" value="<?php echo htmlspecialchars($kurir['nama_kurir']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_telepon" class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="no_telepon" name="no_telepon" value="<?php echo htmlspecialchars($kurir['no_telepon']); ?>" required>
                    </div>
                    <hr>
                    <h5>Pengaturan Akun Login</h5>
                    <div class="mb-3">
                        <label for="username_kurir" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username_kurir" name="username_kurir" value="<?php echo htmlspecialchars($kurir['username_kurir']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_kurir" class="form-label">Password Baru (Opsional)</label>
                        <input type="password" class="form-control" id="password_kurir" name="password_kurir">
                        <small class="form-text text-muted">Kosongkan jika Anda tidak ingin mengubah password.</small>
                    </div>
                    <hr>
                    <h5>Pengaturan Gaji Kurir (Default)</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipe_gaji" class="form-label">Skema Gaji</label>
                            <select name="tipe_gaji" id="tipe_gaji" class="form-select">
                                <option value="flat" <?php if($kurir['tipe_gaji'] == 'flat') echo 'selected'; ?>>Tarif Flat (Rp)</option>
                                <option value="persen" <?php if($kurir['tipe_gaji'] == 'persen') echo 'selected'; ?>>Persentase (%) dari Ongkir</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nilai_gaji" class="form-label">Nilai Gaji</label>
                            <input type="number" class="form-control" id="nilai_gaji" name="nilai_gaji" value="<?php echo $kurir['nilai_gaji']; ?>" required step="0.01" min="0">
                            <small class="form-text text-muted" id="gajiHelp">Masukkan nominal rupiah, contoh: 5000</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="kelola_kurir.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeGajiSelect = document.getElementById('tipe_gaji');
    const gajiHelp = document.getElementById('gajiHelp');

    function updateGajiHelp() {
        if (tipeGajiSelect.value === 'flat') {
            gajiHelp.textContent = 'Masukkan nominal rupiah, contoh: 5000';
        } else {
            gajiHelp.textContent = 'Masukkan persentase (0-100), contoh: 80 untuk 80%';
        }
    }
    tipeGajiSelect.addEventListener('change', updateGajiHelp);
    updateGajiHelp(); // Panggil saat halaman dimuat
});
</script>

<?php require_once '../includes/footer_penjual.php'; ?>