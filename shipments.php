<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Shipments';
$error = '';
$customers = $pdo->query('SELECT customer_id, full_name FROM customers ORDER BY full_name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    // Update an existing shipment's status and add a customer-visible tracking event.
    if ($action === 'update_status') {
        $shipmentId = (int)($_POST['shipment_id'] ?? 0);
        $newStatus = trim($_POST['new_status'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $validStatuses = ['Processing', 'In Transit', 'Delivered', 'On Hold'];

        if ($shipmentId < 1 || !in_array($newStatus, $validStatuses, true)) {
            $error = 'Select a valid shipment and status.';
        } else {
            try {
                $pdo->beginTransaction();

                $find = $pdo->prepare('SELECT shipment_id, tracking_number, status FROM shipments WHERE shipment_id = ? LIMIT 1');
                $find->execute([$shipmentId]);
                $existing = $find->fetch(PDO::FETCH_ASSOC);

                if (!$existing) {
                    throw new RuntimeException('Shipment not found.');
                }

                $update = $pdo->prepare('UPDATE shipments SET status = ? WHERE shipment_id = ?');
                $update->execute([$newStatus, $shipmentId]);

                if ($description === '') {
                    $description = 'Shipment status updated to ' . $newStatus . '.';
                }
                if ($location === '') {
                    $location = 'CIPHER Shipping Network';
                }

                $event = $pdo->prepare('INSERT INTO tracking_events (shipment_id, location, description, event_time) VALUES (?, ?, ?, NOW())');
                $event->execute([$shipmentId, $location, $description]);

                $pdo->commit();
                header('Location: shipments.php?updated=1');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Could not update shipment status. Check that the tracking_events table has location, description and event_time columns.';
            }
        }
    } else {
    $tracking = strtoupper(trim($_POST['tracking_number'] ?? ''));
    $customerId = ($_POST['customer_id'] ?? '') !== '' ? (int)$_POST['customer_id'] : null;
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $type = trim($_POST['shipment_type'] ?? 'Parcel');
    $status = $_POST['status'] ?? 'Processing';
    $cost = (float)($_POST['shipping_cost'] ?? 0);
    $shipDate = $_POST['ship_date'] ?: null;
    $delivery = $_POST['estimated_delivery'] ?: null;
    $validStatuses = ['Processing','In Transit','Delivered','On Hold'];
    if ($tracking === '' || $origin === '' || $destination === '') {
        $error = 'Tracking number, origin, and destination are required.';
    } elseif (!in_array($status, $validStatuses, true)) {
        $error = 'Select a valid shipment status.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO shipments (tracking_number,customer_id,origin,destination,shipment_type,status,shipping_cost,ship_date,estimated_delivery) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$tracking,$customerId,$origin,$destination,$type,$status,$cost,$shipDate,$delivery]);
            header('Location: shipments.php?added=1'); exit;
        } catch (PDOException $e) {
            $error = 'Could not add shipment. The tracking number may already exist.';
        }
    }
    }
}
$shipments = getShipments($pdo);
require 'partials/header.php';
?>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (isset($_GET['added'])): ?><div class="notice">Shipment added successfully.</div><?php endif; ?>
<?php if (isset($_GET['updated'])): ?><div class="notice">Shipment status and tracking history updated successfully.</div><?php endif; ?>
<div class="panel form-panel"><div class="panel-heading"><h2>Create shipment</h2></div><form method="post" class="form-grid">
<div><label>Tracking number *</label><input name="tracking_number" required placeholder="CIP-2026-0001"></div>
<div><label>Customer</label><select name="customer_id"><option value="">Unassigned</option><?php foreach ($customers as $c): ?><option value="<?= (int)$c['customer_id'] ?>"><?= e($c['full_name']) ?></option><?php endforeach; ?></select></div>
<div><label>Origin *</label><input name="origin" required></div><div><label>Destination *</label><input name="destination" required></div>
<div><label>Shipment type</label><input name="shipment_type" value="Parcel"></div>
<div><label>Status</label><select name="status"><option>Processing</option><option>In Transit</option><option>Delivered</option><option>On Hold</option></select></div>
<div><label>Shipping cost</label><input type="number" name="shipping_cost" min="0" step="0.01" value="0"></div>
<div><label>Ship date</label><input type="date" name="ship_date"></div><div><label>Estimated delivery</label><input type="date" name="estimated_delivery"></div>
<button class="button">Save shipment</button></form></div>
<div class="panel"><div class="panel-heading"><h2>All shipments (<?= count($shipments) ?>)</h2><input class="search" id="tableSearch" placeholder="Search shipments..." type="search"></div><div class="table-wrap"><table class="searchable"><thead><tr><th>Tracking number</th><th>Customer</th><th>Route</th><th>Status</th><th>Update status</th><th>Ship date</th><th>Cost</th></tr></thead><tbody>
<?php if (!$shipments): ?><tr><td colspan="7" class="empty">No shipments have been added.</td></tr><?php else: foreach ($shipments as $s): ?><tr>
<td class="tracking"><?= e($s['tracking_number']) ?></td>
<td><?= e($s['customer_name'] ?? 'Unassigned') ?></td>
<td><?= e($s['origin']) ?> → <?= e($s['destination']) ?></td>
<td><span class="badge <?= e(strtolower(str_replace(' ', '-', $s['status']))) ?>"><?= e($s['status']) ?></span></td>
<td>
<form method="post" class="status-update-form">
<input type="hidden" name="action" value="update_status">
<input type="hidden" name="shipment_id" value="<?= (int)$s['shipment_id'] ?>">
<select name="new_status" aria-label="New status for <?= e($s['tracking_number']) ?>">
<?php foreach (['Processing','In Transit','Delivered','On Hold'] as $statusOption): ?>
<option value="<?= e($statusOption) ?>" <?= $s['status'] === $statusOption ? 'selected' : '' ?>><?= e($statusOption) ?></option>
<?php endforeach; ?>
</select>
<input name="location" placeholder="Current location (optional)" aria-label="Current location">
<input name="description" placeholder="Update message (optional)" aria-label="Tracking update message">
<button class="button" type="submit">Save</button>
</form>
</td>
<td><?= e($s['ship_date'] ?: 'Not scheduled') ?></td>
<td><?= number_format((float)$s['shipping_cost'],2) ?></td>
</tr><?php endforeach; endif; ?>
</tbody></table></div></div>
<?php require 'partials/footer.php'; ?>
