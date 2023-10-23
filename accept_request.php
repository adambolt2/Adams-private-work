<?php
// accept_request.php
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

$conn = new mysqli($servername, $username, $password, $dbname);
// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the JSON data from the request body and decode it
    $requestData = json_decode(file_get_contents("php://input"), true);

    if ($requestData && isset($requestData["sender_id"])) {
        $senderId = $requestData["sender_id"];
        $recieverID =  $_SESSION['UniqueID'];
        // Echo the sender_id for testing purposes
        $updateQuery = "UPDATE request SET status = 'accepted' WHERE sender_id = ? AND reciever_id = ? ";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ss", $senderId, $recieverID);
        $updateStmt->execute();







        echo "Received sender_id: " . $senderId;
        echo "Received reciever_id: " . $recieverID;

        $friendship_id1 = bin2hex(random_bytes(6));
        $friendship_id2 = bin2hex(random_bytes(6));
        
        // Insert the friendship_ids and user IDs into the network table
        $networkInsertQuery = "INSERT INTO network (friendship_id, user_id_1, user_id_2) VALUES (?, ?, ?), (?, ?, ?)";
        $stmtNetworkInsert = $conn->prepare($networkInsertQuery);
        $stmtNetworkInsert->bind_param("ssssss", $friendship_id1, $senderId, $recieverID, $friendship_id2, $recieverID, $senderId);
        
        if ($stmtNetworkInsert->execute()) {
            echo "Friendships added to the network table";
        } else {
            echo "Error inserting into network table";
        }
        
        $stmtNetworkInsert->close();


    
    } else {
        // Invalid or missing data in the request
        http_response_code(400); // Bad Request
        echo "Invalid request data";
    }
} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
    echo "Invalid request method";
}
?>
