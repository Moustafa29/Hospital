<?php
session_start();

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
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$date_time = isset($_POST['datetime']) ? $_POST['datetime'] : '';

if (empty($name) || empty($email) || empty($date_time)) {
    die("Error: Name, email, and date/time are required.");
}

// Parse and format the date and time
$formatted_date_time = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date_time))); // Convert slashes to dashes 

// Generate a unique appointment_id
$appointment_id = generateUniqueRandomString($conn, 50);

// Prepare and bind SQL statement
$stmt = $conn->prepare("INSERT INTO appointment (name, email, appointment_id, date_time) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $appointment_id, $formatted_date_time); // Use the formatted date and time

// Execute the statement
if ($stmt->execute()) {
    // Display a JavaScript alert and redirect to appointment.html
    echo "<script>alert('Appointment successfully submitted'); window.location='appointment.html';</script>";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Function to generate a unique random string for appointment_id
function generateUniqueRandomString($conn, $length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    do {
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM appointment WHERE appointment_id = ?");
        $stmt_check->bind_param("s", $randomString);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
    } while ($row_check['count'] > 0);

    return $randomString;
}
?>