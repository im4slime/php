<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'cipher_db';
$dbUser = 'root';
$dbPassword = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check that WAMP MySQL is running and cipher_db exists.');
}

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function requireLogin(): void {
    if (empty($_SESSION['admin']['id'])) {
        header('Location: index.php');
        exit;
    }
}

function activePage(string $page): string {
    return basename($_SERVER['PHP_SELF'] ?? '') === $page ? 'active' : '';
}

function getShipments(PDO $pdo): array {
    $sql = "SELECT s.*, c.full_name AS customer_name
            FROM shipments s
            LEFT JOIN customers c ON c.customer_id = s.customer_id
            ORDER BY s.created_at DESC";
    return $pdo->query($sql)->fetchAll();
}

function getShipmentCount(PDO $pdo): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
}

function getCustomerCount(PDO $pdo): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
}

function getDeliveredCount(PDO $pdo): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM shipments WHERE status = 'Delivered'")->fetchColumn();
}

function getInTransitCount(PDO $pdo): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM shipments WHERE status = 'In Transit'")->fetchColumn();
}
