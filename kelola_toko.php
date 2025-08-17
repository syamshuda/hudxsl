<?php
// /admin/kelola_toko.php (Versi Disederhanakan)
$page_title = "Kelola Toko";
require_once '../includes/header_admin.php';

$query = "
    SELECT 
        t.id, t.nama_toko, t.saldo, t.is_active,
        u.username AS nama_pemilik,
        (SELECT COUNT(p.id) FROM produk p WHERE p.toko_id = t.id) AS jumlah_produk
    FROM toko t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC
";

$result = $koneksi->query($query);
?>

<h1>Kelola Toko</h1>
<p>Daftar semua toko yang terdaftar di platform. Anda dapat mengaktifkan atau menonaktifkan operasional toko.</p>

<?php if (isset($_GET['status'])) { /* ... (kode notifikasi tetap sama) ... */ } ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Toko</th>
                        <th>Pemilik</th>
                        <th class="text-center">Status Toko</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($toko = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($toko['nama_toko']); ?></strong><br>
                                    <small class="text-muted">ID: <?php echo $toko['id']; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($toko['nama_pemilik']); ?></td>
                                <td class="text-center">
                                    <?php if ($toko['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="detail_toko.php?id=<?php echo $toko['id']; ?>" class="btn btn-primary btn-sm">Detail</a>
                                        <form action="proses_toggle_toko.php" method="POST" class="d-inline">
                                            <input type="hidden" name="toko_id" value="<?php echo $toko['id']; ?>">
                                            <button type="submit" name="action" value="<?php echo $toko['is_active'] ? 'deactivate' : 'activate'; ?>" class="btn btn-sm <?php echo $toko['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                                <?php echo $toko['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center p-4">Belum ada toko yang terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$koneksi->close();
require_once '../includes/footer_admin.php'; 
?>