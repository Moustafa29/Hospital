<?php
session_start();

// Connect to  MySQL database
$servername = "localhost"; 
$username = "root";
$password = ""; 
$dbname = "myhospital"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize form data
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    die("Error: Email and password are required.");
}

// Check if the user exists in signup_user table
$stmt_check = $conn->prepare("SELECT signup_id FROM signup_user WHERE email = ?");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // User found, generate random login_id
    function generateUniqueRandomNumber($conn)
    {
        do {
            $login_id = generateRandomString(50);
            $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM login_user WHERE login_id = ?");
            $stmt_check->bind_param("s", $login_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $row_check = $result_check->fetch_assoc();
        } while ($row_check['count'] > 0);

        return $login_id;
    }

    $login_id = generateUniqueRandomNumber($conn); // Generates a random 50-character string

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into login_user table
    $stmt_insert = $conn->prepare("INSERT INTO login_user (login_id, email, password) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $login_id, $email, $hashed_password);

    if ($stmt_insert->execute()) {
        // Get the signup_id of the user
        $row = $result_check->fetch_assoc();
        $_SESSION['signup_id'] = $row['signup_id'];
        header("Location: home.html"); // Redirect to home page after successful login
        exit();
    } else {
        echo "Error: " . $stmt_insert->error;
    }
} else {
    // User not found, display alert
    echo '<script>alert("User not found. Please sign up."); window.location.href = "login.html";</script>';
    exit();
}

// Close statements and connection
$stmt_check->close();
$conn->close();

// Function to generate a random string
function generateRandomString($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>