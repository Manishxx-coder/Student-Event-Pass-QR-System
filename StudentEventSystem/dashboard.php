<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$user = $_SESSION['user'];

// Admin: Create event
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && $user['role'] === 'admin'
    && isset($_POST['create_event'])
) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $date = trim($_POST['event_date'] ?? '');
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);

    if (
        $title !== '' &&
        $description !== '' &&
        $venue !== '' &&
        $date !== '' &&
        $capacity &&
        $capacity > 0
    ) {
        $stmt = $pdo->prepare(
            'INSERT INTO events
            (title, description, venue, event_date, capacity, created_by)
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $title,
            $description,
            $venue,
            date('Y-m-d H:i:s', strtotime($date)),
            $capacity,
            $user['id']
        ]);

        header('Location: dashboard.php?created=1');
        exit;
    }
}

// Staff: Verify pass token and mark attendance
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && $user['role'] === 'staff'
    && isset($_POST['checkin'])
) {
    $token = trim($_POST['token'] ?? '');

    $stmt = $pdo->prepare(
        "SELECT id FROM registrations
         WHERE qr_token = ? AND status = 'registered'
         LIMIT 1"
    );

    $stmt->execute([$token]);
    $registration = $stmt->fetch();

    if ($registration) {
        $pdo->beginTransaction();

        try {
            $pdo->prepare(
                "UPDATE registrations
                 SET status = 'checked_in'
                 WHERE id = ?"
            )->execute([$registration['id']]);

            $pdo->prepare(
                'INSERT INTO attendance (registration_id, checked_by)
                 VALUES (?, ?)'
            )->execute([$registration['id'], $user['id']]);

            $pdo->commit();

            header('Location: dashboard.php?checkin=success');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    header('Location: dashboard.php?checkin=failed');
    exit;
}

$pageTitle = 'Dashboard | EventPass';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">
            Welcome, <?= h($user['name']) ?>
            · <?= h(ucfirst($user['role'])) ?>
        </p>
    </div>
</div>

<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success">
        Event created successfully.
    </div>
<?php endif; ?>

<?php if (($_GET['checkin'] ?? '') === 'success'): ?>
    <div class="alert alert-success">
        Attendance checked in successfully.
    </div>
<?php elseif (($_GET['checkin'] ?? '') === 'failed'): ?>
    <div class="alert alert-danger">
        Invalid token or this pass was already checked in.
    </div>
<?php endif; ?>


<?php if ($user['role'] === 'admin'): ?>

    <!-- ADMIN DASHBOARD -->
    <div class="card p-4 mb-4">
        <h2 class="h4">Create event</h2>

        <form method="post" class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Event title</label>
                <input
                    class="form-control"
                    name="title"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Venue</label>
                <input
                    class="form-control"
                    name="venue"
                    required
                >
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea
                    class="form-control"
                    name="description"
                    rows="3"
                    required
                ></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Date and time</label>
                <input
                    class="form-control"
                    type="datetime-local"
                    name="event_date"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Capacity</label>
                <input
                    class="form-control"
                    type="number"
                    name="capacity"
                    min="1"
                    value="100"
                    required
                >
            </div>

            <div class="col-12">
                <button
                    class="btn btn-primary"
                    name="create_event"
                    value="1"
                >
                    Create event
                </button>
            </div>

        </form>
    </div>


<?php elseif ($user['role'] === 'staff'): ?>

    <!-- STAFF DASHBOARD -->
    <div class="card p-4">
        <h2 class="h4">Verify event pass</h2>

        <p class="text-secondary">
            Enter the complete token from a student's QR code
            to mark attendance. Camera-based scanning is not
            included in this version.
        </p>

        <form method="post">
            <label class="form-label">QR token</label>

            <input
                class="form-control mb-3"
                name="token"
                minlength="64"
                maxlength="64"
                required
            >

            <button
                class="btn btn-primary"
                name="checkin"
                value="1"
            >
                Verify and check in
            </button>
        </form>
    </div>


<?php else: ?>

    <!-- STUDENT DASHBOARD -->
    <div class="card p-4">
        <h2 class="h4">My registrations</h2>

        <?php
        $stmt = $pdo->prepare(
            'SELECT
                r.id,
                r.qr_token,
                r.status,
                r.registered_at,
                e.title,
                e.venue,
                e.event_date
             FROM registrations r
             JOIN events e ON e.id = r.event_id
             WHERE r.user_id = ?
             ORDER BY r.registered_at DESC'
        );

        $stmt->execute([$user['id']]);
        $regs = $stmt->fetchAll();
        ?>

        <?php if (!$regs): ?>

            <p class="text-secondary mb-0">
                You have not registered for any events yet.
                <a href="index.php">Browse events</a>.
            </p>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Event Pass</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($regs as $reg): ?>

                        <tr>
                            <td>
                                <?= h($reg['title']) ?>

                                <div class="small text-secondary">
                                    <?= h($reg['venue']) ?>
                                </div>
                            </td>

                            <td>
                                <?= h(
                                    date(
                                        'd M Y, h:i A',
                                        strtotime($reg['event_date'])
                                    )
                                ) ?>
                            </td>

                            <td>
                                <span class="badge text-bg-<?= 
                                    $reg['status'] === 'checked_in'
                                        ? 'success'
                                        : 'primary'
                                ?>">
                                    <?= h($reg['status']) ?>
                                </span>
                            </td>

                            <td>
                                <div class="mb-2">
                                    <strong>Pass token:</strong>
                                    <br>

                                    <code>
                                        <?= h(
                                            substr($reg['qr_token'], 0, 12)
                                        ) ?>…
                                    </code>
                                </div>

                                <button
                                    class="btn btn-sm btn-outline-secondary mb-3"
                                    type="button"
                                    onclick="navigator.clipboard.writeText(
                                        <?= htmlspecialchars(
                                            json_encode(
                                                $reg['qr_token']
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    )"
                                >
                                    Copy full token
                                </button>

                                <div>
                                    <p class="mb-2">
                                        <strong>Event Pass QR Code</strong>
                                    </p>

                                    <img
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&amp;data=<?= urlencode($reg['qr_token']) ?>"
                                        alt="Event Pass QR Code"
                                        width="200"
                                        height="200"
                                        loading="lazy"
                                    >
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
```