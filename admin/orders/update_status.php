<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
checkAdminAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($orderId && $status) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        $_SESSION['success'] = "Statut de la commande mis à jour.";
    } else {
        $_SESSION['error'] = "Données invalides.";
    }
    header('Location: view.php?id=' . $orderId);
    exit;
}
?>