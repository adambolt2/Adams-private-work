<?php
session_start();

require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];


$data = json_decode(file_get_contents("php://input"));

if (isset($data->groupid) && isset($data->message)) {
    // Extract the group ID and message text
    $groupid = $data->groupid;
    $message = $data->message;

    // Generate a random message ID (6 random bytes as bin2hex)
    $message_id = bin2hex(random_bytes(6));

    // Get the user ID from the session
    $user_id = $_SESSION['UniqueID'];

    // Get the current timestamp
    $timestamp = date("Y-m-d H:i:s");

    // Create a connection to the database
    $conn = new mysqli($serv, $us, $wrd, $nmedb);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL statement for inserting the message
    $stmt = $conn->prepare("INSERT INTO group_messages (group_id, user_id, message_id, message, time_stamp) VALUES (?, ?, ?, ?, ?)");

    // Bind parameters
    $stmt->bind_param("sssss", $groupid, $user_id, $message_id, $message, $timestamp);

    // Execute the statement
    if ($stmt->execute()) {
        // Message sent successfully
        echo json_encode(['success' => true]);
    } else {
        // Message sending failed
        echo json_encode(['success' => false]);
    }

    // Close the database connection
    $conn->close();
} else {
    // Incomplete or invalid data received
    echo json_encode(['success' => false]);
}

?>