<?php
// Allow POST requests from other domains (useful for API access from frontend)
header('Access-Control-Allow-Origin: http://localhost:5173');

// Allow credentials (cookies, HTTP auth, etc.)
header('Access-Control-Allow-Credentials: true');

// Allow specific HTTP methods (POST, GET, OPTIONS)
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

// Allow specific headers
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
    if (empty($email) || empty($pass)) {
        echo json_encode(["status" => "error", "message" => "Email and password are required."]);
        exit;
    }

    // Validate email format using regex
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
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
            echo json_encode(["status" => "success", "message" => "Login successful."]);
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
