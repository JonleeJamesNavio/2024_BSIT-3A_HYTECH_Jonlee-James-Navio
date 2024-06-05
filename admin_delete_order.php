<?php
session_start();
include 'connection.php';

$order_id = $_GET['order_id'];

// Start a transaction
$conn->begin_transaction();

try {
    // Delete the related gcash payments
    $delete_gcash_payments_sql = "DELETE FROM gcash_payments WHERE order_id = ?";
    $stmt = $conn->prepare($delete_gcash_payments_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Delete the related order items
    $delete_order_items_sql = "DELETE FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($delete_order_items_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Delete the order
    $delete_order_sql = "DELETE FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($delete_order_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Commit the transaction
    $conn->commit();
    $_SESSION['message'] = "Order deleted successfully.";
} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    $_SESSION['error'] = "Error deleting order: " . $exception->getMessage();
}

// Redirect to admin_orders.php
header("Location: admin_orders.php");
exit();
?>
