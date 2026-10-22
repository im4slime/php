<?php
require_once 'config.php';

$trackingNumber = strtoupper(trim($_GET['tracking'] ?? $_POST['tracking'] ?? ''));
$shipment = null;
$events = [];
$error = '';

if ($trackingNumber !== '') {
    $stmt = $pdo->prepare(
        "SELECT s.shipment_id, s.tracking_number, s.origin, s.destination,
                s.shipment_type, s.status, s.ship_date, s.estimated_delivery,
                s.created_at, c.full_name AS customer_name
         FROM shipments s
         LEFT JOIN customers c ON c.customer_id = s.customer_id
         WHERE s.tracking_number = ?
         LIMIT 1"
    );
    $stmt->execute([$trackingNumber]);
    $shipment = $stmt->fetch();

    if ($shipment) {
        $eventStmt = $pdo->prepare(
            "SELECT location, description, event_time
             FROM tracking_events
             WHERE shipment_id = ?
             ORDER BY event_time ASC, event_id ASC"
        );
        $eventStmt->execute([$shipment['shipment_id']]);
        $events = $eventStmt->fetchAll();
    } else {
        $error = 'We could not find a package with that tracking number. Check the number and try again.';
    }
}

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function statusClass($status): string {
    return strtolower(str_replace(' ', '-', (string)$status));
}

$steps = [
    'Processing' => 1,
    'In Transit' => 2,
    'Delivered' => 4,
    'On Hold' => 2
];
$currentStatus = $shipment['status'] ?? '';
$currentStep = $steps[$currentStatus] ?? 1;
if ($currentStatus === 'On Hold') $currentStep = 2;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CIPHER | Track your package</title>
<link rel="stylesheet" href="assets/css/tracking.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="track.php">CI<span>PH</span>ER</a>
    <span class="header-label">SHIPMENT TRACKING</span>
</header>
<main>
    <section class="hero">
        <div class="eyebrow">CIPHER DELIVERY NETWORK</div>
        <h1>Your package.<br><span>Every step of the way.</span></h1>
        <p>Enter your tracking number to view your package status, route, estimated delivery and tracking history.</p>
        <form class="track-form" method="get" action="track.php">
            <label class="visually-hidden" for="tracking">Tracking number</label>
            <input id="tracking" name="tracking" value="<?= h($trackingNumber) ?>" placeholder="Enter your tracking number" required>
            <button type="submit">Track package <span aria-hidden="true">→</span></button>
        </form>
        <div class="form-note">Use the tracking number provided by CIPHER.</div>
    </section>

    <?php if ($error): ?>
        <section class="result-card error-card"><div class="error-icon">!</div><h2>Package not found</h2><p><?= h($error) ?></p></section>
    <?php endif; ?>

    <?php if ($shipment): ?>
    <section class="result-section">
        <div class="result-top">
            <div><div class="section-label">TRACKING NUMBER</div><h2 class="tracking-number"><?= h($shipment['tracking_number']) ?></h2></div>
            <span class="status-pill <?= h(statusClass($shipment['status'])) ?>"><?= h($shipment['status']) ?></span>
        </div>

        <div class="route-card">
            <div class="route-point"><span class="point-dot"></span><div><small>FROM</small><strong><?= h($shipment['origin']) ?></strong></div></div>
            <div class="route-line"><span>SHIPMENT ROUTE</span></div>
            <div class="route-point destination"><span class="point-dot"></span><div><small>TO</small><strong><?= h($shipment['destination']) ?></strong></div></div>
        </div>

        <div class="details-grid">
            <div class="detail-box"><span>Estimated delivery</span><strong><?= $shipment['estimated_delivery'] ? h(date('M j, Y', strtotime($shipment['estimated_delivery']))) : 'Not available yet' ?></strong></div>
            <div class="detail-box"><span>Ship date</span><strong><?= $shipment['ship_date'] ? h(date('M j, Y', strtotime($shipment['ship_date']))) : 'Not available' ?></strong></div>
            <div class="detail-box"><span>Package type</span><strong><?= h($shipment['shipment_type'] ?: 'Package') ?></strong></div>
        </div>

        <div class="result-card progress-card">
            <div class="card-heading"><div><div class="section-label">DELIVERY PROGRESS</div><h2>Shipment journey</h2></div></div>
            <div class="progress-track">
                <?php
                $progressSteps = [
                    ['label' => 'Processing', 'sub' => 'Shipment registered', 'number' => 1],
                    ['label' => 'In transit', 'sub' => 'On the way', 'number' => 2],
                    ['label' => 'Delivered', 'sub' => 'Package received', 'number' => 4]
                ];
                foreach ($progressSteps as $step):
                    $done = ($currentStatus === 'Delivered') || ($step['number'] <= $currentStep && $currentStatus !== 'On Hold');
                    $active = ($step['label'] === 'Processing' && $currentStatus === 'Processing') ||
                              (strtolower($step['label']) === 'in transit' && $currentStatus === 'In Transit') ||
                              ($step['label'] === 'Delivered' && $currentStatus === 'Delivered');
                ?>
                <div class="progress-step <?= $done ? 'done' : '' ?> <?= $active ? 'current' : '' ?>">
                    <div class="step-circle"><?= $done ? '✓' : $step['number'] ?></div>
                    <strong><?= h($step['label']) ?></strong><small><?= h($step['sub']) ?></small>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($currentStatus === 'On Hold'): ?><div class="hold-notice"><strong>Shipment on hold</strong><p>Your package is temporarily on hold. Please check back for the next update.</p></div><?php endif; ?>
        </div>

        <div class="result-card history-card">
            <div class="card-heading"><div><div class="section-label">LATEST UPDATES</div><h2>Tracking history</h2></div></div>
            <?php if (!$events): ?>
                <div class="empty-history"><span class="empty-symbol">◷</span><div><strong>No tracking updates yet</strong><p>Your package is registered. New updates will appear here when CIPHER records them.</p></div></div>
            <?php else: ?>
                <div class="timeline">
                <?php foreach (array_reverse($events) as $index => $event): ?>
                    <div class="timeline-item <?= $index === 0 ? 'latest' : '' ?>">
                        <span class="timeline-dot"></span>
                        <div class="timeline-content"><div class="timeline-heading"><strong><?= h($event['description']) ?></strong><time><?= h(date('M j, Y · g:i A', strtotime($event['event_time']))) ?></time></div><p><?= h($event['location']) ?></p></div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <p class="privacy-note">For your privacy, this page only shows shipment information associated with the tracking number entered.</p>
    </section>
    <?php endif; ?>
</main>
<footer><a class="logo footer-logo" href="track.php">CI<span>PH</span>ER</a><p>Reliable shipping. Clear updates.</p><small>&copy; <?= date('Y') ?> CIPHER Shipping</small></footer>
</body>
</html>
