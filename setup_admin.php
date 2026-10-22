<?php
require_once 'config.php';
$count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$message = '';
$error = '';
if ($count > 0) {
    $message = 'An administrator account already exists. For security, setup is disabled. Sign in instead.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
        $error = 'Enter your name, username, valid email, and a password of at least 10 characters.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO admins (full_name, username, email, password, role) VALUES (?, ?, ?, ?, ?)');
        try {
            $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);
            $message = 'Administrator created. Sign in now, then delete setup_admin.php from your website folder.';
        } catch (PDOException $e) {
            $error = 'Could not create administrator. Check that the username and email are unique.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>CIPHER | Setup</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="login-body"><main class="login-card"><div class="brand">CI<span>PH</span>ER</div><h1>Administrator setup</h1>
<?php if ($message): ?><div class="notice"><?= e($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($count === 0 && !$message): ?>
<form method="post"><label>Full name</label><input name="full_name" required><label>Username</label><input name="username" required><label>Email</label><input type="email" name="email" required><label>Password (10+ characters)</label><input type="password" name="password" minlength="10" required><button class="button full">Create administrator</button></form>
<?php endif; ?>
<p class="hint"><a href="index.php">Return to login</a></p></main></body></html>
