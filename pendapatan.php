<?php
// /kurir/pendapatan.php (Versi Final)
$page_title = "Laporan Pendapatan";
require_once 'includes/header_kurir.php';

$kurir_id = $_SESSION['kurir_id'];

// --- Logika Filter ---
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';
$filter_status_pembayaran = $_GET['status_pembayaran'] ?? 'semua';

$where_clauses = ["p.kurir_lokal_id = ?"];
$bind_params = [$kurir_id];
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

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// --- Query untuk Statistik ---
$query_stats = "SELECT 
                    COUNT(id) as total_pengantaran,
                    SUM(gaji_kurir) as total_gaji,
                    SUM(CASE WHEN status_pembayaran_kurir = 'Sudah Dibayar' THEN gaji_kurir ELSE 0 END) as total_diterima,
                    SUM(CASE WHEN status_pembayaran_kurir = 'Belum Dibayar' THEN gaji_kurir ELSE 0 END) as total_tagihan
                FROM pesanan p $where_sql";
$stmt_stats = $koneksi->prepare($query_stats);
$stmt_stats->bind_param($bind_types, ...$bind_params);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

// --- Query untuk Tabel Rincian ---
$query_rincian = "SELECT id, tanggal_pesanan, gaji_kurir, status_pembayaran_kurir FROM pesanan p $where_sql ORDER BY tanggal_pesanan DESC";
$stmt_rincian = $koneksi->prepare($query_rincian);
$stmt_rincian->bind_param($bind_types, ...$bind_params);
$stmt_rincian->execute();
$result_rincian = $stmt_rincian->get_result();
?>

<h3>Laporan Pendapatan Anda</h3>
<hr>

<div class="row">
    <div class="col-md-4 mb-3"><div class="card bg-primary text-white"><div class="card-body"><h6>Total Gaji</h6><h4 class="fw-bold">Rp <?php echo number_format($stats['total_gaji'] ?? 0); ?></h4></div></div></div>
    <div class="col-md-4 mb-3"><div class="card bg-success text-white"><div class="card-body"><h6>Sudah Diterima</h6><h4 class="fw-bold">Rp <?php echo number_format($stats['total_diterima'] ?? 0); ?></h4></div></div></div>
    <div class="col-md-4 mb-3"><div class="card bg-warning text-dark"><div class="card-body"><h6>Tagihan ke Penjual</h6><h4 class="fw-bold">Rp <?php echo number_format($stats['total_tagihan'] ?? 0); ?></h4></div></div></div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" action="" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-4"><label for="start_date" class="form-label">Dari Tanggal</label><input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>"></div>
                <div class="col-md-4"><label for="end_date" class="form-label">Sampai Tanggal</label><input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>"></div>
                <div class="col-md-2"><label for="status_pembayaran" class="form-label">Status</label><select name="status_pembayaran" class="form-select"><option value="semua">Semua</option><option value="Belum Dibayar" <?php if($filter_status_pembayaran == 'Belum Dibayar') echo 'selected'; ?>>Belum Dibayar</option><option value="Sudah Dibayar" <?php if($filter_status_pembayaran == 'Sudah Dibayar') echo 'selected'; ?>>Sudah Dibayar</option></select></div>
                <div class="col-md-auto"><button type="submit" class="btn btn-primary">Filter</button></div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Tanggal</th><th>No. Pesanan</th><th class="text-end">Gaji</th><th class="text-center">Status Pembayaran</th></tr></thead>
                <tbody>
                    <?php if ($result_rincian->num_rows > 0): ?>
                        <?php while($item = $result_rincian->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($item['tanggal_pesanan'])); ?></td>
                                <td><a href="detail_tugas.php?id=<?php echo $item['id']; ?>">#<?php echo $item['id']; ?></a></td>
                                <td class="text-end fw-bold">Rp <?php echo number_format($item['gaji_kurir']); ?></td>
                                <td class="text-center"><span class="badge <?php echo $item['status_pembayaran_kurir'] == 'Sudah Dibayar' ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo $item['status_pembayaran_kurir']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center p-4">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$stmt_rincian->close();
require_once 'includes/footer_kurir.php'; 
?>