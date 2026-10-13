<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if ($name === '' || strlen($name) > 100) $errors[] = 'Enter your name (up to 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
            $stmt->execute([$name,$email,password_hash($password, PASSWORD_DEFAULT),'student']);
            header('Location: login.php?registered=1'); exit;
        } catch (PDOException $e) { $errors[] = 'That email may already be registered.'; }
    }
}
$pageTitle = 'Create account | EventPass';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-7 col-lg-5"><div class="card p-4 p-md-5">
<h1 class="h3 mb-3">Create student account</h1><p class="text-secondary">Register to book campus events.</p>
<?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endforeach; ?>
<form method="post">
<div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="name" maxlength="100" required value="<?= h($_POST['name'] ?? '') ?>"></div>
<div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>"></div>
<div class="mb-3"><label class="form-label">Password (minimum 8 characters)</label><input class="form-control" type="password" name="password" minlength="8" required></div>
<button class="btn btn-primary w-100">Create account</button>
</form><p class="mt-3 mb-0 text-center">Already registered? <a href="login.php">Log in</a></p>
</div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
