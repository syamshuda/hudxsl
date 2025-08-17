<?php
// /penjual/riwayat_pesanan.php
$page_title = "Riwayat Pesanan";
require_once '../includes/header_penjual.php';

$user_id = $_SESSION['user_id'];

$query = "
    SELECT
        p.id as pesanan_id,
        p.tanggal_pesanan,
        p.status_pesanan,
        p.nomor_resi,
        p.nama_penerima,
        p.alamat_lengkap,
        p.kota,
        p.provinsi,
        GROUP_CONCAT(CONCAT(pr.nama_produk, ' (x', dp.jumlah, ')') SEPARATOR ',<br>') as semua_produk,
        SUM(dp.jumlah * dp.harga_satuan) as total_produk_pesanan
    FROM detail_pesanan dp
    JOIN produk pr ON dp.produk_id = pr.id
    JOIN toko t ON pr.toko_id = t.id
    JOIN pesanan p ON dp.pesanan_id = p.id
    WHERE t.user_id = ?
    AND p.status_pesanan IN ('dikirim', 'selesai', 'dibatalkan')
    GROUP BY p.id
    ORDER BY p.tanggal_pesanan DESC
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">Riwayat Pesanan</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Produk</th>
                        <th>Total Produk</th>
                        <th>Status</th>
                        <th>Detail Pengiriman</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($item = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $item['pesanan_id']; ?></td>
                                <td style="min-width: 250px;"><?php echo $item['semua_produk']; ?></td>
                                <td>Rp <?php echo number_format($item['total_produk_pesanan']); ?></td>
                                <td>
                                    <?php
                                    $status = $item['status_pesanan'];
                                    $badge_class = 'bg-secondary';
                                    if($status == 'dikirim') $badge_class = 'bg-info';
                                    if($status == 'selesai') $badge_class = 'bg-success';
                                    if($status == 'dibatalkan') $badge_class = 'bg-danger';
                                    echo '<span class="badge ' . $badge_class . '">' . ucfirst($status) . '</span>';
                                    ?>
                                </td>
                                <td style="min-width: 300px;">
                                    <strong><?php echo htmlspecialchars($item['nama_penerima']); ?></strong><br>
                                    <small>
                                        <?php echo htmlspecialchars($item['alamat_lengkap']); ?><br>
                                        <?php echo htmlspecialchars($item['kota']); ?>, <?php echo htmlspecialchars($item['provinsi']); ?><br>
                                        Resi: <span class="fw-bold text-primary"><?php echo htmlspecialchars($item['nomor_resi'] ?? '-'); ?></span>
                                    </small>
                                </td>
                                <td>
                                    <a href="cetak_label.php?id=<?php echo $item['pesanan_id']; ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                        <i class="bi bi-printer"></i> Cetak Ulang
                                    </a>
                                    </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center p-4">Belum ada riwayat pesanan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
if(isset($stmt)) $stmt->close();
require_once '../includes/footer_penjual.php'; 
?>