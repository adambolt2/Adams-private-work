<?php
session_start();

require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];

$servername = $serv;
$username = $us;
$password = $wrd;
$dbname = $nmedb;

$results = [];

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$Uniqueid = $_SESSION["UniqueID"];

// Prepare the SQL query
$sql = "SELECT group_id, creator_id, group_title, group_desc, group_img, time_stamp FROM group_create WHERE creator_id = ?";

// Create a prepared statement
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    // Bind the parameter
    mysqli_stmt_bind_param($stmt, "s", $Uniqueid);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // Get the results
        $result = mysqli_stmt_get_result($stmt);

        // Fetch and store the results in the array
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }

        // Close the result and the statement
        mysqli_free_result($result);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error executing the query: ' . mysqli_error($conn)]);
    }

    // Close the prepared statement
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Error preparing the statement: ' . mysqli_error($conn)]);
}

// Return JSON data
echo json_encode(['success' => true, 'groups' => $results]);
?>
