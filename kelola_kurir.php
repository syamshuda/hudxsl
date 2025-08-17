<?php
// /penjual/kelola_kurir.php (Versi FINAL dengan Tombol Edit)
$page_title = "Kelola Kurir Lokal";
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

$stmt_kurir = $koneksi->prepare("SELECT * FROM kurir_lokal WHERE toko_id = ? ORDER BY nama_kurir ASC");
$stmt_kurir->bind_param("i", $toko_id);
$stmt_kurir->execute();
$result_kurir = $stmt_kurir->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Kelola Kurir Lokal</h1>
</div>

<p>Daftarkan dan kelola akun kurir lokal terpercaya Anda. Setiap kurir akan memiliki akun login sendiri untuk melihat tugas pengantaran.</p>

<?php 
if (isset($_GET['status'])) {
    $pesan = htmlspecialchars($_GET['pesan'] ?? 'Operasi berhasil.');
    if ($_GET['status'] == 'sukses') {
        echo '<div class="alert alert-success">' . $pesan . '</div>';
    } elseif ($_GET['status'] == 'gagal') {
        echo '<div class="alert alert-danger">' . $pesan . '</div>';
    }
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tambah Akun Kurir Baru</h5>
            </div>
            <div class="card-body">
                <form action="proses_kurir.php" method="POST">
                    <input type="hidden" name="action" value="tambah">
                    <div class="mb-3">
                        <label for="nama_kurir" class="form-label">Nama Lengkap Kurir</label>
                        <input type="text" class="form-control" id="nama_kurir" name="nama_kurir" required placeholder="Contoh: Budi Santoso">
                    </div>
                    <div class="mb-3">
                        <label for="no_telepon" class="form-label">Nomor Telepon (WhatsApp)</label>
                        <input type="tel" class="form-control" id="no_telepon" name="no_telepon" required placeholder="Contoh: 081234567890">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="username_kurir" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username_kurir" name="username_kurir" required placeholder="Buat username unik">
                    </div>
                    <div class="mb-3">
                        <label for="password_kurir" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_kurir" name="password_kurir" required placeholder="Min. 6 karakter">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Tambah Kurir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Daftar Kurir Anda</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Kurir</th>
                                <th>Username</th>
                                <th class="text-center">Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_kurir->num_rows > 0): ?>
                                <?php while($kurir = $result_kurir->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($kurir['nama_kurir']); ?></td>
                                        <td><?php echo htmlspecialchars($kurir['username_kurir']); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $kurir['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $kurir['is_active'] ? 'Aktif' : 'Non-Aktif'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_kurir.php?id=<?php echo $kurir['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                            <form action="proses_kurir.php" method="POST" class="d-inline">
                                                <input type="hidden" name="kurir_id" value="<?php echo $kurir['id']; ?>">
                                                <button type="submit" name="action" value="<?php echo $kurir['is_active'] ? 'nonaktifkan' : 'aktifkan'; ?>" class="btn btn-sm <?php echo $kurir['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                                    <?php echo $kurir['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                                </button>
                                            </form>
                                            <form action="proses_kurir.php" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus kurir ini?');">
                                                <input type="hidden" name="kurir_id" value="<?php echo $kurir['id']; ?>">
                                                <button type="submit" name="action" value="hapus" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted p-4">Anda belum memiliki kurir lokal.</td>
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
$stmt_kurir->close();
require_once '../includes/footer_penjual.php'; 
?>