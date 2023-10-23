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

    // Update the group invite status to "accepted"
    $sql = "UPDATE group_invite SET invite_status = 'accepted' WHERE user_id = ? AND group_id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $user_id, $group_id);

    if (mysqli_stmt_execute($stmt)) {
        // Group invite was accepted successfully
        echo "Group invite accepted successfully";

        // Insert a new row into group_members
        $insertSql = "INSERT INTO group_members (group_id, user_id, privs, time_stamp) VALUES (?, ?, 'member', NOW())";
        $insertStmt = mysqli_prepare($connection, $insertSql);
        mysqli_stmt_bind_param($insertStmt, "ss", $group_id, $user_id);

        if (mysqli_stmt_execute($insertStmt)) {
            // Inserted into group_members successfully
        } else {
            // Error occurred while inserting into group_members
        }
    } else {
        // Error occurred while accepting the group invite
        echo "Error accepting the group invite: " . mysqli_error($connection);
    }
} else {
    // Redirect to an error page or handle the request accordingly
    header("Location: error.php");
}

// Close the database connection
mysqli_close($connection);
?>
