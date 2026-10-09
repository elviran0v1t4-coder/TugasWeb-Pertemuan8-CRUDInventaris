<?php
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php?message=" . urlencode("ID produk tidak valid.") . "&type=error");
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name, category_id, supplier_id, price, stock
    FROM products
    WHERE id = ?
");

$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php?message=" . urlencode("Produk tidak ditemukan.") . "&type=error");
    exit;
}

$categories = $conn->query(
    "SELECT id, name FROM categories ORDER BY name ASC"
)->fetchAll();

$suppliers = $conn->query(
    "SELECT id, name FROM suppliers ORDER BY name ASC"
)->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);

    if (
        $name === '' ||
        $category_id <= 0 ||
        $supplier_id <= 0 ||
        $price < 0 ||
        $stock < 0
    ) {
        $error = 'Semua data harus diisi dengan benar.';
    } else {
        $stmt = $conn->prepare("
            UPDATE products
            SET name = ?,
                category_id = ?,
                supplier_id = ?,
                price = ?,
                stock = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $category_id,
            $supplier_id,
            $price,
            $stock,
            $id
        ]);

        header(
            "Location: index.php?message=" .
            urlencode("Produk berhasil diperbarui.") .
            "&type=success"
        );
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container form-container">

    <div class="form-header">
        <div>
            <p class="label">DATA PRODUK</p>
            <h1>Edit Produk</h1>
            <p class="subtitle">Perbarui informasi barang yang dipilih.</p>
        </div>

        <a href="index.php" class="btn btn-reset">← Kembali</a>
    </div>

    <div class="card form-card">

        <?php if ($error): ?>
            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>"
                    required
                >
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>

                        <?php
                        $selectedCategory = $_POST['category_id'] ?? $product['category_id'];
                        ?>

                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= $category['id'] ?>"
                                <?= $selectedCategory == $category['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">-- Pilih Supplier --</option>

                        <?php
                        $selectedSupplier = $_POST['supplier_id'] ?? $product['supplier_id'];
                        ?>

                        <?php foreach ($suppliers as $supplier): ?>
                            <option
                                value="<?= $supplier['id'] ?>"
                                <?= $selectedSupplier == $supplier['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($supplier['name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="price">Harga</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        value="<?= htmlspecialchars($_POST['stock'] ?? $product['stock']) ?>"
                        required
                    >
                </div>

            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-reset">Batal</a>
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

</div>

</body>
</html>