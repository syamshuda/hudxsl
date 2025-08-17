<?php
// /saldo_saya.php (Halaman Saldo untuk Pembeli)
$page_title = "Saldo Saya";
require_once 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: /auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data saldo pengguna saat ini
$stmt_user = $koneksi->prepare("SELECT saldo FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$saldo_saat_ini = $user['saldo'] ?? 0;
$stmt_user->close();

// Ambil riwayat transaksi saldo
$stmt_riwayat = $koneksi->prepare("SELECT * FROM riwayat_saldo_pembeli WHERE user_id = ? ORDER BY tanggal_transaksi DESC");
$stmt_riwayat->bind_param("i", $user_id);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
?>

<div class="d-flex align-items-center mb-4">
    <a href="/saya.php" class="text-dark text-decoration-none fs-4 me-3"><i class="bi bi-arrow-left-circle"></i></a>
    <h1>Saldo Saya</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Saldo Tersedia</h6>
                <h3 class="card-title fw-bold">Rp <?php echo number_format($saldo_saat_ini, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Riwayat Transaksi Saldo</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_riwayat->num_rows > 0): ?>
                                <?php while($trx = $result_riwayat->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></td>
                                        <td>
                                            <?php if($trx['jenis_transaksi'] == 'masuk'): ?>
                                                <span class="badge bg-success">Masuk</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Keluar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold <?php echo ($trx['jenis_transaksi'] == 'masuk') ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($trx['jenis_transaksi'] == 'masuk' ? '+' : '-'); ?> Rp <?php echo number_format($trx['jumlah'], 2, ',', '.'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($trx['deskripsi']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted p-4">Belum ada riwayat transaksi saldo.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$stmt_riwayat->close();
require_once 'includes/footer.php'; 
?>