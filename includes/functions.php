
<?php
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . SITE_URL . "/auth/login.php");
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

function uploadImage($file) {
    $target_dir = UPLOAD_DIR;
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return basename($file["name"]);
    }
    return false;
}
?>
