<?php
// Start session and validate if the user is logged in
session_start();

if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];

// Include database connection
include("../connection.php");

// Fetch user details from the database
$sqlmain = "SELECT * FROM patient WHERE pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$userfetch = $result->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
    <title>Book Appointment</title>
</head>

<body>

    <?php
    // Check if schedule ID is passed in the URL
    if (isset($_GET["id"])) {
        $scheduleid = $_GET["id"];

        // Get schedule details
        $sqlmain = "SELECT schedule.*, doctor.docname, doctor.docemail FROM schedule 
                    INNER JOIN doctor ON schedule.docid = doctor.docid 
                    WHERE schedule.scheduleid = ? ORDER BY schedule.scheduledate DESC";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("i", $scheduleid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $scheduletime = $row["scheduletime"];
            $scheduledate = $row["scheduledate"];
            $docname = $row["docname"];
            $docemail = $row["docemail"];
        } else {
            echo "Schedule not found.";
            exit();
        }

        // Get the last appointment for the given schedule (if any)
        $sql2 = "SELECT appodate, apponum FROM appointment WHERE scheduleid = ? ORDER BY appodate DESC, apponum DESC LIMIT 1";
        $stmt2 = $database->prepare($sql2);
        $stmt2->bind_param("i", $scheduleid);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0) {
            $last_appointment = $result2->fetch_assoc();
            $last_appointment_date = $last_appointment['appodate'];
            $last_appointment_num = $last_appointment['apponum'];

            // If the last appointment is on the same date, increment time by 10 minutes
            if ($last_appointment_date == $scheduledate) {
                $new_time = date('H:i:s', strtotime($scheduletime . ' + ' . ($last_appointment_num * 10) . ' minutes'));
                $apponum = $last_appointment_num + 1;
            } else {
                // Use the original schedule time for the first appointment of the day
                $new_time = $scheduletime;
                $apponum = 1;
            }
        } else {
            // No previous appointments, use the original schedule time
            $new_time = $scheduletime;
            $apponum = 1;
        }

        // Process the appointment booking
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Insert the new appointment with time
            $sql_insert = "INSERT INTO appointment (pid, apponum, scheduleid, appodate, appotime) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $database->prepare($sql_insert);
            $stmt_insert->bind_param("iiiss", $userid, $apponum, $scheduleid, $scheduledate, $new_time);
            $stmt_insert->execute();

            // Redirect to confirmation page after successful booking
            header("Location: appointment.php");
            exit();
        }
    } else {
        echo "No schedule ID provided.";
        exit();
    }
    ?>

    <div class="container">
        <div class="menu">
            <!-- Menu content (same as before) -->
        </div>

        <div class="dash-body">
            <h2>Book Your Appointment</h2>
            <form action="booking.php?id=<?php echo $scheduleid; ?>" method="POST">
                <table>
                    <tr>
                        <td>Doctor Name:</td>
                        <td><b><?php echo $docname; ?></b></td>
                    </tr>
                    <tr>
                        <td>Doctor Email:</td>
                        <td><b><?php echo $docemail; ?></b></td>
                    </tr>
                    <tr>
                        <td>Session Date:</td>
                        <td><b><?php echo $scheduledate; ?></b></td>
                    </tr>
                    <tr>
                        <td>Session Time:</td>
                        <td><b><?php echo $new_time; ?></b></td>
                    </tr>
                    <tr>
                        <td>Channeling Fee:</td>
                        <td><b>LKR.2,000.00</b></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="submit" value="Book Now" class="btn-primary">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>

</body>
</html>
