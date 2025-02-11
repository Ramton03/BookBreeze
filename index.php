<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Fetch top 6 books by price with author information
$books = $conn->query("
    SELECT b.*, a.Name as AuthorName, g.GenreName 
    FROM BOOK b 
    JOIN AUTHOR a ON b.AuthorID = a.AuthorID 
    JOIN GENRE g ON b.GenreID = g.GenreID 
    WHERE b.Availability = TRUE 
    ORDER BY b.Price DESC 
    LIMIT 6
")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<!-- Add Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
    body {
        background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
        font-family: 'Roboto', sans-serif;
    }
    .container {
        margin-top: 50px;
    }
    .book-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s;
    }
    .book-card:hover {
        transform: scale(1.05);
    }
    .book-cover {
        height: 200px;
        object-fit: cover;
    }
    .book-info {
        padding: 15px;
    }
    .btn-primary {
        background-color: #ff7e5f;
        border: none;
    }
    .btn-primary:hover {
        background-color: #feb47b;
    }
    .quote {
        font-style: italic;
        color: #ddd;
        margin: 20px 0;
    }
    .extra-section {
        margin-top: 50px;
    }
    .extra-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s;
    }
    .extra-card:hover {
        transform: scale(1.05);
    }
    .extra-card img {
        width: 100%;
        height: 300px;
        object-fit: cover;
    }
    .extra-card-body {
        padding: 15px;
    }
</style>

<div class="container text-center">
    <h1 class="mb-4 text-white">Welcome to BookBreeze</h1>
    <p class="quote">"Books are a uniquely portable magic." - Stephen King</p>
    
    <div class="row">
        <?php foreach($books as $book): ?>
            <div class="col-md-4 mb-4">
                <div class="book-card">
                    <?php if($book['CoverImage']): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/uploads/covers/<?php echo $book['CoverImage']; ?>" 
                             alt="<?php echo $book['Title']; ?>" class="book-cover w-100">
                    <?php endif; ?>
                    
                    <div class="book-info">
                        <h5 class="book-title"><?php echo $book['Title']; ?></h5>
                        <p class="book-author">by <?php echo $book['AuthorName']; ?></p>
                        <p class="book-genre"><?php echo $book['GenreName']; ?></p>
                        <p class="book-price">$<?php echo number_format($book['Price'], 2); ?></p>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="<?php echo SITE_URL; ?>/cart/add.php">
                                <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-secondary">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <p class="quote">"So many books, so little time." - Frank Zappa</p>
    
    <div class="extra-section">
        <h2 class="text-white">Explore More</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="extra-card">
                    <img src="https://i.ibb.co.com/dbhBkXr/wp5274721-rabindranath-tagore-wallpapers-1-1.jpg" alt="Rabindranath Tagore">
                    <div class="extra-card-body">
                        <h5 class="extra-card-title">Discover New Genres</h5>
                        <p class="extra-card-text">"The pages of a book hold the power to transport us to new worlds." - Rabindranath Tagore</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="extra-card">
                    <img src="https://i.ibb.co.com/LQxHRqb/9180753.jpg" alt="Placeholder Image">
                    <div class="extra-card-body">
                        <h5 class="extra-card-title">Meet Your Favorite Authors</h5>
                        <p class="extra-card-text">"Books are the mirrors of the soul." - Kazi Nazrul Islam</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="extra-card">
                    <img src="https://i.ibb.co.com/P9J8Ckh/Chattopadhyay-Sharat-Chandra.jpg" alt="Placeholder Image">
                    <div class="extra-card-body">
                        <h5 class="extra-card-title">Join Our Community</h5>
                        <p class="extra-card-text">"Reading is a conversation. All books talk. But a good book listens as well." - Sarat Chandra Chattopadhyay</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
