<?php

//GCASH API Credentials 'Paymonggo'

  $apiURL = ""; //REPLACE WITH GCASH API URL 
  $apiKey = ""; // REPLACE API KEY 
  $secretKey = ""; //REPLACE WITH YOUR SECRET KEY

  // Function to create the payment request
function createPaymentRequest($amount, $transaction_id) {
  global $apiUrl, $apiKey, $secretKey;

  // Data to send in the request (e.g., payment amount, transaction ID, etc.)
  $data = [
      'transaction_id' => $transaction_id,
      'amount' => $amount,
      'currency' => 'PHP',
      'callback_url' => '', // Replace with your callback URL
      'api_key' => $apiKey,
  ];

  // Initialize cURL session
  $ch = curl_init();

  // Set cURL options
  curl_setopt($ch, CURLOPT_URL, $apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

  // Execute cURL request and capture response
  $response = curl_exec($ch);
  $error = curl_error($ch);

  // Close cURL session
  curl_close($ch);

  if ($error) {
      return ['error' => $error];
  }

  // Parse the response
  $responseData = json_decode($response, true);

  // Handle response (e.g., save to database, check for errors)
  if (isset($responseData['status']) && $responseData['status'] == 'success') {
      // Store transaction details in MySQL
      saveTransactionToDatabase($transaction_id, $amount, 'pending');
      return ['status' => 'success', 'data' => $responseData];
  } else {
      return ['status' => 'error', 'message' => $responseData['message']];
  }
}

// Function to save transaction details in MySQL
function saveTransactionToDatabase($transaction_id, $amount, $status) {
  // Connect to the database
  $mysqli = new mysqli("localhost", "your-db-username", "your-db-password", "your-db-name");

  // Check connection
  if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
  }

  // Prepare and bind
  $stmt = $mysqli->prepare("INSERT INTO gcash_transactions (transaction_id, amount, status) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $transaction_id, $amount, $status);

  // Execute statement
  $stmt->execute();

  // Close connection
  $stmt->close();
  $mysqli->close();
}

// Example usage
$transaction_id = uniqid('gcash_');
$amount = 500; // Amount in PHP
$response = createPaymentRequest($amount, $transaction_id);

echo json_encode($response);

?>