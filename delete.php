<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header(
        "Location: index.php?message=" .
        urlencode("ID produk tidak valid.") .
        "&type=error"
    );
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $message = "Produk berhasil dihapus.";
        $type = "success";
    } else {
        $message = "Produk tidak ditemukan.";
        $type = "error";
    }
} catch (PDOException $e) {
    $message = "Produk tidak dapat dihapus karena masih digunakan.";
    $type = "error";
}

header(
    "Location: index.php?message=" .
    urlencode($message) .
    "&type=" . urlencode($type)
);
exit;
?>