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

// Handle role updates
if (isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'];
    
    try {
        $stmt = $conn->prepare("UPDATE USER SET Role = ? WHERE UserID = ?");
        $stmt->execute([$new_role, $user_id]);
        $success = "User role updated successfully";
    } catch (PDOException $e) {
        $error = "Failed to update user role";
    }
}

// Handle user removal
if (isset($_POST['remove_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM PROFILE WHERE UserID = ?");
        $stmt->execute([$user_id]);
        $stmt = $conn->prepare("DELETE FROM USER WHERE UserID = ?");
        $stmt->execute([$user_id]);
        $success = "User removed successfully";
    } catch (PDOException $e) {
        $error = "Failed to remove user";
    }
}

// Fetch all users except current admin
$stmt = $conn->prepare("
    SELECT u.*, p.City, p.PostCode, p.Pic 
    FROM USER u 
    LEFT JOIN PROFILE p ON u.UserID = p.UserID 
    WHERE u.UserID != ?
");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Users</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Profile Picture</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="text-center" style="width: 100px;">
                            <?php if ($user['Pic']): ?>
                                <img src="<?php echo SITE_URL; ?>/<?php echo $user['Pic']; ?>" 
                                     alt="<?php echo $user['Name']; ?>" 
                                     class="img-fluid rounded-circle" style="max-height: 100px;">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/profile/default.png" 
                                     alt="Default Profile Picture" 
                                     class="img-fluid rounded-circle" style="max-height: 100px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['Name']; ?></td>
                        <td><?php echo $user['Email']; ?></td>
                        <td><?php echo $user['Phone']; ?></td>
                        <td>
                            <?php 
                            if ($user['City'] && $user['PostCode']) {
                                echo $user['City'] . ', ' . $user['PostCode'];
                            } else {
                                echo 'Not provided';
                            }
                            ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                <select name="role" onchange="this.form.submit()" class="form-select form-select-sm">
                                    <option value="User" <?php echo $user['Role'] == 'User' ? 'selected' : ''; ?>>User</option>
                                    <option value="Admin" <?php echo $user['Role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="update_role" value="1">
                            </form>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                <button type="submit" name="remove_user" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Include Bootstrap via jsDelivr -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
