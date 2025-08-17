<?php
// /penjual/laporan_kurir.php (Versi FINAL dengan perbaikan sinkronisasi data)
$page_title = "Laporan Kurir Lokal";
require_once '../includes/header_penjual.php';

$user_id = $_SESSION['user_id'];
$stmt_toko = $koneksi->prepare("SELECT id FROM toko WHERE user_id = ?");
$stmt_toko->bind_param("i", $user_id);
$stmt_toko->execute();
$toko_id = $stmt_toko->get_result()->fetch_assoc()['id'];
$stmt_toko->close();

if (!$toko_id) {
    echo '<div class="alert alert-danger">Profil toko Anda tidak ditemukan.</div>';
    require_once '../includes/footer_penjual.php';
    exit();
}

$stmt_kurir_list = $koneksi->prepare("SELECT id, nama_kurir FROM kurir_lokal WHERE toko_id = ? ORDER BY nama_kurir ASC");
$stmt_kurir_list->bind_param("i", $toko_id);
$stmt_kurir_list->execute();
$result_kurir_list = $stmt_kurir_list->get_result();

$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';
$filter_status_pembayaran = $_GET['status_pembayaran'] ?? 'semua';
$filter_kurir_id = isset($_GET['kurir_id']) && !empty($_GET['kurir_id']) ? (int)$_GET['kurir_id'] : 'semua';

$base_query = "
    FROM pesanan p 
    JOIN kurir_lokal kl ON p.kurir_lokal_id = kl.id
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    JOIN produk pr ON dp.produk_id = pr.id
    WHERE pr.toko_id = ?
";

$where_clauses = [];
$bind_params = [$toko_id];
$bind_types = "i";

if (!empty($filter_start_date)) {
    $where_clauses[] = "p.tanggal_pesanan >= ?";
    $bind_params[] = $filter_start_date . " 00:00:00";
    $bind_types .= "s";
}
if (!empty($filter_end_date)) {
    $where_clauses[] = "p.tanggal_pesanan <= ?";
    $bind_params[] = $filter_end_date . " 23:59:59";
    $bind_types .= "s";
}
if ($filter_status_pembayaran !== 'semua') {
    $where_clauses[] = "p.status_pembayaran_kurir = ?";
    $bind_params[] = $filter_status_pembayaran;
    $bind_types .= "s";
}
if ($filter_kurir_id !== 'semua') {
    $where_clauses[] = "p.kurir_lokal_id = ?";
    $bind_params[] = $filter_kurir_id;
    $bind_types .= "i";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " AND " . implode(" AND ", $where_clauses);
}

// --- Query untuk Statistik ---
$query_stats = "SELECT 
                    COUNT(DISTINCT p.id) as total_pengantaran,
                    SUM(p.gaji_kurir) as total_gaji,
                    SUM(CASE WHEN p.status_pembayaran_kurir = 'Sudah Dibayar' THEN p.gaji_kurir ELSE 0 END) as total_dibayar,
                    SUM(CASE WHEN p.status_pembayaran_kurir = 'Belum Dibayar' THEN p.gaji_kurir ELSE 0 END) as total_tagihan
                $base_query $where_sql";

$stmt_stats = $koneksi->prepare($query_stats);
$stmt_stats->bind_param($bind_types, ...$bind_params);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();


// --- Query untuk Tabel Rincian ---
$query_rincian = "SELECT p.id as pesanan_id, p.tanggal_pesanan, p.biaya_ongkir, p.gaji_kurir, p.status_pembayaran_kurir, kl.nama_kurir 
                  $base_query $where_sql
                  GROUP BY p.id
                  ORDER BY p.tanggal_pesanan DESC";

$stmt_rincian = $koneksi->prepare($query_rincian);
$stmt_rincian->bind_param($bind_types, ...$bind_params);
$stmt_rincian->execute();
$result_rincian = $stmt_rincian->get_result();

$periode_teks = "Keseluruhan";
if (!empty($filter_start_date) || !empty($filter_end_date)) {
    $start = !empty($filter_start_date) ? date('d M Y', strtotime($filter_start_date)) : '...';
    $end = !empty($filter_end_date) ? date('d M Y', strtotime($filter_end_date)) : '...';
    $periode_teks = "$start - $end";
}
?>

<h1>Laporan Kurir Lokal</h1>
<p>Lacak kinerja, pendapatan, dan status pembayaran untuk setiap pengantaran yang dilakukan oleh kurir lokal Anda.</p>
<hr>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-info text-white h-100"><div class="card-body"><h6>Total Pengantaran</h6><h4 class="fw-bold"><?php echo number_format($stats['total_pengantaran'] ?? 0); ?></h4><small>Periode: <?php echo $periode_teks; ?></small></div></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-primary text-white h-100"><div class="card-body"><h6>Total Gaji Kurir</h6><h4 class="fw-bold">Rp <?php echo number_format($stats['total_gaji'] ?? 0, 0, ',', '.'); ?></h4><small>Total upah dari pengantaran.</small></div></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-success text-white h-100"><div class="card-body"><h6>Sudah Dibayarkan</h6><h4 class="fw-bold">Rp <?php echo number_format($stats['total_dibayar'] ?? 0, 0, ',', '.'); ?></h4><small>Total gaji yang sudah Anda bayarkan.</small></div></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-danger text-white h-100"><div class="card-body"><h6>Tagihan (Belum Dibayar)</h6><h4 class="fw-bold">Rp <?php echo number_format($stats['total_tagihan'] ?? 0, 0, ',', '.'); ?></h4><small>Total gaji yang perlu dibayarkan.</small></div></div></div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><h5 class="mb-0">Rincian Pengantaran</h5></div>
    <div class="card-body">
        <form method="GET" action="" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label for="start_date" class="form-label">Dari Tanggal</label><input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>"></div>
                <div class="col-md-3"><label for="end_date" class="form-label">Sampai Tanggal</label><input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>"></div>
                <div class="col-md-3"><label for="kurir_id" class="form-label">Nama Kurir</label><select name="kurir_id" class="form-select"><option value="semua">Semua Kurir</option><?php while($kurir = $result_kurir_list->fetch_assoc()): ?><option value="<?php echo $kurir['id']; ?>" <?php if($filter_kurir_id == $kurir['id']) echo 'selected'; ?>><?php echo htmlspecialchars($kurir['nama_kurir']); ?></option><?php endwhile; ?></select></div>
                <div class="col-md-2"><label for="status_pembayaran" class="form-label">Status Bayar</label><select name="status_pembayaran" class="form-select"><option value="semua" <?php if($filter_status_pembayaran == 'semua') echo 'selected'; ?>>Semua</option><option value="Belum Dibayar" <?php if($filter_status_pembayaran == 'Belum Dibayar') echo 'selected'; ?>>Belum Dibayar</option><option value="Sudah Dibayar" <?php if($filter_status_pembayaran == 'Sudah Dibayar') echo 'selected'; ?>>Sudah Dibayar</option></select></div>
                <div class="col-md-auto"><button type="submit" class="btn btn-primary">Filter</button><a href="laporan_kurir.php" class="btn btn-secondary">Reset</a></div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Tanggal</th><th>No. Pesanan</th><th>Nama Kurir</th><th class="text-end">Ongkir (Pembeli)</th><th class="text-end">Gaji (Kurir)</th><th class="text-end">Laba (Penjual)</th><th class="text-center">Status Pembayaran</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                    <?php if ($result_rincian->num_rows > 0): while($item = $result_rincian->fetch_assoc()): $laba_penjual = $item['biaya_ongkir'] - $item['gaji_kurir']; ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($item['tanggal_pesanan'])); ?></td>
                            <td><a href="detail_pesanan.php?id=<?php echo $item['pesanan_id']; ?>">#<?php echo $item['pesanan_id']; ?></a></td>
                            <td><?php echo htmlspecialchars($item['nama_kurir']); ?></td>
                            <td class="text-end">Rp <?php echo number_format($item['biaya_ongkir']); ?></td>
                            <td class="text-end fw-bold">Rp <?php echo number_format($item['gaji_kurir']); ?></td>
                            <td class="text-end <?php echo ($laba_penjual >= 0) ? 'text-success' : 'text-danger'; ?>">Rp <?php echo number_format($laba_penjual); ?></td>
                            <td class="text-center"><span id="status-<?php echo $item['pesanan_id']; ?>" class="badge <?php echo $item['status_pembayaran_kurir'] == 'Sudah Dibayar' ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo htmlspecialchars($item['status_pembayaran_kurir']); ?></span></td>
                            <td class="text-center">
                                <?php if ($item['status_pembayaran_kurir'] == 'Belum Dibayar'): ?>
                                    <button class="btn btn-sm btn-success btn-bayar" data-id="<?php echo $item['pesanan_id']; ?>">Tandai Lunas</button>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="8" class="text-center p-4">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.btn-bayar').on('click', function() {
        var button = $(this);
        var pesananId = button.data('id');
        if (confirm('Anda yakin ingin menandai gaji kurir ini sudah lunas?')) {
            $.ajax({
                url: 'proses_update_pembayaran_kurir.php',
                type: 'POST',
                data: { pesanan_id: pesananId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload(); 
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                }
            });
        }
    });
});
</script>
<?php 
$stmt_rincian->close();
require_once '../includes/footer_penjual.php'; 
?>