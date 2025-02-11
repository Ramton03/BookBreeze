<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = (int)$_POST['book_id'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id]++;
    } else {
        $_SESSION['cart'][$book_id] = 1;
    }
    
    header("Location: " . SITE_URL . "/cart/view.php");
    exit();
}
?>
