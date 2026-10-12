<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
if ($_SESSION['user']['role'] !== 'student') { header('Location: dashboard.php'); exit; }
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId) { header('Location: index.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM events WHERE id=?'); $stmt->execute([$eventId]); $event = $stmt->fetch();
if (!$event) { http_response_code(404); exit('Event not found.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id=? AND status <> 'cancelled'");
    $count->execute([$eventId]);
    if ((int)$count->fetchColumn() >= (int)$event['capacity']) { header('Location: index.php?full=1'); exit; }
    $token = bin2hex(random_bytes(32));
    try {
        $stmt = $pdo->prepare("INSERT INTO registrations (user_id,event_id,qr_token) VALUES (?,?,?)");
        $stmt->execute([$_SESSION['user']['id'],$eventId,$token]);
        header('Location: dashboard.php?registered=1'); exit;
    } catch (PDOException $e) { $error = 'You may already be registered for this event.'; }
}
$pageTitle = 'Register for event | EventPass';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-8"><div class="card p-4">
<h1 class="h3"><?= h($event['title']) ?></h1><p><?= h($event['description']) ?></p>
<p><strong>Venue:</strong> <?= h($event['venue']) ?></p><p><strong>Date:</strong> <?= h(date('d M Y, h:i A', strtotime($event['event_date']))) ?></p>
<?php if (!empty($error)): ?><div class="alert alert-warning"><?= h($error) ?></div><?php endif; ?>
<form method="post"><button class="btn btn-primary">Confirm registration</button> <a class="btn btn-outline-secondary" href="index.php">Cancel</a></form>
</div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
