<?php
// Include JWT library (ensure that you have installed firebase/php-jwt using Composer)
require_once 'vendor/autoload.php'; // Path to Composer autoload file

use \Firebase\JWT\JWT;

// Define your secret key (store it securely)
$secret_key = "YOUR_SECRET_KEY";

// Set headers for CORS
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // MySQL database connection
    $servername = "localhost";
    $username = "root"; // your MySQL username
    $password = "Odkanhe@2003"; // your MySQL password
    $dbname = "kavaavi_education";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$conn) {
        die(json_encode(["status" => "error", "message" => "Connection failed: " . mysqli_connect_error()]));
    }

    // Get the JSON body from the request
    $data = json_decode(file_get_contents("php://input"), true);

    // Ensure form fields are set before accessing them
    $email = isset($data['email']) ? $data['email'] : '';
    $pass = isset($data['password']) ? $data['password'] : '';

    // Validate form fields
    if (empty($email)) {
        echo json_encode(["status" => "error", "message" => "Email is required."]);
        exit;
    }

    if (empty($pass)) {
        echo json_encode(["status" => "error", "message" => "Password is required."]);
        exit;
    }

    // Validate email format using regex
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    // Check if the email exists in the database
    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Fetch user data
        $user_data = mysqli_fetch_assoc($result);

        // Verify the entered password with the hashed password in the database
        if (password_verify($pass, $user_data['password'])) {
            // Generate JWT token on successful login
            $issued_at = time();
            $expiration_time = $issued_at + (60 * 60);  // Token expires in 1 hour
            $payload = array(
                "iat" => $issued_at,
                "exp" => $expiration_time,
                "email" => $email,
                "user_id" => $user_data['id']
            );

            // Generate the token
            $jwt = JWT::encode($payload, $secret_key, 'HS256');

            // Send the response with the JWT
            echo json_encode([
                "status" => "success",
                "message" => "Login successful.",
                "token" => $jwt
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No user found with this email."]);
    }

    // Close connection
    mysqli_close($conn);
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed."]);
}
