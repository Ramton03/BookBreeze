<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

checkLogin();
if (!isAdmin()) {
    header("Location: " . SITE_URL);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

if (isset($_POST['delete'])) {
    $book_id = (int)$_POST['book_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM BOOK WHERE BookID = ?");
        $stmt->execute([$book_id]);
        $success = "Book deleted successfully";
    } catch(PDOException $e) {
        $error = "Failed to delete book";
    }
}

$books = $conn->query("
    SELECT b.*, a.Name as AuthorName, g.GenreName 
    FROM BOOK b 
    JOIN AUTHOR a ON b.AuthorID = a.AuthorID 
    JOIN GENRE g ON b.GenreID = g.GenreID
")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Books</h2>
    <a href="add-book.php" class="btn btn-primary mb-3">Add New Book</a>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($books as $book): ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <img src="<?php echo $book['CoverImage'] ? SITE_URL . '/assets/uploads/covers/' . $book['CoverImage'] : SITE_URL . '/assets/images/default-cover.jpg'; ?>" 
                         class="card-img-top img-fluid" 
                         alt="<?php echo $book['Title']; ?>" 
                         style="height: 300px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $book['Title']; ?></h5>
                        <p class="card-text">
                            <strong>Author:</strong> <?php echo $book['AuthorName']; ?><br>
                            <strong>Genre:</strong> <?php echo $book['GenreName']; ?><br>
                            <strong>Price:</strong> $<?php echo number_format($book['Price'], 2); ?>
                        </p>
                        <div class="d-flex justify-content-between">
                            <a href="edit-book.php?id=<?php echo $book['BookID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Include Bootstrap via jsDelivr -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
