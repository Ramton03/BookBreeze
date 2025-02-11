
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

$success = '';
$error = '';

// Handle author addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_author'])) {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $nationality = $_POST['nationality'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO AUTHOR (Name, Bio, Nationality) VALUES (?, ?, ?)");
        $stmt->execute([$name, $bio, $nationality]);
        $success = "Author added successfully";
    } catch (PDOException $e) {
        $error = "Failed to add author";
    }
}

// Handle author removal
if (isset($_POST['remove_author'])) {
    $author_id = (int)$_POST['author_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM AUTHOR WHERE AuthorID = ?");
        $stmt->execute([$author_id]);
        $success = "Author removed successfully";
    } catch (PDOException $e) {
        $error = "Failed to remove author";
    }
}

// Fetch all authors
$stmt = $conn->prepare("SELECT * FROM AUTHOR");
$stmt->execute();
$authors = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Manage Authors</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Add New Author</h5>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="nationality" class="form-label">Nationality</label>
                    <input type="text" class="form-control" id="nationality" name="nationality" required>
                </div>
                <button type="submit" name="add_author" class="btn btn-primary">Add Author</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Authors List</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Bio</th>
                        <th>Nationality</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($authors as $author): ?>
                        <tr>
                            <td><?php echo $author['Name']; ?></td>
                            <td><?php echo $author['Bio']; ?></td>
                            <td><?php echo $author['Nationality']; ?></td>
                            <td>
                                <form method="POST" action="" class="d-inline">
                                    <input type="hidden" name="author_id" value="<?php echo $author['AuthorID']; ?>">
                                    <button type="submit" name="remove_author" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Bootstrap via jsDelivr -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
