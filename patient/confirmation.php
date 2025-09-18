<?php
// Start the session to access user data
session_start();

// Check if the scheduleid is provided in the URL
if (!isset($_GET['scheduleid']) || empty($_GET['scheduleid'])) {
    die("Schedule ID not provided in the URL.");
} else {
    $scheduleid = $_GET['scheduleid'];
}

// Include the database connection
include("../connection.php");  // Adjust the path if necessary

// Check if the connection was successful
if (!$database) {
    die("Database connection failed: " . $database->connect_error);
}

// Get user details from the session
$useremail = $_SESSION["user"];
$sqlmain = "SELECT * FROM patient WHERE pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$userfetch = $result->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];

// Fetch the appointment details based on the scheduleid
$sql = "SELECT appointment.appodate, appointment.appotime, doctor.docname 
        FROM appointment 
        INNER JOIN doctor ON appointment.docid = doctor.docid
        WHERE appointment.scheduleid = ? 
        ORDER BY appointment.appoid DESC LIMIT 1";
$stmt = $database->prepare($sql);
$stmt->bind_param("i", $scheduleid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the appointment details
    $appointment = $result->fetch_assoc();
    $appointmentDate = $appointment['appodate'];
    $appointmentTime = $appointment['appotime'];
    $doctorName = $appointment['docname'];
} else {
    echo "No appointment found for this schedule.";
    exit();
}

// Add a small delay before redirecting (optional)
header("refresh:5;url=appointment.php");  // Redirects after 5 seconds
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <title>Appointment Confirmation</title>
</head>
<body>

    <div class="container">
        <div class="menu">
            <!-- Include your menu content here -->
        </div>

        <div class="confirmation-body">
            <h2>Appointment Confirmation</h2>
            <p>Dear <?php echo htmlspecialchars($username); ?>,</p>
            <p>Your appointment has been successfully booked.</p>
            
            <h3>Appointment Details:</h3>
            <table>
                <tr>
                    <td><strong>Doctor:</strong></td>
                    <td><?php echo htmlspecialchars($doctorName); ?></td>
                </tr>
                <tr>
                    <td><strong>Appointment Date:</strong></td>
                    <td><?php echo htmlspecialchars($appointmentDate); ?></td>
                </tr>
                <tr>
                    <td><strong>Appointment Time:</strong></td>
                    <td><?php echo htmlspecialchars($appointmentTime); ?></td>
                </tr>
            </table>

            <p>Thank you for using our service. We look forward to seeing you!</p>

            <p>You will be redirected to your appointments page shortly.</p>
        </div>
    </div>

</body>
</html>
