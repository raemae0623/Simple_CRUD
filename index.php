<?php
$host = 'localhost';
$dbname = 'activity';
$username = 'access';
$password = 'access!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$message = '';
$id = $name = $description = $price = $quantity = $barcode = '';

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $barcode = $_POST['barcode'];
    $id = $_POST['id'] ?? '';

    if (empty($name) || empty($description) || empty($price) || empty($quantity) || empty($barcode)) {
        $message = 'All fields are required!';
    } else {
        if ($id) {
            // Update existing product
            $sql = "UPDATE product SET name=?, description=?, price=?, quantity=?, barcode=?, updated_at=NOW() WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $quantity, $barcode, $id]);
            $message = 'Product updated successfully!';
        } else {
            // Insert new product
            $sql = "INSERT INTO product (name, description, price, quantity, barcode, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $quantity, $barcode]);
            $message = 'Product added successfully!';
        }
        // Redirect to avoid resubmission
        header("Location: index.php");
        exit();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM product WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $message = 'Product deleted successfully!';
    // Redirect to avoid resubmission
    header("Location: index.php");
    exit();
}

// Handle edit
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM product WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $name = $product['name'];
        $description = $product['description'];
        $price = $product['price'];
        $quantity = $product['quantity'];
        $barcode = $product['barcode'];
    }
}

// Fetch all products
$sql = "SELECT * FROM product";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
</head>
<body>
    <h1>Product List</h1>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <table border="1">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Barcode</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td><?= htmlspecialchars($product['price']) ?></td>
                    <td><?= htmlspecialchars($product['quantity']) ?></td>
                    <td><?= htmlspecialchars($product['barcode']) ?></td>
                    <td><?= htmlspecialchars($product['created_at']) ?></td>
                    <td><?= htmlspecialchars($product['updated_at']) ?></td>
                    <td>
                        <a href="?edit=<?= htmlspecialchars($product['id']) ?>">Edit</a>
                        <a href="?delete=<?= htmlspecialchars($product['id']) ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Product Form</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
        <label>Description</label>
        <input type="text" name="description" value="<?= htmlspecialchars($description) ?>" required>
        <label>Price</label>
        <input type="number" name="price" value="<?= htmlspecialchars($price) ?>" step="0.01" required>
        <label>Quantity</label>
        <input type="number" name="quantity" value="<?= htmlspecialchars($quantity) ?>" required>
        <label>Barcode</label>
        <input type="text" name="barcode" value="<?= htmlspecialchars($barcode) ?>" required>
        <button type="submit">Save Product</button>
    </form>
</body>
</html>
