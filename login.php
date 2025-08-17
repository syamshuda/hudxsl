<?php
// /kurir/login.php
$page_title = "Login Kurir";
require_once 'includes/header_kurir.php';

// Jika kurir sudah login, arahkan ke dashboard
if (isset($_SESSION['kurir_id'])) {
    header("Location: " . BASE_URL . "/kurir/index.php");
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h4 class="mb-0">Login Kurir</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <form action="proses_login_kurir.php" method="POST">
                    <div class="mb-3">
                        <label for="username_kurir" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username_kurir" name="username_kurir" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_kurir" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_kurir" name="password_kurir" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer_kurir.php'; ?>