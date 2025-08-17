<?php
// /penjual/kelola_voucher.php (Versi Final dengan 3 Jenis Voucher)
$page_title = "Kelola Voucher";
require_once '../includes/header_penjual.php';

$user_id = $_SESSION['user_id'];
$query_toko = "SELECT id FROM toko WHERE user_id = ?";
$stmt_toko = $koneksi->prepare($query_toko);
$stmt_toko->bind_param("i", $user_id);
$stmt_toko->execute();
$toko_id = $stmt_toko->get_result()->fetch_assoc()['id'];
$stmt_toko->close();

$query_voucher = "SELECT * FROM voucher WHERE toko_id = ? ORDER BY created_at DESC";
$stmt_voucher = $koneksi->prepare($query_voucher);
$stmt_voucher->bind_param("i", $toko_id);
$stmt_voucher->execute();
$result_voucher = $stmt_voucher->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Voucher Saya</h1>
    <a href="tambah_voucher.php" class="btn btn-primary">Tambah Voucher Baru</a>
</div>

<?php 
if (isset($_GET['status'])) { /* ... notifikasi ... */ }
?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Kode Voucher</th>
                        <th>Jenis & Nilai</th>
                        <th>Periode Aktif</th>
                        <th>Kuota</th>
                        <th>Batas/Pembeli</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_voucher->num_rows > 0): ?>
                        <?php while($voucher = $result_voucher->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($voucher['kode']); ?></strong><br><small class="text-muted">Min. Blj: Rp<?php echo number_format($voucher['min_pembelian'] ?? 0); ?></small></td>
                                <td>
                                    <?php 
                                        $jenis_voucher = $voucher['jenis_voucher'];
                                        $nilai = (float)$voucher['nilai'];
                                        $teks_nilai = '';
                                        if ($jenis_voucher == 'diskon') $teks_nilai = 'Diskon ' . $nilai . '%';
                                        elseif ($jenis_voucher == 'cashback') $teks_nilai = 'Cashback Rp' . number_format($nilai);
                                        elseif ($jenis_voucher == 'gratis_ongkir') $teks_nilai = 'Gratis Ongkir s/d Rp' . number_format($nilai);
                                        echo htmlspecialchars($teks_nilai);
                                    ?>
                                </td>
                                <td style="min-width: 150px;"><?php echo date('d/m/y H:i', strtotime($voucher['tanggal_mulai'])); ?> - <?php echo date('d/m/y H:i', strtotime($voucher['tanggal_akhir'])); ?></td>
                                <td><?php echo $voucher['jumlah_digunakan_saat_ini'] . ' / ' . ($voucher['jumlah_penggunaan_total'] ?? '∞'); ?></td>
                                <td><?php echo ($voucher['limit_per_pembeli'] ?? '1') . 'x'; ?></td>
                                <td>
                                    <?php
                                    $mulai = new DateTime($voucher['tanggal_mulai']);
                                    $akhir = new DateTime($voucher['tanggal_akhir']);
                                    $now = new DateTime();
                                    if ($voucher['is_active'] && $now >= $mulai && $now <= $akhir) echo '<span class="badge bg-success">Aktif</span>';
                                    elseif ($voucher['is_active'] && $now < $mulai) echo '<span class="badge bg-info">Mendatang</span>';
                                    else echo '<span class="badge bg-danger">Tidak Aktif</span>';
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_voucher.php?id=<?php echo $voucher['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="proses_voucher.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="voucher_id" value="<?php echo $voucher['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $voucher['is_active']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $voucher['is_active'] ? 'btn-secondary' : 'btn-success'; ?>"><?php echo $voucher['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Anda belum memiliki voucher.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php 
$stmt_voucher->close();
require_once '../includes/footer_penjual.php'; 
?>