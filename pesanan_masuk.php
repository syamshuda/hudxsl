<?php
// /penjual/pesanan_masuk.php
$page_title = "Pesanan Masuk";
require_once '../includes/header_penjual.php';

$user_id = $_SESSION['user_id'];

$query = "
    SELECT DISTINCT p.id as pesanan_id, p.tanggal_pesanan, u.nama_lengkap as nama_pembeli
    FROM pesanan p
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    JOIN produk pr ON dp.produk_id = pr.id
    JOIN toko t ON pr.toko_id = t.id
    JOIN users u ON p.pembeli_id = u.id
    WHERE t.user_id = ? AND p.status_pesanan = 'diproses'
    ORDER BY p.tanggal_pesanan DESC
";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">Pesanan Perlu Diproses</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pembeli</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th style="width: 25%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($item = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $item['pesanan_id']; ?></td>
                                <td><?php echo htmlspecialchars($item['nama_pembeli']); ?></td>
                                <td><?php echo date('d M Y', strtotime($item['tanggal_pesanan'])); ?></td>
                                <td><span class="badge bg-primary">Diproses</span></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="cetak_label.php?id=<?php echo $item['pesanan_id']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-printer"></i> Cetak Label
                                        </a>
                                        <a href="detail_pesanan.php?id=<?php echo $item['pesanan_id']; ?>" class="btn btn-primary btn-sm">
                                            Proses & Kirim
                                        </a>
                                    </div>
                                    </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-4">Belum ada pesanan yang perlu diproses.</td></tr>
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