<?php
// /checkout.php (Versi FINAL dengan perbaikan total pada fitur voucher)
$page_title = "Checkout";
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: " . BASE_URL . "/auth/login.php?redirect=checkout");
    exit();
}

require_once 'includes/header.php';
require_once 'includes/functions.php';

$is_buy_now = isset($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item']);
$items_to_process = $is_buy_now ? [$_SESSION['buy_now_item']['produk_id'] => $_SESSION['buy_now_item']['jumlah']] : ($_SESSION['keranjang'] ?? []);

if (empty($items_to_process)) {
    header("Location: " . BASE_URL . "/keranjang.php?status=kosong"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_lengkap, email, no_telepon, can_use_cod, saldo FROM users WHERE id = $user_id"));

$total_harga_produk = 0;
$total_berat = 0;
$ada_produk_fisik = false;
$ada_produk_digital = false;
$kecamatan_asal_toko = '';
$produk_di_ringkasan = [];

$ids_to_fetch = array_keys($items_to_process);
if (!empty($ids_to_fetch)) {
    $ids_string = implode(',', array_map('intval', $ids_to_fetch));
    $query = "SELECT p.*, t.nama_toko, t.kecamatan as toko_kecamatan, t.id as toko_id FROM produk p JOIN toko t ON p.toko_id = t.id WHERE p.id IN ($ids_string)";
    $result = mysqli_query($koneksi, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $jumlah = $items_to_process[$row['id']];
        $promo_data_item = getEffectivePriceAndPromoStatus($row);
        $total_harga_produk += $promo_data_item['price'] * $jumlah;
        
        $row['promo_data'] = $promo_data_item;
        $produk_di_ringkasan[] = $row;
        
        if ($row['jenis_produk'] == 'digital') $ada_produk_digital = true;
        if ($row['jenis_produk'] == 'fisik') {
            $ada_produk_fisik = true;
            $total_berat += ($row['berat'] * $jumlah);
            if (empty($kecamatan_asal_toko)) { $kecamatan_asal_toko = $row['toko_kecamatan']; }
        }
    }
}
?>

<main class="main-container">
    <div class="d-flex align-items-center mb-4">
        <a href="<?php echo $is_buy_now ? 'javascript:history.back()' : BASE_URL . '/keranjang.php'; ?>" class="text-dark text-decoration-none fs-4 me-3"><i class="bi bi-arrow-left-circle"></i></a>
        <h1>Checkout</h1>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'gagal'): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['pesan'] ?? 'Terjadi kesalahan.'); ?></div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>/proses_checkout.php" method="POST" id="formCheckout">
        <input type="hidden" name="is_buy_now" value="<?php echo $is_buy_now ? '1' : '0'; ?>">
        <div class="row">
            <div class="col-lg-7">
                <?php if ($ada_produk_fisik): ?>
                <div id="physical-section">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header"><h4>Alamat Pengiriman</h4></div>
                        <div class="card-body">
                            <div id="shipping-warning" class="alert alert-warning" style="display: none;"></div>
                            <div class="mb-3"><label class="form-label">Nama Penerima</label><input type="text" class="form-control" name="nama_penerima" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required></div>
                            <div class="mb-3"><label class="form-label">Nomor Telepon</label><input type="tel" class="form-control" name="no_telepon" value="<?php echo htmlspecialchars($user_data['no_telepon'] ?? ''); ?>" required></div>
                            <div class="mb-3"><label class="form-label">Provinsi Tujuan</label><select class="form-select" id="provinsi_tujuan" name="provinsi_tujuan" required><option value="">-- Pilih Lokasi --</option></select></div>
                            <div class="mb-3"><label class="form-label">Kota/Kabupaten Tujuan</label><select class="form-select" id="kota_tujuan" name="kota_tujuan" required disabled><option value="">Pilih provinsi</option></select></div>
                            <div class="mb-3"><label class="form-label">Kecamatan Tujuan</label><select class="form-select" id="kecamatan_tujuan" name="kecamatan_tujuan" required disabled><option value="">Pilih kota/kab.</option></select></div>
                            <div class="mb-3"><label class="form-label">Detail Alamat</label><textarea class="form-control" name="alamat_lengkap" rows="3" placeholder="Nama jalan, gedung, no. rumah..." required></textarea></div>
                            <div class="mb-3"><label class="form-label">Kode Pos</label><input type="text" class="form-control" name="kode_pos" required></div>
                            <div class="mt-3"><label class="form-label">Pilih Kurir</label><select class="form-select" id="kurir" name="kurir" required><option value="">-- Pilih Kurir --</option><option value="jnt">J&T Express</option><option value="pos">POS Indonesia</option><option value="lokal">Kurir Lokal</option></select></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h4>Metode Pembayaran</h4></div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="metodeTransfer" value="Transfer Bank" checked>
                            <label class="form-check-label" for="metodeTransfer">Transfer Bank & QRIS</label>
                        </div>
                        <?php if ($user_data['can_use_cod'] && $ada_produk_fisik): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="metodeCOD" value="COD">
                            <label class="form-check-label" for="metodeCOD">Bayar di Tempat (COD)</label>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5">
                 <div class="card shadow-sm mb-4">
                    <div class="card-header"><h4>Ringkasan Pesanan</h4></div>
                    <div class="card-body">
                        <?php foreach($produk_di_ringkasan as $item): 
                            $jumlah = $items_to_process[$item['id']];
                            $promo_data = $item['promo_data'];
                        ?>
                        <div class="d-flex mb-3 align-items-center">
                            <img src="/uploads/produk/<?php echo htmlspecialchars($item['gambar_produk']); ?>" class="rounded me-3" width="60" height="60" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <small><?php echo htmlspecialchars($item['nama_produk']); ?></small>
                                <div class="fw-bold">x <?php echo $jumlah; ?></div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold">Rp <?php echo number_format($promo_data['price'] * $jumlah); ?></span>
                                <?php if ($promo_data['is_promo']): ?>
                                    <br><small class="text-muted"><del>Rp <?php echo number_format($promo_data['harga_normal'] * $jumlah); ?></del></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <hr>
                        
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0"><span>Subtotal Produk</span><span id="subtotalProduk">Rp <?php echo number_format($total_harga_produk); ?></span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" id="ongkir-summary" style="<?php if (!$ada_produk_fisik) echo 'display:none !important;'; ?>">
                                <span>Biaya Ongkir <small id="kurir-terpilih" class="text-muted"></small></span>
                                <span id="biayaOngkir">Rp 0</span>
                            </li>
                            <li id="voucher-applied-display" class="list-group-item d-flex justify-content-between align-items-center px-0" style="display: none;">
                                <span><span id="voucher-type-text"></span> (<small id="voucher-code-text" class="fw-bold"></small>)</span>
                                <span id="voucher-value-text" class="text-success"></span>
                            </li>
                        </ul>

                        <div class="d-grid mt-3">
                            <button type="button" class="btn btn-outline-success" id="toggle-voucher-btn">
                                Gunakan / Pilih Voucher <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                        <div id="voucher-selection-area" class="mt-3 border rounded p-3" style="display:none;">
                            <div id="voucher-list-container">
                                <p class="text-center text-muted">Memuat voucher...</p>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5"><span>Total Pembayaran</span><span id="totalPembayaran">Rp <?php echo number_format($total_harga_produk); ?></span></div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" name="total_harga" id="inputTotalHarga" value="<?php echo $total_harga_produk; ?>">
                        <input type="hidden" name="biaya_ongkir" id="inputOngkir" value="0">
                        <input type="hidden" name="applied_voucher_id" id="applied_voucher_id" value="">
                        <input type="hidden" name="nilai_diskon_final" id="inputNilaiDiskonFinal" value="0">
                        <div class="d-grid"><button type="submit" class="btn btn-success btn-lg" id="btnBuatPesanan" disabled>Lengkapi Data</button></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>


<script>
// Pastikan jQuery sudah dimuat dari header.php
$(document).ready(function() {
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const adaProdukFisik = <?php echo $ada_produk_fisik ? 'true' : 'false'; ?>;
    const kecamatanAsal = "<?php echo htmlspecialchars($kecamatan_asal_toko); ?>";
    const totalBerat = <?php echo $total_berat; ?>;
    let biayaOngkirSaatIni = 0;
    let selectedVoucher = null;

    function updateTotalDisplay() {
        let subtotal = parseFloat($('#inputTotalHarga').val()) || 0;
        let ongkir = biayaOngkirSaatIni >= 0 ? biayaOngkirSaatIni : 0;
        let diskonProduk = 0;
        let diskonOngkir = 0;
        let nilaiDiskonFinal = 0;

        if (selectedVoucher && selectedVoucher.bisa_dipakai) {
            if (selectedVoucher.jenis_voucher === 'diskon') {
                diskonProduk = subtotal * (parseFloat(selectedVoucher.nilai) / 100);
            } else if (selectedVoucher.jenis_voucher === 'gratis_ongkir') {
                diskonOngkir = Math.min(ongkir, parseFloat(selectedVoucher.nilai));
            }
        }
        
        nilaiDiskonFinal = Math.round(diskonProduk + diskonOngkir);
        let total = subtotal + ongkir - nilaiDiskonFinal;
        total = Math.max(0, total);
        
        $('#biayaOngkir').text('Rp ' + ongkir.toLocaleString('id-ID'));
        $('#totalPembayaran').text('Rp ' + total.toLocaleString('id-ID'));
        $('#inputOngkir').val(ongkir);
        $('#inputNilaiDiskonFinal').val(nilaiDiskonFinal);
        $('#applied_voucher_id').val(selectedVoucher ? selectedVoucher.id : '');

        if (selectedVoucher) {
            $('#voucher-code-text').text(selectedVoucher.kode);
            if (selectedVoucher.jenis_voucher === 'diskon') {
                $('#voucher-type-text').text('Diskon Produk');
                $('#voucher-value-text').text('- Rp' + Math.round(diskonProduk).toLocaleString('id-ID'));
            } else if (selectedVoucher.jenis_voucher === 'gratis_ongkir') {
                $('#voucher-type-text').text('Diskon Ongkir');
                $('#voucher-value-text').text('- Rp' + Math.round(diskonOngkir).toLocaleString('id-ID'));
            } else {
                $('#voucher-type-text').text('Cashback');
                $('#voucher-value-text').text('Akan didapat: Rp' + parseFloat(selectedVoucher.nilai).toLocaleString('id-ID'));
            }
            $('#voucher-applied-display').show();
            $('#toggle-voucher-btn').html(`Voucher Terpasang: <strong class="ms-2 text-success">${selectedVoucher.kode}</strong> <i class="bi bi-chevron-up"></i>`);
        } else {
            $('#voucher-applied-display').hide();
            $('#toggle-voucher-btn').html('Gunakan / Pilih Voucher <i class="bi bi-chevron-down"></i>');
        }
        updateCheckoutButtonState();
    }
    
    function updateCheckoutButtonState() {
        let canCheckout = true;
        let buttonText = 'Buat Pesanan';
        if (adaProdukFisik) {
            if (!$('#kurir').val() || !$('#provinsi_tujuan').val() || !$('#kota_tujuan').val() || !$('#kecamatan_tujuan').val() || biayaOngkirSaatIni < 0) {
                canCheckout = false;
                buttonText = 'Lengkapi Alamat & Kurir';
                if (biayaOngkirSaatIni < 0) buttonText = 'Rute Pengiriman Tidak Tersedia';
            }
        }
        $('#btnBuatPesanan').prop('disabled', !canCheckout).text(buttonText);
    }

    async function updateOngkir() {
        biayaOngkirSaatIni = 0;
        $('#kurir-terpilih').text('');
        const kurir = $('#kurir').val();
        const kecamatanTujuan = $('#kecamatan_tujuan').val();
        
        if (!adaProdukFisik || !kurir || !kecamatanTujuan) {
            updateTotalDisplay(); return;
        }
        
        $('#biayaOngkir').text('Menghitung...');
        let params = { berat: totalBerat, kurir: kurir, kecamatan_asal: kecamatanAsal, kecamatan_tujuan: kecamatanTujuan };
        
        try {
            const response = await fetch(`${BASE_URL}/api_ongkir.php?${$.param(params)}`);
            const data = await response.json();
            if (data.success) {
                biayaOngkirSaatIni = data.biaya;
                $('#kurir-terpilih').text(`(${kurir.toUpperCase()})`);
            } else {
                biayaOngkirSaatIni = -1;
                alert(data.message);
            }
        } catch (error) {
            biayaOngkirSaatIni = -1;
            alert('Error mengambil data ongkir.');
        } finally {
            updateTotalDisplay();
            loadVouchers();
        }
    }

    function loadVouchers() {
        const subtotal = parseFloat($('#inputTotalHarga').val()) || 0;
        const ongkir = biayaOngkirSaatIni >= 0 ? biayaOngkirSaatIni : 0;
        const container = $('#voucher-list-container');
        container.html('<p class="text-center text-muted">Memuat voucher...</p>'); // Tampilkan pesan loading
        
        $.getJSON('api_get_vouchers.php', { subtotal: subtotal, ongkir: ongkir }, function(data) {
            container.empty();
            if (data.vouchers && data.vouchers.length > 0) {
                data.vouchers.forEach(v => {
                    let disabled = v.bisa_dipakai ? '' : 'disabled';
                    let TeksVoucher = '';
                    if (v.jenis_voucher === 'diskon') TeksVoucher = `Diskon ${parseFloat(v.nilai)}%`;
                    if (v.jenis_voucher === 'cashback') TeksVoucher = `Cashback Rp${parseInt(v.nilai).toLocaleString()}`;
                    if (v.jenis_voucher === 'gratis_ongkir') TeksVoucher = `Gratis Ongkir s/d Rp${parseInt(v.nilai).toLocaleString()}`;
                    
                    const voucherHTML = `
                        <div class="form-check mb-2 p-3 border rounded ${disabled ? 'bg-light' : ''}">
                            <input class="form-check-input" type="radio" name="voucherPilihan" id="voucher-${v.id}" value='${JSON.stringify(v)}' ${disabled}>
                            <label class="form-check-label w-100" for="voucher-${v.id}">
                                <strong class="d-block ${disabled ? 'text-muted' : 'text-success'}">${TeksVoucher}</strong>
                                <small>Min. belanja: Rp${parseInt(v.min_pembelian).toLocaleString()}</small>
                                <small class="d-block text-danger">${v.alasan_tidak_bisa || ''}</small>
                            </label>
                        </div>`;
                    container.append(voucherHTML);
                });
            } else {
                container.html('<p class="text-center text-muted">Anda tidak memiliki voucher yang bisa digunakan.</p>');
            }
        });
    }

    if (adaProdukFisik) {
        if (!kecamatanAsal) {
            $('#shipping-warning').text('Penjual belum mengatur alamat pengiriman.').show();
            $('#formCheckout').find('input, select, textarea, button').prop('disabled', true);
            return;
        }
        const provSelect = $('#provinsi_tujuan'), kotaSelect = $('#kota_tujuan'), kecSelect = $('#kecamatan_tujuan');
        $.getJSON(`${BASE_URL}/api_alamat.php?get=tujuan_provinsi&kecamatan_asal=${encodeURIComponent(kecamatanAsal)}`, function(data) {
            if(data.length === 0){
                provSelect.empty().append('<option value="">Jangkauan pengiriman belum tersedia</option>').prop('disabled', true);
                 $('#btnBuatPesanan').prop('disabled', true).text('Tujuan Tidak Terjangkau');
            } else {
                 provSelect.empty().append('<option value="">-- Pilih Provinsi --</option>');
                 $.each(data, function(key, value) { provSelect.append($('<option>', { value: value, text: value })); });
            }
        });
        provSelect.on('change', function() {
            const prov = $(this).val();
            kotaSelect.empty().append('<option value="">-- Pilih Kota/Kab. --</option>').prop('disabled', true);
            kecSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
            if (prov) {
                $.getJSON(`${BASE_URL}/api_alamat.php?get=tujuan_kota&kecamatan_asal=${encodeURIComponent(kecamatanAsal)}&provinsi=${encodeURIComponent(prov)}`, function(data) {
                    kotaSelect.prop('disabled', false);
                    $.each(data, function(key, value) { kotaSelect.append($('<option>', { value: value, text: value })); });
                });
            }
        });
        kotaSelect.on('change', function() {
            const prov = provSelect.val(); const kota = $(this).val();
            kecSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
            if (kota && prov) {
                 $.getJSON(`${BASE_URL}/api_alamat.php?get=tujuan_kecamatan&kecamatan_asal=${encodeURIComponent(kecamatanAsal)}&provinsi=${encodeURIComponent(prov)}&kota=${encodeURIComponent(kota)}`, function(data) {
                    kecSelect.prop('disabled', false);
                    $.each(data, function(key, value) { kecSelect.append($('<option>', { value: value, text: value })); });
                });
            }
        });
        $('#kurir, #kecamatan_tujuan').on('change', updateOngkir);
    }

    // ========== PERUBAHAN UTAMA PADA JAVASCRIPT ADA DI SINI ==========
    // Event listener untuk tombol voucher
    $('#toggle-voucher-btn').on('click', function() {
        // Hanya muat data voucher saat pertama kali dibuka atau saat ongkir berubah
        if ($('#voucher-selection-area').is(':hidden')) {
            loadVouchers();
        }
        // Gunakan fungsi slideToggle dari jQuery
        $('#voucher-selection-area').slideToggle();
        $(this).find('i').toggleClass('bi-chevron-down bi-chevron-up');
    });

    // Event listener saat memilih voucher
    $(document).on('change', 'input[name="voucherPilihan"]', function() {
        const selectedRadio = $(this);
        if (selectedRadio.is(':checked')) {
            selectedVoucher = JSON.parse(selectedRadio.val());
            updateTotalDisplay();
            // Menutup area pilihan secara otomatis setelah memilih
            $('#voucher-selection-area').slideUp();
            $('#toggle-voucher-btn').find('i').removeClass('bi-chevron-up').addClass('bi-chevron-down');
        }
    });
    
    // Inisialisasi awal
    updateCheckoutButtonState();
});
</script>