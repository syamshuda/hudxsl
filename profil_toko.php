<?php
// /penjual/profil_toko.php (Versi Final Penuh dengan Upload Banner)
$page_title = "Profil Toko";
require_once '../includes/header_penjual.php';

$user_id = $_SESSION['user_id'];

// Ambil data toko saat ini
$stmt = $koneksi->prepare("SELECT * FROM toko WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$toko = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$toko) {
    echo '<div class="alert alert-danger">Profil toko tidak ditemukan.</div>';
    require_once '../includes/footer_penjual.php';
    exit();
}
?>

<h1>Profil Toko Saya</h1>
<hr>

<?php
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses') {
        echo '<div class="alert alert-success">Profil toko berhasil diperbarui.</div>';
    } elseif ($_GET['status'] == 'gagal') {
        $pesan = isset($_GET['pesan']) ? htmlspecialchars($_GET['pesan']) : 'Terjadi kesalahan.';
        echo '<div class="alert alert-danger">' . $pesan . '</div>';
    }
}
if (empty($toko['kecamatan'])) {
    echo '<div class="alert alert-warning"><strong>Penting:</strong> Harap lengkapi alamat asal pengiriman toko Anda untuk dapat melanjutkan.</div>';
}
?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="proses_profil_toko.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="toko_id" value="<?php echo $toko['id']; ?>">
            <input type="hidden" name="logo_lama" value="<?php echo htmlspecialchars($toko['logo_toko'] ?? ''); ?>">
            <input type="hidden" name="banner_lama" value="<?php echo htmlspecialchars($toko['banner_toko'] ?? ''); ?>">

            <div class="row">
                <div class="col-md-4 text-center">
                    <p class="fw-bold">Logo Toko</p>
                    <img src="/uploads/logo_toko/<?php echo htmlspecialchars($toko['logo_toko'] ?? 'default_logo.png'); ?>" class="img-thumbnail mb-2" alt="Logo Toko" style="width: 150px; height: 150px; object-fit: cover;">
                    <div class="mb-3">
                        <input type="file" class="form-control form-control-sm" id="logo_toko" name="logo_toko">
                    </div>

                    <p class="fw-bold mt-4">Banner Toko</p>
                    <img src="/uploads/banners/<?php echo htmlspecialchars($toko['banner_toko'] ?? 'default_banner.jpg'); ?>" class="img-thumbnail mb-2" alt="Banner Toko" style="width: 100%; height: 100px; object-fit: cover;">
                    <div class="mb-3">
                        <input type="file" class="form-control form-control-sm" id="banner_toko" name="banner_toko">
                        <small class="form-text text-muted">Ukuran ideal: 1200x675px</small>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="nama_toko" class="form-label">Nama Toko</label>
                        <input type="text" class="form-control" id="nama_toko" name="nama_toko" value="<?php echo htmlspecialchars($toko['nama_toko']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi Toko</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?php echo htmlspecialchars($toko['deskripsi']); ?></textarea>
                    </div>
                </div>
            </div>

            <hr>
            <h5>Alamat Asal Pengiriman Toko</h5>
            <p class="text-muted">Pilih lokasi asal pengiriman Anda dari daftar yang telah disediakan admin.</p>

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
            <div class="mb-3">
                <label for="alamat_lengkap" class="form-label">Detail Alamat (Nama Jalan, Gedung, No. Rumah)</label>
                <textarea class="form-control" id="alamat_lengkap" name="alamat_lengkap" rows="3" required><?php echo htmlspecialchars($toko['alamat_lengkap'] ?? ''); ?></textarea>
            </div>
             <div class="mb-3">
                <label for="kode_pos" class="form-label">Kode Pos</label>
                <input type="text" class="form-control" id="kode_pos" name="kode_pos" value="<?php echo htmlspecialchars($toko['kode_pos'] ?? ''); ?>" required>
            </div>

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
            provinsiSelect.val(savedProvinsi).trigger('change');
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
                if (prov === savedProvinsi && savedKota) {
                    kotaSelect.val(savedKota).trigger('change');
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
                if (kota === savedKota && savedKecamatan) {
                    kecamatanSelect.val(savedKecamatan);
                }
            });
        }
    });
});
</script>

<?php require_once '../includes/footer_penjual.php'; ?>