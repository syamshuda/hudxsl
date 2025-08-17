<?php
$page_title = "Profil Toko";
require_once '../includes/header_penjual.php';
$user_id = $_SESSION['user_id'];
$stmt = $koneksi->prepare("SELECT * FROM toko WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$toko = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<h1>Profil Toko Saya</h1>
<hr>
<?php
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses') {
        echo '<div class="alert alert-success">Profil toko berhasil diperbarui.</div>';
    } elseif ($_GET['status'] == 'gagal') {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.') . '</div>';
    }
}
if (empty($toko['kecamatan'])) {
    echo '<div class="alert alert-warning"><strong>Penting:</strong> Harap pilih alamat asal pengiriman toko Anda.</div>';
}
?>
<div class="card shadow-sm">
    <div class="card-body">
        <form action="proses_profil_toko.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="toko_id" value="<?php echo $toko['id']; ?>">
            <input type="hidden" name="logo_lama" value="<?php echo htmlspecialchars($toko['logo_toko'] ?? ''); ?>">
            
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="/uploads/logo_toko/<?php echo htmlspecialchars($toko['logo_toko'] ?? 'default_logo.png'); ?>" class="img-thumbnail mb-3" alt="Logo Toko" style="width: 200px; height: 200px; object-fit: cover;">
                    <div class="mb-3"><label for="logo_toko" class="form-label">Ganti Logo (Opsional)</label><input type="file" class="form-control" id="logo_toko" name="logo_toko"></div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3"><label for="nama_toko" class="form-label">Nama Toko</label><input type="text" class="form-control" id="nama_toko" name="nama_toko" value="<?php echo htmlspecialchars($toko['nama_toko']); ?>" required></div>
                    <div class="mb-3"><label for="deskripsi" class="form-label">Deskripsi Toko</label><textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo htmlspecialchars($toko['deskripsi']); ?></textarea></div>
                </div>
            </div>

            <hr>
            <h5>Alamat Asal Pengiriman Toko</h5>
            <p class="text-muted">Pilih lokasi asal dari daftar yang valid. Ini akan menjadi dasar perhitungan ongkir.</p>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="provinsi" class="form-label">Provinsi Asal</label>
                    <select class="form-select" id="provinsi" name="provinsi" required><option value="">-- Memuat... --</option></select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="kota" class="form-label">Kota/Kabupaten Asal</label>
                    <select class="form-select" id="kota" name="kota" required disabled><option value="">-- Pilih Provinsi --</option></select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="kecamatan" class="form-label">Kecamatan Asal</label>
                    <select class="form-select" id="kecamatan" name="kecamatan" required disabled><option value="">-- Pilih Kota/Kabupaten --</option></select>
                </div>
            </div>
            <div class="mb-3"><label for="alamat_lengkap" class="form-label">Detail Alamat</label><textarea class="form-control" name="alamat_lengkap" rows="3" required><?php echo htmlspecialchars($toko['alamat_lengkap'] ?? ''); ?></textarea></div>
            <div class="mb-3"><label for="kode_pos" class="form-label">Kode Pos</label><input type="text" class="form-control" name="kode_pos" value="<?php echo htmlspecialchars($toko['kode_pos'] ?? ''); ?>" required></div>
            <hr>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const savedProvinsi = "<?php echo htmlspecialchars($toko['provinsi'] ?? ''); ?>";
    const savedKota = "<?php echo htmlspecialchars($toko['kota'] ?? ''); ?>";
    const savedKecamatan = "<?php echo htmlspecialchars($toko['kecamatan'] ?? ''); ?>";

    const provinsiSelect = $('#provinsi');
    const kotaSelect = $('#kota');
    const kecamatanSelect = $('#kecamatan');

    // 1. Muat Provinsi Asal
    $.getJSON('../api_alamat.php?get=asal_provinsi', function(data) {
        provinsiSelect.empty().append('<option value="">-- Pilih Provinsi --</option>');
        $.each(data, function(key, value) { provinsiSelect.append($('<option>', { value: value, text: value })); });
        if (savedProvinsi) {
            provinsiSelect.val(savedProvinsi);
            provinsiSelect.trigger('change'); // Memicu event change untuk memuat kota
        }
    });

    // 2. Event Listener untuk Provinsi
    provinsiSelect.on('change', function() {
        const prov = $(this).val();
        kotaSelect.empty().append('<option value="">-- Pilih Kota/Kabupaten --</option>').prop('disabled', true);
        kecamatanSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
        if (prov) {
            $.getJSON(`../api_alamat.php?get=asal_kota&provinsi=${encodeURIComponent(prov)}`, function(data) {
                kotaSelect.prop('disabled', false);
                $.each(data, function(key, value) { kotaSelect.append($('<option>', { value: value, text: value })); });
                // Hanya set nilai dan trigger jika ini adalah bagian dari pemuatan awal
                if (prov === savedProvinsi && savedKota) {
                    kotaSelect.val(savedKota);
                    kotaSelect.trigger('change'); // Memicu event change untuk memuat kecamatan
                }
            });
        }
    });

    // 3. Event Listener untuk Kota
    kotaSelect.on('change', function() {
        const prov = provinsiSelect.val();
        const kota = $(this).val();
        kecamatanSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
        if (prov && kota) {
            $.getJSON(`../api_alamat.php?get=asal_kecamatan&provinsi=${encodeURIComponent(prov)}&kota=${encodeURIComponent(kota)}`, function(data) {
                kecamatanSelect.prop('disabled', false);
                $.each(data, function(key, value) { kecamatanSelect.append($('<option>', { value: value, text: value })); });
                // Hanya set nilai jika ini bagian dari pemuatan awal
                if (kota === savedKota && savedKecamatan) {
                    kecamatanSelect.val(savedKecamatan);
                }
            });
        }
    });
});
</script>
<?php require_once '../includes/footer_penjual.php'; ?>