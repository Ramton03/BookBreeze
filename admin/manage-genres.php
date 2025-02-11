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

// Handle genre addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_genre'])) {
    $genre_name = $_POST['genre_name'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO GENRE (GenreName) VALUES (?)");
        $stmt->execute([$genre_name]);
        $success = "Genre added successfully";
    } catch (PDOException $e) {
        $error = "Failed to add genre";
    }
}

// Handle genre removal
if (isset($_POST['remove_genre'])) {
    $genre_id = (int)$_POST['genre_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM GENRE WHERE GenreID = ?");
        $stmt->execute([$genre_id]);
        $success = "Genre removed successfully";
    } catch (PDOException $e) {
        $error = "Failed to remove genre";
    }
}

// Fetch all genres
$stmt = $conn->prepare("SELECT * FROM GENRE");
$stmt->execute();
$genres = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Manage Genres</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Add New Genre</h5>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="genre_name" class="form-label">Genre Name</label>
                    <input type="text" class="form-control" id="genre_name" name="genre_name" required>
                </div>
                <button type="submit" name="add_genre" class="btn btn-primary">Add Genre</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Genres List</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Genre Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($genres as $genre): ?>
                        <tr>
                            <td><?php echo $genre['GenreName']; ?></td>
                            <td>
                                <form method="POST" action="" class="d-inline">
                                    <input type="hidden" name="genre_id" value="<?php echo $genre['GenreID']; ?>">
                                    <button type="submit" name="remove_genre" class="btn btn-danger btn-sm">Remove</button>
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
