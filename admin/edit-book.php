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

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$book_id) {
    header("Location: manage-books.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $price = (float)$_POST['price'];
    $pub_year = (int)$_POST['pub_year'];
    
    // Handle author (existing or new)
    if ($_POST['author_type'] === 'new') {
        try {
            $stmt = $conn->prepare("INSERT INTO AUTHOR (Name, Nationality) VALUES (?, ?)");
            $stmt->execute([$_POST['new_author'], $_POST['nationality']]);
            $author_id = $conn->lastInsertId();
        } catch(PDOException $e) {
            $error = "Failed to add new author";
        }
    } else {
        $author_id = (int)$_POST['author_id'];
    }
    
    // Handle genre (existing or new)
    if ($_POST['genre_type'] === 'new') {
        try {
            $stmt = $conn->prepare("INSERT INTO GENRE (GenreName) VALUES (?)");
            $stmt->execute([$_POST['new_genre']]);
            $genre_id = $conn->lastInsertId();
        } catch(PDOException $e) {
            $error = "Failed to add new genre";
        }
    } else {
        $genre_id = (int)$_POST['genre_id'];
    }
    
    $cover_image = '';
    if(isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $cover_image = uploadImage($_FILES['cover']);
        if(!$cover_image) {
            $error = "Failed to upload image";
        }
    }
    
    if(!$error) {
        try {
            if($cover_image) {
                $stmt = $conn->prepare("UPDATE BOOK SET Title=?, ISBN=?, CoverImage=?, AuthorID=?, GenreID=?, Price=?, PubYear=? WHERE BookID=?");
                $stmt->execute([$title, $isbn, $cover_image, $author_id, $genre_id, $price, $pub_year, $book_id]);
            } else {
                $stmt = $conn->prepare("UPDATE BOOK SET Title=?, ISBN=?, AuthorID=?, GenreID=?, Price=?, PubYear=? WHERE BookID=?");
                $stmt->execute([$title, $isbn, $author_id, $genre_id, $price, $pub_year, $book_id]);
            }
            $success = "Book updated successfully";
        } catch(PDOException $e) {
            $error = "Failed to update book";
        }
    }
}

// Fetch book data
$stmt = $conn->prepare("SELECT b.*, a.Name as AuthorName, a.Nationality FROM BOOK b 
                       LEFT JOIN AUTHOR a ON b.AuthorID = a.AuthorID 
                       WHERE b.BookID = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

// Fetch authors and genres
$authors = $conn->query("SELECT AuthorID, Name, Nationality FROM AUTHOR ORDER BY Name")->fetchAll();
$genres = $conn->query("SELECT GenreID, GenreName FROM GENRE ORDER BY GenreName")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-image: url('../assets/other/library-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }
        .current-cover {
            max-width: 150px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error-message {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 4px solid #dc3545;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .success-message {
            background-color: rgba(25, 135, 84, 0.1);
            border-left: 4px solid #198754;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Edit Book</h2>
            
            <?php if($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($book['Title']); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" class="form-control" name="isbn" value="<?php echo htmlspecialchars($book['ISBN']); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Author</label>
                        <select id="author_type" class="form-select mb-2" name="author_type">
                            <option value="existing">Select Existing Author</option>
                            <option value="new">Add New Author</option>
                        </select>
                        
                        <div id="existing_author">
                            <select name="author_id" class="form-control select2">
                                <?php foreach($authors as $author): ?>
                                    <option value="<?php echo $author['AuthorID']; ?>" 
                                            <?php echo ($author['AuthorID'] == $book['AuthorID']) ? 'selected' : ''; ?>>
                                        <?php echo $author['Name'] . ' (' . $author['Nationality'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="new_author" style="display:none;">
                            <input type="text" class="form-control mb-2" name="new_author" placeholder="Author Name">
                            <input type="text" class="form-control" name="nationality" placeholder="Nationality">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Genre</label>
                        <select id="genre_type" class="form-select mb-2" name="genre_type">
                            <option value="existing">Select Existing Genre</option>
                            <option value="new">Add New Genre</option>
                        </select>
                        
                        <div id="existing_genre">
                            <select name="genre_id" class="form-control select2">
                                <?php foreach($genres as $genre): ?>
                                    <option value="<?php echo $genre['GenreID']; ?>"
                                            <?php echo ($genre['GenreID'] == $book['GenreID']) ? 'selected' : ''; ?>>
                                        <?php echo $genre['GenreName']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="new_genre" style="display:none;">
                            <input type="text" class="form-control" name="new_genre" placeholder="New Genre Name">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" step="0.01" 
                               value="<?php echo $book['Price']; ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="pub_year" class="form-label">Publication Year</label>
                        <input type="number" class="form-control" name="pub_year" 
                               value="<?php echo $book['PubYear']; ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="cover" class="form-label">Cover Image</label>
                    <?php if($book['CoverImage']): ?>
                        <div class="mb-2">
                            <img src="<?php echo SITE_URL; ?>/assets/uploads/covers/<?php echo $book['CoverImage']; ?>" 
                                 alt="Current cover" class="current-cover">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="cover" accept="image/*">
                    <small class="text-muted">Leave empty to keep current image</small>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-5">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
            
            $('#author_type').change(function() {
                if ($(this).val() === 'new') {
                    $('#existing_author').hide();
                    $('#new_author').show();
                } else {
                    $('#existing_author').show();
                    $('#new_author').hide();
                }
            });
            
            $('#genre_type').change(function() {
                if ($(this).val() === 'new') {
                    $('#existing_genre').hide();
                    $('#new_genre').show();
                } else {
                    $('#existing_genre').show();
                    $('#new_genre').hide();
                }
            });
        });
    </script>
</body>
</html>
