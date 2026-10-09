<?php
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$keyword = trim($_GET['keyword'] ?? '');

$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

if ($keyword !== '') {
    $search = "%$keyword%";

    $countStmt = $conn->prepare("
        SELECT COUNT(*)
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.name LIKE ?
        OR c.name LIKE ?
        OR s.name LIKE ?
    ");

    $countStmt->execute([$search, $search, $search]);
} else {
    $countStmt = $conn->query("SELECT COUNT(*) FROM products");
}

$totalData = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalData / $limit));

if ($keyword !== '') {
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.name,
            c.name AS category_name,
            s.name AS supplier_name,
            p.price,
            p.stock
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.name LIKE ?
        OR c.name LIKE ?
        OR s.name LIKE ?
        ORDER BY p.id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bindValue(1, $search, PDO::PARAM_STR);
    $stmt->bindValue(2, $search, PDO::PARAM_STR);
    $stmt->bindValue(3, $search, PDO::PARAM_STR);
    $stmt->bindValue(4, $limit, PDO::PARAM_INT);
    $stmt->bindValue(5, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.name,
            c.name AS category_name,
            s.name AS supplier_name,
            p.price,
            p.stock
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$products = $stmt->fetchAll();

$message = $_GET['message'] ?? '';
$type = $_GET['type'] ?? 'success';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Barang</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <div class="header">
        <div>
            <p class="label">SISTEM INVENTARIS</p>
            <h1>Inventaris Barang</h1>
            <p class="subtitle">Kelola data barang dengan mudah dan terorganisir.</p>
        </div>

        <a href="create.php" class="btn btn-primary">+ Tambah Produk</a>
    </div>

    <?php if ($message): ?>
        <div class="alert <?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <div class="toolbar">
            <form method="GET" class="search-form">
                <input
                    type="text"
                    name="keyword"
                    placeholder="Cari produk, kategori, atau supplier..."
                    value="<?= htmlspecialchars($keyword) ?>"
                >
                <button type="submit" class="btn btn-search">Cari</button>

                <?php if ($keyword !== ''): ?>
                    <a href="index.php" class="btn btn-reset">Reset</a>
                <?php endif; ?>
            </form>

            <div class="total">
                <?= $totalData ?> data ditemukan
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($products) > 0): ?>

                        <?php foreach ($products as $index => $product): ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>

                                <td class="product-name">
                                    <?= htmlspecialchars($product['name']) ?>
                                </td>

                                <td>
                                    <span class="badge">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($product['supplier_name']) ?>
                                </td>

                                <td>
                                    Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                </td>

                                <td>
                                    <span class="stock">
                                        <?= htmlspecialchars($product['stock']) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="actions">
                                        <a
                                            href="edit.php?id=<?= $product['id'] ?>"
                                            class="btn btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="delete.php"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus produk ini?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= $product['id'] ?>"
                                            >

                                            <button type="submit" class="btn btn-delete">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="empty">
                                Data produk tidak ditemukan.
                            </td>
                        </tr>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">

                <?php if ($page > 1): ?>
                    <a href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">
                        ←
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a
                        href="?keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"
                        class="<?= $i == $page ? 'active' : '' ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">
                        →
                    </a>
                <?php endif; ?>

            </div>
        <?php endif; ?>

    </div>

    <p class="footer">CRUD Inventaris • PHP PDO & MySQL</p>

</div>

</body>
</html>