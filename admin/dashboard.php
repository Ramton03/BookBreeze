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

// Fetch user statistics
$stmt = $conn->prepare("SELECT Role, COUNT(*) as count FROM USER GROUP BY Role");
$stmt->execute();
$userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminCount = 0;
$userCount = 0;
foreach ($userStats as $stat) {
    if ($stat['Role'] == 'Admin') {
        $adminCount = $stat['count'];
    } elseif ($stat['Role'] == 'User') {
        $userCount = $stat['count'];
    }
}

// Fetch book statistics
$stmt = $conn->prepare("SELECT g.GenreName, COUNT(b.BookID) as count 
                        FROM BOOK b 
                        JOIN GENRE g ON b.GenreID = g.GenreID 
                        GROUP BY g.GenreName 
                        ORDER BY count DESC 
                        LIMIT 2");
$stmt->execute();
$genreStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$genres = [];
$genreCounts = [];
foreach ($genreStats as $stat) {
    $genres[] = $stat['GenreName'];
    $genreCounts[] = $stat['count'];
}

include '../includes/header.php';
?>

<!-- Include Bootstrap and FontAwesome via jsDelivr -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mt-5">
    <h1 class="text-center mb-4">Admin Dashboard</h1>
    
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Manage Books</h5>
                    <p class="card-text">View, edit, and manage all books in the database.</p>
                    <a href="manage-books.php" class="btn btn-primary">View Books</a>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-plus-circle fa-3x mb-3 text-success"></i>
                    <h5 class="card-title">Add New Book</h5>
                    <p class="card-text">Quickly add a new book to the library database.</p>
                    <a href="add-book.php" class="btn btn-success">Add Book</a>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-users fa-3x mb-3 text-warning"></i>
                    <h5 class="card-title">Manage Users</h5>
                    <p class="card-text">View and manage user accounts and permissions.</p>
                    <a href="manage-users.php" class="btn btn-warning">View Users</a>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-user-edit fa-3x mb-3 text-info"></i>
                    <h5 class="card-title">Manage Authors</h5>
                    <p class="card-text">View, add, and remove authors.</p>
                    <a href="manage-authors.php" class="btn btn-info">View Authors</a>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-tags fa-3x mb-3 text-secondary"></i>
                    <h5 class="card-title">Manage Genres</h5>
                    <p class="card-text">View, add, and remove genres.</p>
                    <a href="manage-genres.php" class="btn btn-secondary">View Genres</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">User Statistics</h5>
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Book Statistics</h5>
                    <canvas id="bookChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // User Statistics Chart
    var ctx = document.getElementById('userChart').getContext('2d');
    var userChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Admins', 'Users'],
            datasets: [{
                label: '# of Users',
                data: [<?php echo $adminCount; ?>, <?php echo $userCount; ?>],
                backgroundColor: ['#ffc107', '#007bff'],
                borderColor: ['#ffc107', '#007bff'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Book Statistics Chart
    var ctx2 = document.getElementById('bookChart').getContext('2d');
    var bookChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($genres); ?>,
            datasets: [{
                label: '# of Books',
                data: <?php echo json_encode($genreCounts); ?>,
                backgroundColor: ['#28a745', '#dc3545'],
                borderColor: ['#28a745', '#dc3545'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
</script>
