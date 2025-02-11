<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

checkLogin();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$db = new Database();
$conn = $db->getConnection();

$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $book_id => $quantity) {
    $stmt = $conn->prepare("SELECT b.*, a.Name as AuthorName FROM BOOK b JOIN AUTHOR a ON b.AuthorID = a.AuthorID WHERE b.BookID = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if ($book) {
        $book['Quantity'] = $quantity;
        $book['Subtotal'] = $book['Price'] * $quantity;
        $cart_items[] = $book;
        $total += $book['Subtotal'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-warning">Your cart is empty.</div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td class="text-center" style="width: 100px;">
                                        <?php if ($item['CoverImage']): ?>
                                            <img src="<?php echo SITE_URL; ?>/assets/uploads/covers/<?php echo $item['CoverImage']; ?>" 
                                                 alt="<?php echo $item['Title']; ?>" 
                                                 class="img-fluid rounded" style="max-height: 100px;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item['Title']; ?></td>
                                    <td><?php echo $item['AuthorName']; ?></td>
                                    <td>$<?php echo number_format($item['Price'], 2); ?></td>
                                    <td>
                                        <form method="POST" action="update.php" class="d-inline">
                                            <input type="hidden" name="book_id" value="<?php echo $item['BookID']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['Quantity']; ?>" min="1" class="form-control d-inline w-50">
                                            <button type="submit" name="update" class="btn btn-primary btn-sm">Update</button>
                                        </form>
                                    </td>
                                    <td>$<?php echo number_format($item['Subtotal'], 2); ?></td>
                                    <td>
                                        <form method="POST" action="update.php" class="d-inline">
                                            <input type="hidden" name="book_id" value="<?php echo $item['BookID']; ?>">
                                            <button type="submit" name="remove" class="btn btn-danger btn-sm">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total:</th>
                                <th colspan="2">$<?php echo number_format($total, 2); ?></th>
                            </tr>
                            <tr>
                                <th colspan="7" class="text-end">
                                    <form method="POST" action="update.php">
                                        <button type="submit" name="remove_all" class="btn btn-danger">Remove All</button>
                                    </form>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
