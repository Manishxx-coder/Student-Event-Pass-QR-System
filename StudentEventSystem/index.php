<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Events | EventPass';
$events = $pdo->query("SELECT e.*, u.name AS creator FROM events e LEFT JOIN users u ON u.id=e.created_by ORDER BY e.event_date ASC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero mb-4">
  <span class="badge text-bg-light mb-3">Campus events made simple</span>
  <h1 class="display-5 fw-bold">Your next campus experience starts here.</h1>
  <p class="lead mb-4">Discover events, register online, and manage your digital event pass.</p>
  <a class="btn btn-light btn-lg" href="register.php">Get started</a>
</section>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h3 mb-0">Upcoming events</h2>
  <span class="text-secondary"><?= count($events) ?> event(s)</span>
</div>
<div class="row g-4">
<?php if (!$events): ?>
  <div class="col-12"><div class="card p-4"><h3 class="h5">No events yet</h3><p class="text-secondary mb-0">An admin can sign in and create the first event.</p></div></div>
<?php else: foreach ($events as $event): ?>
  <div class="col-md-6 col-lg-4"><article class="card h-100"><div class="card-body d-flex flex-column">
    <span class="badge text-bg-primary align-self-start mb-2"><?= h(date('d M Y, h:i A', strtotime($event['event_date']))) ?></span>
    <h3 class="h5"><?= h($event['title']) ?></h3><p class="text-secondary"><?= h($event['description']) ?></p>
    <p class="small mb-1"><strong>Venue:</strong> <?= h($event['venue']) ?></p>
    <p class="small"><strong>Capacity:</strong> <?= (int)$event['capacity'] ?></p>
    <a class="btn btn-primary mt-auto" href="<?= !empty($_SESSION['user']) ? 'register_event.php?id='.(int)$event['id'] : 'login.php' ?>">Register for event</a>
  </div></article></div>
<?php endforeach; endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
