<?php
// /admin/kelola_produk.php
$page_title = "Moderasi Produk";
require_once '../includes/header_admin.php';

// Filter
$filter = "WHERE 1";
if (isset($_GET['filter']) && $_GET['filter'] == 'ditinjau') {
    $filter = "WHERE p.status_moderasi = 'ditinjau'";
}

$query = "SELECT p.*, t.nama_toko FROM produk p JOIN toko t ON p.toko_id = t.id $filter ORDER BY p.created_at DESC";
$result = mysqli_query($koneksi, $query);
?>

<h1>Moderasi Produk</h1>
<p>Setujui atau tolak produk yang diajukan oleh penjual.</p>

<div class="mb-3">
    <a href="?filter=semua" class="btn btn-secondary">Tampilkan Semua</a>
    <a href="?filter=ditinjau" class="btn btn-warning">Tampilkan yang Perlu Ditinjau</a>
</div>

<?php 
if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
    echo '<div class="alert alert-success">Status produk berhasil diperbarui.</div>';
}
?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                 <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th>Penjual</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($produk = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <img src="/uploads/produk/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" width="60" class="me-2 float-start">
                            <strong><?php echo htmlspecialchars($produk['nama_produk']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($produk['nama_toko']); ?></td>
                        <td>Rp <?php echo number_format($produk['harga']); ?></td>
                        <td>
                            <?php 
                                $status = $produk['status_moderasi'];
                                $badge_class = 'bg-secondary';
                                if ($status == 'disetujui') $badge_class = 'bg-success';
                                if ($status == 'ditolak') $badge_class = 'bg-danger';
                                echo '<span class="badge ' . $badge_class . '">' . ucfirst($status) . '</span>';
                            ?>
                        </td>
                        <td>
                            <?php if ($produk['status_moderasi'] === 'ditinjau'): ?>
                                <form action="proses_moderasi_produk.php" method="POST" class="d-inline">
                                    <input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>">
                                    <button type="submit" name="action" value="setujui" class="btn btn-success btn-sm">Setujui</button>
                                    <button type="submit" name="action" value="tolak" class="btn btn-danger btn-sm">Tolak</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer_admin.php'; ?>