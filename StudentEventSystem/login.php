<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        header('Location: dashboard.php'); exit;
    }
    $error = 'Incorrect email or password.';
}
$pageTitle = 'Login | EventPass';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-7 col-lg-5"><div class="card p-4 p-md-5">
<h1 class="h3 mb-3">Welcome back</h1>
<?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Account created. Please log in.</div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
<form method="post">
<div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
<div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
<button class="btn btn-primary w-100">Log in</button>
</form><p class="mt-3 mb-0 text-center">New here? <a href="register.php">Create an account</a></p>
</div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
