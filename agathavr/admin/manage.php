<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/role_check.php';

// STRICT: admin only. This is the page a Regular User must be
// blocked from, even if they type /admin/manage.php directly.
requireRole('admin');

$message = '';

// Admin action: delete a booking record.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $bookingsXml = loadXML(BOOKINGS_XML);

    if ($bookingsXml !== false) {
        $dom = dom_import_simplexml($bookingsXml);
        foreach ($bookingsXml->booking as $b) {
            if ((string)$b->id === $deleteId) {
                $node = dom_import_simplexml($b);
                $node->parentNode->removeChild($node);
                break;
            }
        }
        saveXML($bookingsXml, BOOKINGS_XML);
        $message = 'Booking #' . htmlspecialchars($deleteId) . ' was removed.';
    }
}

$bookingsXml = loadXML(BOOKINGS_XML);
$allBookings = $bookingsXml !== false ? $bookingsXml->booking : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Bookings - Sports Facility Booking</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Manage All Bookings</h2>
        <p>This page and this action are restricted to Administrators only.</p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if (count($allBookings) === 0): ?>
            <p>No bookings have been made yet.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th><th>User</th><th>Facility</th><th>Date</th>
                <th>Hours</th><th>Status</th><th>Booked On</th><th></th>
            </tr>
            <?php foreach ($allBookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars((string)$b->id) ?></td>
                <td><?= htmlspecialchars((string)$b->username) ?></td>
                <td><?= htmlspecialchars((string)$b->facility_name) ?></td>
                <td><?= htmlspecialchars((string)$b->date) ?></td>
                <td><?= htmlspecialchars((string)$b->hours) ?></td>
                <td><span class="status-pill status-confirmed"><?= htmlspecialchars((string)$b->status) ?></span></td>
                <td><?= htmlspecialchars((string)$b->created_at) ?></td>
                <td>
                    <form method="POST" action="manage.php" style="margin:0;" onsubmit="return confirm('Remove this booking?');">
                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars((string)$b->id) ?>">
                        <button type="submit" class="btn-danger" style="margin:0; padding:6px 12px; font-size:.8rem;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
