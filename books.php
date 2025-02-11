<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Get search parameters
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'title';
$search_query = isset($_GET['query']) ? sanitize($_GET['query']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'title_asc';

// Build the query
$query = "
    SELECT b.*, a.Name as AuthorName, g.GenreName 
    FROM BOOK b 
    JOIN AUTHOR a ON b.AuthorID = a.AuthorID 
    JOIN GENRE g ON b.GenreID = g.GenreID 
    WHERE b.Availability = TRUE
";

// Add search conditions
if ($search_query) {
    switch ($search_type) {
        case 'isbn':
            $query .= " AND b.ISBN LIKE :search";
            break;
        case 'author':
            $query .= " AND a.Name LIKE :search";
            break;
        case 'genre':
            $query .= " AND g.GenreName LIKE :search";
            break;
        default: // title
            $query .= " AND b.Title LIKE :search";
    }
}

// Add sorting
switch ($sort_by) {
    case 'price_asc':
        $query .= " ORDER BY b.Price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY b.Price DESC";
        break;
    case 'title_desc':
        $query .= " ORDER BY b.Title DESC";
        break;
    case 'author':
        $query .= " ORDER BY a.Name ASC";
        break;
    default: // title_asc
        $query .= " ORDER BY b.Title ASC";
}

$stmt = $conn->prepare($query);

if ($search_query) {
    $stmt->bindValue(':search', '%' . $search_query . '%');
}

$stmt->execute();
$books = $stmt->fetchAll();

// Fetch all genres for filter
$genres = $conn->query("SELECT DISTINCT GenreName FROM GENRE ORDER BY GenreName")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<!-- Add required CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
    .search-section {
        background: rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 10px;
        backdrop-filter: blur(10px);
        margin-bottom: 30px;
    }
    
    .book-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
        padding: 20px;
    }
    
    .book-card {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease-in;
    }
    
    .book-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }
    
    .book-cover {
        height: 300px;
        width: 100%;
        object-fit: cover;
        transition: all 0.3s ease;
    }
    
    .book-cover:hover {
        transform: scale(1.05);
    }
    
    .no-cover {
        height: 300px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-style: italic;
        color: #666;
    }
    
    .book-info {
        padding: 20px;
    }
    
    .search-form select,
    .search-form input {
        border-radius: 20px;
        border: none;
        padding: 10px 20px;
        background: rgba(255, 255, 255, 0.9);
    }
    
    .search-form button {
        border-radius: 20px;
        padding: 10px 25px;
        transition: all 0.3s ease;
    }
    
    .search-form button:hover {
        transform: scale(1.05);
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container">
    <div class="search-section animate__animated animate__fadeIn">
        <form class="search-form" method="GET">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <select name="search_type" class="form-select">
                        <option value="title" <?php echo $search_type == 'title' ? 'selected' : ''; ?>>Title</option>
                        <option value="isbn" <?php echo $search_type == 'isbn' ? 'selected' : ''; ?>>ISBN</option>
                        <option value="author" <?php echo $search_type == 'author' ? 'selected' : ''; ?>>Author</option>
                        <option value="genre" <?php echo $search_type == 'genre' ? 'selected' : ''; ?>>Genre</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="query" class="form-control" 
                           value="<?php echo htmlspecialchars($search_query); ?>" 
                           placeholder="Search...">
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="title_asc" <?php echo $sort_by == 'title_asc' ? 'selected' : ''; ?>>Title (A-Z)</option>
                        <option value="title_desc" <?php echo $sort_by == 'title_desc' ? 'selected' : ''; ?>>Title (Z-A)</option>
                        <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="author" <?php echo $sort_by == 'author' ? 'selected' : ''; ?>>Author</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>
    </div>

    <div class="book-grid">
        <?php foreach($books as $book): ?>
            <div class="book-card animate__animated animate__fadeIn">
                <?php if($book['CoverImage']): ?>
                    <img src="<?php echo SITE_URL; ?>/assets/uploads/covers/<?php echo $book['CoverImage']; ?>" 
                         alt="<?php echo $book['Title']; ?>" class="book-cover">
                <?php else: ?>
                    <div class="no-cover">No Cover Available</div>
                <?php endif; ?>
                
                <div class="book-info">
                    <h5 class="book-title"><?php echo $book['Title']; ?></h5>
                    <p class="book-author">by <?php echo $book['AuthorName']; ?></p>
                    <p class="book-genre"><?php echo $book['GenreName']; ?></p>
                    <p class="book-isbn">ISBN: <?php echo $book['ISBN']; ?></p>
                    <p class="book-price">$<?php echo number_format($book['Price'], 2); ?></p>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form method="POST" action="<?php echo SITE_URL; ?>/cart/add.php">
                            <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                            <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-secondary w-100">Login to Purchase</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Add smooth loading animation when filtering/sorting
document.querySelectorAll('select, input').forEach(element => {
    element.addEventListener('change', () => {
        document.querySelector('.book-grid').style.opacity = '0.5';
    });
});
</script>
