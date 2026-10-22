<?php
require_once 'config.php';
if (!empty($_SESSION['admin']['id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT admin_id, full_name, username, password, role FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => (int)$admin['admin_id'],
            'name' => $admin['full_name'],
            'username' => $admin['username'],
            'role' => $admin['role']
        ];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CIPHER | Admin Login</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="login-body"><main class="login-card">
<div class="brand">CI<span>PH</span>ER</div><p class="muted">SHIPPING MANAGEMENT SYSTEM</p>
<h1>Admin sign in</h1><p class="muted">Sign in to manage shipping operations.</p>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<label for="username">Username</label><input id="username" name="username" required autocomplete="username">
<label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password">
<button class="button full" type="submit">Sign in</button>
</form>
<p class="hint">First setup? Open <a href="setup_admin.php">administrator setup</a>.</p>
</main></body></html>
