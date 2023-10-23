<?php
session_start();
unset($_SESSION['VerificationToken']);
unset($_SESSION['viewedID']);
require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];

$servername = $serv;
$username = $us;
$password = $wrd;
$dbname = $nmedb;

// Check if the activation token is provided in the URL

// It's time to get person ID
$the_membership_type = array("contributor", "buddy", "entrepreneur");
$all_emails = array();
$email_membership_map = array();
$user_email = $_SESSION['LoginMail'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Prepare the SQL query to select all emails from the four user type tables
foreach ($the_membership_type as $membership_type) {
    $sql = "SELECT email_address, unique_id FROM {$membership_type} WHERE email_address = ?";
    
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("s", $user_email);
    $stmt2->execute();
    $stmt2->bind_result($email, $unique_id);
    
    if ($stmt2->fetch()) {
        // Found the email address in this table, store it and its corresponding membership type
        $email_membership_map[$email] = $membership_type;
        $all_emails[] = $email;
        
        // Set the UniqueID in the session
        $_SESSION['UniqueID'] = $unique_id;
        $stmt2->close();
        // Break the loop since we found the email
        break;
    }
    
    $stmt2->close();
}

// Check if the email was found and perform actions accordingly
if (!empty($_SESSION['UniqueID'])) {
   // echo "Found email in table: " . $email_membership_map[$user_email];
   // echo "UniqueID: " . $_SESSION['UniqueID'];
} else {
    //echo "Email not found in any table.";
}

$MyID = $_SESSION['UniqueID']; // Get your ID from the session

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the JSON data from the POST body and decode it
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    // Check if JSON decoding was successful
    if ($data === null) {
        echo "Invalid JSON data";
        exit;
    }

    // Extract friend_id and content from the decoded data
    $friendId = $data->friend_id;
    $messageContent = $data->content;

    // Generate a unique message_id using bin2hex
    $messageId = bin2hex(random_bytes(6));

    // Get the current timestamp
    $timestamp = date("Y-m-d H:i:s");

    // Insert the message into the messages table with the timestamp
    $stmt = $conn->prepare("INSERT INTO messages (message_id, sender_id, reciever_id, text_message, time_stamp) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $messageId, $MyID, $friendId, $messageContent, $timestamp);
    
    if ($stmt->execute()) {
        echo "Message sent successfully";
    } else {
        echo "Failed to send message";
    }

    $stmt->close();
} else {
    // Handle non-POST requests here, if necessary
    echo "Invalid request method";
}

// Close the database connection
$conn->close();
?>
