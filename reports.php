<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Reports';
$total = getShipmentCount($pdo);
$customers = getCustomerCount($pdo);
$delivered = getDeliveredCount($pdo);
$transit = getInTransitCount($pdo);
$revenue = (float)$pdo->query("SELECT COALESCE(SUM(shipping_cost),0) FROM shipments WHERE status='Delivered'")->fetchColumn();
$statuses = $pdo->query('SELECT status, COUNT(*) AS total FROM shipments GROUP BY status ORDER BY status')->fetchAll();
require 'partials/header.php';
?>
<div class="cards"><div class="stat-card"><p>Total shipments</p><strong><?= number_format($total) ?></strong></div><div class="stat-card"><p>Customers</p><strong><?= number_format($customers) ?></strong></div><div class="stat-card"><p>Delivered</p><strong><?= number_format($delivered) ?></strong></div><div class="stat-card"><p>Delivered shipping value</p><strong><?= number_format($revenue,2) ?></strong><small>Same currency as entered in shipment records</small></div></div>
<div class="panel"><div class="panel-heading"><h2>Shipments by status</h2></div><div class="table-wrap"><table><thead><tr><th>Status</th><th>Number of shipments</th></tr></thead><tbody><?php if (!$statuses): ?><tr><td colspan="2" class="empty">No report data yet.</td></tr><?php else: foreach ($statuses as $row): ?><tr><td><?= e($row['status']) ?></td><td><?= number_format((int)$row['total']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></div>
<?php require 'partials/footer.php'; ?>
