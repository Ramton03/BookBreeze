<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = (int)$_POST['book_id'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_POST['update'])) {
        $quantity = (int)$_POST['quantity'];
        if ($quantity > 0) {
            $_SESSION['cart'][$book_id] = $quantity;
        }
    } elseif (isset($_POST['remove'])) {
        unset($_SESSION['cart'][$book_id]);
    } elseif (isset($_POST['remove_all'])) {
        $_SESSION['cart'] = [];
    }
    
    header("Location: " . SITE_URL . "/cart/view.php");
    exit();
}
?>
