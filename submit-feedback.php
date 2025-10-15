<?php
// 1. DATABASE CREDENTIALS - REPLACE WITH YOURS
$servername = "localhost"; // This is usually correct for Hostinger
$dbname     = "feedback_db"; // Your database name from hPanel
$username   = "root";     // Your username from hPanel
$password   = ""; // Your password from hPanel

// 2. RECEIVE DATA FROM FRONTEND
// Get the raw JSON data sent from the JavaScript fetch
$json_data = file_get_contents('php://input');
// Decode the JSON into a PHP object
$data = json_decode($json_data);

// Set headers to respond with JSON
header('Content-Type: application/json');

// 3. VALIDATE DATA (Basic Validation)
if (!isset($data->rating)) {
    echo json_encode(['status' => 'error', 'message' => 'Rating is required.']);
    exit;
}

// 4. CONNECT TO THE DATABASE AND INSERT DATA
try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Prepare an SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO feedback (rating, likes, issues, name, email, gender, phone, remarks, agreedToOffers, agreedToTerms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Get values from the data, using null if they don't exist
    $rating = $data->rating;
    $likes = isset($data->likes) ? json_encode($data->likes) : null;
    $issues = isset($data->issues) ? json_encode($data->issues) : null;
    $name = $data->details->name ?? null;
    $email = $data->details->email ?? null;
    $gender = $data->details->gender ?? null;
    $phone = $data->details->phone ?? null;
    $remarks = $data->details->remarks ?? null;
    $agreedToOffers = $data->details->agreedToOffers ?? false;
    $agreedToTerms = $data->details->agreedToTerms ?? false;

    // Bind parameters to the statement
    $stmt->bind_param("isssssssii", $rating, $likes, $issues, $name, $email, $gender, $phone, $remarks, $agreedToOffers, $agreedToTerms);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Feedback saved successfully!']);
    } else {
        throw new Exception("Error saving feedback: " . $stmt->error);
    }

    // Close the connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // If anything goes wrong, send back an error
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>