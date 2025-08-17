<?php
$page_title = "Edit Ongkos Kirim";
require_once '../includes/header_admin.php';

if (!isset($_GET['id'])) {
    header("Location: kelola_ongkir.php");
    exit();
}

$id = (int)$_GET['id'];
$stmt = $koneksi->prepare("SELECT * FROM ongkos_kirim WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Data tarif tidak ditemukan.</div>";
    require_once '../includes/footer_admin.php';
    exit();
}
$ongkir = $result->fetch_assoc();
$stmt->close();
?>

<h1>Edit Tarif Ongkos Kirim</h1>
<hr>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="proses_ongkir.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $ongkir['id']; ?>">
                    
                    <div class="mb-3"><label for="kurir" class="form-label">Kurir</label><select name="kurir" id="kurir" class="form-select" required><option value="jnt" <?php if($ongkir['kurir'] == 'jnt') echo 'selected'; ?>>J&T Express</option><option value="pos" <?php if($ongkir['kurir'] == 'pos') echo 'selected'; ?>>POS Indonesia</option><option value="lokal" <?php if($ongkir['kurir'] == 'lokal') echo 'selected'; ?>>Kurir Lokal</option></select></div>
                    <hr><h6>ASAL PENGIRIMAN</h6>
                    <div class="mb-3"><label class="form-label">Provinsi Asal</label><input type="text" name="provinsi_asal" class="form-control" value="<?php echo htmlspecialchars($ongkir['provinsi_asal']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label class="form-label">Kota/Kab. Asal</label><input type="text" name="kota_asal" class="form-control" value="<?php echo htmlspecialchars($ongkir['kota_asal']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label class="form-label">Kecamatan Asal</label><input type="text" name="kecamatan_asal" class="form-control" value="<?php echo htmlspecialchars($ongkir['kecamatan_asal']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></div>
                    <hr><h6>TUJUAN PENGIRIMAN</h6>
                    <div class="mb-3"><label class="form-label">Provinsi Tujuan</label><input type="text" name="provinsi_tujuan" class="form-control" value="<?php echo htmlspecialchars($ongkir['provinsi_tujuan']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label class="form-label">Kota/Kab. Tujuan</label><input type="text" name="kota_tujuan" class="form-control" value="<?php echo htmlspecialchars($ongkir['kota_tujuan']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></div>
                    <div class="mb-3"><label class="form-label">Kecamatan Tujuan</label><input type="text" name="kecamatan_tujuan" class="form-control" value="<?php echo htmlspecialchars($ongkir['kecamatan_tujuan']); ?>" required onkeyup="this.value = this.value.toUpperCase()"></div>
                    <hr>
                    <div class="mb-3"><label class="form-label">Biaya per KG (Rp)</label><input type="number" name="biaya" class="form-control" value="<?php echo $ongkir['biaya']; ?>" required min="0"></div>
                    <div class="mb-3"><label class="form-label">Estimasi (Opsional)</label><input type="text" name="estimasi" class="form-control" value="<?php echo htmlspecialchars($ongkir['estimasi']); ?>"></div>
                    <div class="d-flex justify-content-end"><a href="kelola_ongkir.php" class="btn btn-secondary me-2">Batal</a><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer_admin.php'; ?>