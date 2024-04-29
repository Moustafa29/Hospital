<?php
// Connect to MySQL database
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
$fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($fullname) || empty($email) || empty($password)) {
    die("Error: All fields are required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format.");
}

// Check if the email is already in use
$stmt_check_email = $conn->prepare("SELECT COUNT(*) as count FROM signup_user WHERE email = ?");
$stmt_check_email->bind_param("s", $email);
$stmt_check_email->execute();
$result_check_email = $stmt_check_email->get_result();
$row_check_email = $result_check_email->fetch_assoc();

if ($row_check_email['count'] > 0) {
    // Email already in use, display alert
    echo '<script>alert("This email is already used."); window.location.href = "signup.html";</script>';
    exit();
}

// Generate a unique random number
$signup_id = generateUniqueRandomNumber($conn);

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare and bind SQL statement
$stmt = $conn->prepare("INSERT INTO signup_user (signup_id, name, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $signup_id, $fullname, $email, $hashed_password);

// Execute the statement
if ($stmt->execute()) {
    header("Location: home.html");
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Function to generate a unique random number
function generateUniqueRandomNumber($conn)
{
    do {
        $signup_id = generateRandomString(50);
        $stmt_check_signup_id = $conn->prepare("SELECT COUNT(*) as count FROM signup_user WHERE signup_id = ?");
        $stmt_check_signup_id->bind_param("s", $signup_id);
        $stmt_check_signup_id->execute();
        $result_check_signup_id = $stmt_check_signup_id->get_result();
        $row_check_signup_id = $result_check_signup_id->fetch_assoc();
    } while ($row_check_signup_id['count'] > 0);

    return $signup_id;
}

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