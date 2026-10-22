<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Dashboard Overview';
$shipments = getShipments($pdo);
$totalShipments = getShipmentCount($pdo);
$totalCustomers = getCustomerCount($pdo);
$deliveredShipments = getDeliveredCount($pdo);
$inTransitShipments = getInTransitCount($pdo);
$recentShipments = array_slice($shipments, 0, 8);
require 'partials/header.php';
?>
<div class="welcome"><h2>Welcome back, <?= e($_SESSION['admin']['name'] ?? 'Administrator') ?>.</h2><p>Here is the latest activity across your shipping operation.</p></div>
<div class="cards">
<div class="stat-card"><p>Total shipments</p><strong><?= number_format($totalShipments) ?></strong><small>All recorded shipments</small></div>
<div class="stat-card"><p>Total customers</p><strong><?= number_format($totalCustomers) ?></strong><small>Registered customers</small></div>
<div class="stat-card"><p>In transit</p><strong><?= number_format($inTransitShipments) ?></strong><small>Currently on the way</small></div>
<div class="stat-card"><p>Delivered</p><strong><?= number_format($deliveredShipments) ?></strong><small>Successfully delivered</small></div>
</div>
<div class="panel"><div class="panel-heading"><h2>Recent shipments</h2><input class="search" id="tableSearch" placeholder="Search shipments..." type="search"></div>
<div class="table-wrap"><table class="searchable"><thead><tr><th>Tracking number</th><th>Customer</th><th>Route</th><th>Ship date</th><th>Status</th><th>Cost</th></tr></thead><tbody>
<?php if (!$recentShipments): ?><tr><td colspan="6" class="empty">No shipments yet. Add a shipment from the Shipments page.</td></tr><?php else: foreach ($recentShipments as $s): ?>
<tr><td class="tracking"><?= e($s['tracking_number']) ?></td><td><?= e($s['customer_name'] ?? 'Unassigned') ?></td><td><?= e($s['origin']) ?> → <?= e($s['destination']) ?></td><td><?= e($s['ship_date'] ?: 'Not scheduled') ?></td><td><span class="badge <?= e(strtolower(str_replace(' ', '-', $s['status']))) ?>"><?= e($s['status']) ?></span></td><td><?= number_format((float)$s['shipping_cost'], 2) ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<?php require 'partials/footer.php'; ?>
