<?php
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

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
            INSERT INTO products
            (name, category_id, supplier_id, price, stock)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $category_id,
            $supplier_id,
            $price,
            $stock
        ]);

        header(
            "Location: index.php?message=" .
            urlencode("Produk berhasil ditambahkan.") .
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
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container form-container">

    <div class="form-header">
        <div>
            <p class="label">DATA PRODUK</p>
            <h1>Tambah Produk</h1>
            <p class="subtitle">Tambahkan barang baru ke dalam inventaris.</p>
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
                    placeholder="Contoh: Pulpen Hitam"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>

                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= $category['id'] ?>"
                                <?= ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>
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

                        <?php foreach ($suppliers as $supplier): ?>
                            <option
                                value="<?= $supplier['id'] ?>"
                                <?= ($_POST['supplier_id'] ?? '') == $supplier['id'] ? 'selected' : '' ?>
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
                        placeholder="Contoh: 500000"
                        value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
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
                        placeholder="Contoh: 10"
                        value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>"
                        required
                    >
                </div>

            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-reset">Batal</a>
                <button type="submit" class="btn btn-primary">
                    Simpan Produk
                </button>
            </div>

        </form>

    </div>

</div>

</body>
</html>