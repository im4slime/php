<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Customers';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if ($name === '') $error = 'Customer name is required.';
    else {
        $stmt = $pdo->prepare('INSERT INTO customers (full_name,email,phone,address) VALUES (?,?,?,?)');
        $stmt->execute([$name, $email ?: null, $phone ?: null, $address ?: null]);
        header('Location: customers.php?added=1'); exit;
    }
}
$customers = $pdo->query('SELECT * FROM customers ORDER BY created_at DESC')->fetchAll();
require 'partials/header.php';
?>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (isset($_GET['added'])): ?><div class="notice">Customer added successfully.</div><?php endif; ?>
<div class="panel form-panel"><div class="panel-heading"><h2>Add customer</h2></div><form method="post" class="form-grid"><div><label>Full name *</label><input name="full_name" required></div><div><label>Email</label><input type="email" name="email"></div><div><label>Phone</label><input name="phone"></div><div><label>Address</label><input name="address"></div><button class="button">Save customer</button></form></div>
<div class="panel"><div class="panel-heading"><h2>Customer list (<?= count($customers) ?>)</h2><input class="search" id="tableSearch" placeholder="Search customers..." type="search"></div><div class="table-wrap"><table class="searchable"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Added</th></tr></thead><tbody>
<?php if (!$customers): ?><tr><td colspan="5" class="empty">No customers have been added.</td></tr><?php else: foreach ($customers as $c): ?><tr><td><?= e($c['full_name']) ?></td><td><?= e($c['email']) ?></td><td><?= e($c['phone']) ?></td><td><?= e($c['address']) ?></td><td><?= e($c['created_at']) ?></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div>
<?php require 'partials/footer.php'; ?>
