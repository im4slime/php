<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Tracking';
$events = $pdo->query('SELECT te.*, s.tracking_number FROM tracking_events te JOIN shipments s ON s.shipment_id=te.shipment_id ORDER BY te.event_time DESC')->fetchAll();
$shipments = $pdo->query('SELECT shipment_id, tracking_number FROM shipments ORDER BY created_at DESC')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipmentId = (int)($_POST['shipment_id'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (!$shipmentId || $location === '' || $description === '') $error = 'Select a shipment and enter its location and update.';
    else {
        $pdo->prepare('INSERT INTO tracking_events (shipment_id,location,description) VALUES (?,?,?)')->execute([$shipmentId,$location,$description]);
        if (in_array($_POST['status'] ?? '', ['Processing','In Transit','Delivered','On Hold'], true)) {
            $pdo->prepare('UPDATE shipments SET status=? WHERE shipment_id=?')->execute([$_POST['status'],$shipmentId]);
        }
        header('Location: tracking.php?added=1'); exit;
    }
}
require 'partials/header.php';
?>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><?php if (isset($_GET['added'])): ?><div class="notice">Tracking update saved.</div><?php endif; ?>
<div class="panel form-panel"><div class="panel-heading"><h2>Add tracking update</h2></div>
<?php if (!$shipments): ?><p class="empty">Create a shipment first.</p><?php else: ?><form method="post" class="form-grid"><div><label>Shipment</label><select name="shipment_id" required><?php foreach ($shipments as $s): ?><option value="<?= (int)$s['shipment_id'] ?>"><?= e($s['tracking_number']) ?></option><?php endforeach; ?></select></div><div><label>Location</label><input name="location" required></div><div><label>Description</label><input name="description" required></div><div><label>Update shipment status</label><select name="status"><option>Processing</option><option>In Transit</option><option>Delivered</option><option>On Hold</option></select></div><button class="button">Save tracking update</button></form><?php endif; ?></div>
<div class="panel"><div class="panel-heading"><h2>Tracking history</h2><input class="search" id="tableSearch" placeholder="Search tracking history..." type="search"></div><div class="table-wrap"><table class="searchable"><thead><tr><th>Tracking number</th><th>Location</th><th>Update</th><th>Date and time</th></tr></thead><tbody><?php if (!$events): ?><tr><td colspan="4" class="empty">No tracking updates yet.</td></tr><?php else: foreach ($events as $event): ?><tr><td class="tracking"><?= e($event['tracking_number']) ?></td><td><?= e($event['location']) ?></td><td><?= e($event['description']) ?></td><td><?= e($event['event_time']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></div>
<?php require 'partials/footer.php'; ?>
