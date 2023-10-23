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
// Check if the activation token is provided in the URL

// its time to get person ID
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
$friend = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the JSON data from the request body and decode it
    $requestData = json_decode(file_get_contents("php://input"), true);

    if ($requestData && isset($requestData["friend_id"])) {
        $friend = $requestData["friend_id"];
    }
}


// Assuming $conn is your database connection

if ($friend !== null) {
    // Update the messages that match the criteria
    $sql = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND reciever_id = ? AND is_read = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $friend, $MyID);
    $stmt->execute();
    $stmt->close();
    
    // Output the number of messages updated
    $numUpdated = $conn->affected_rows;
    echo "Updated $numUpdated messages as read.";
}
