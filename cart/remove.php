<?php
require '../includes/auth.php';
if (isset($_GET['id']) && isset($_SESSION['cart'][$_GET['id']])) {
    unset($_SESSION['cart'][$_GET['id']]);
    $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
header('Location: view.php');
exit;
?>
