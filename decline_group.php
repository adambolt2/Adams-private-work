<?php
// Start the session
session_start();

require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];

// Database connection
$connection = mysqli_connect($serv, $us, $wrd, $nmedb);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the data was sent with POST method.

    // Retrieve the group ID from the POST data
    $postData = json_decode(file_get_contents("php://input"));
    $group_id = $postData->group_id;

    // Retrieve the user ID from the session
    $user_id = $_SESSION['UniqueID'];

    // Delete the group invite
    $sql = "DELETE FROM group_invite WHERE user_id = ? AND group_id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $user_id, $group_id);

    if (mysqli_stmt_execute($stmt)) {
        // Group invite was declined successfully
        echo "Group invite declined successfully";
    } else {
        // Error occurred while declining the group invite
        echo "Error declining the group invite: " . mysqli_error($connection);
    }
} else {
    // Redirect to an error page or handle the request accordingly
    header("Location: error.php");
}

// Close the database connection
mysqli_close($connection);
?>
