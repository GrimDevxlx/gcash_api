<?php
// Handle callback from GCash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && isset($data['transaction_id'])) {
        $transaction_id = $data['transaction_id'];
        $status = $data['status'];  // Could be 'success' or 'failed'

        // Update transaction status in the database
        updateTransactionStatus($transaction_id, $status);
    }
}

// to update transaction status in MySQL
function updateTransactionStatus($transaction_id, $status) {
    // Connect to the database
    $mysqli = new mysqli("localhost", "your-db-username", "your-db-password", "your-db-name");

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Prepare & bind
    $stmt = $mysqli->prepare("UPDATE gcash_transactions SET status = ? WHERE transaction_id = ?");
    $stmt->bind_param("ss", $status, $transaction_id);

    // Execute statement
    $stmt->execute();

    // Close connection
    $stmt->close();
    $mysqli->close();
}
?>
