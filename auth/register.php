<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postcode = $_POST['postcode'];
    
    // Handle profile picture upload
    $pic = null;
    if (isset($_FILES['pic']) && $_FILES['pic']['error'] == 0) {
        $pic = 'assets/profile/' . basename($_FILES['pic']['name']);
        if (!move_uploaded_file($_FILES['pic']['tmp_name'], '../' . $pic)) {
            $error = "Failed to upload profile picture.";
        }
    }

    if (!$error) {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check both email and phone
        $stmt = $conn->prepare("SELECT Email, Phone FROM USER WHERE Email = ? OR Phone = ?");
        $stmt->execute([$email, $phone]);
        $existingUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $errors = [];
        foreach ($existingUser as $user) {
            if ($user['Email'] === $email) {
                $errors[] = "Email is already registered";
            }
            if ($user['Phone'] === $phone) {
                $errors[] = "Phone number is already registered";
            }
        }
        
        if (empty($errors)) {
            try {
                // Insert into USER table
                $stmt = $conn->prepare("INSERT INTO USER (Name, Email, Phone, Password, Role) VALUES (?, ?, ?, ?, 'User')");
                $stmt->execute([$name, $email, $phone, $password]);
                $user_id = $conn->lastInsertId();

                // Insert into PROFILE table
                $stmt = $conn->prepare("INSERT INTO PROFILE (UserID, DOB, Address, City, PostCode, Pic) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $dob, $address, $city, $postcode, $pic]);

                $success = "Registration successful! You can now login.";
            } catch(PDOException $e) {
                $error = "Registration failed. Please try again.";
            }
        } else {
            $error = implode("<br>", $errors);
        }
    }
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('../assets/other/library-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 5rem;
            max-width: 400px;
        }
        .form-floating { margin-bottom: 1rem; }
        .btn-register { 
            padding: 0.8rem;
            font-weight: 500;
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
<body class="d-flex flex-column min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="register-container">
                    <h2 class="text-center mb-4">Create Account</h2>
                    
                    <?php if($error): ?>
                        <div class="error-message">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="success-message">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="name" name="name" 
                                placeholder="Your Name" required>
                            <label for="name">Full Name</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" 
                                placeholder="name@example.com" required>
                            <label for="email">Email address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                placeholder="Phone Number" required>
                            <label for="phone">Phone Number</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" 
                                placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="dob" name="dob" required>
                            <label for="dob">Date of Birth</label>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="address" name="address" placeholder="Address" required></textarea>
                            <label for="address">Address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="city" name="city" placeholder="City" required>
                            <label for="city">City</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Post Code" required>
                            <label for="postcode">Post Code</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="file" class="form-control" id="pic" name="pic" required>
                            <label for="pic">Profile Picture</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-register mb-3">
                            Create Account
                        </button>

                        <div class="text-center">
                            <p class="mb-2">
                                Already have an account? 
                                <a href="login.php" class="text-decoration-none">Login here</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
