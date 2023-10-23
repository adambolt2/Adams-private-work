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

$timestamp = date('Y-m-d H:i:s');
$unique_id = $_SESSION['UniqueID'];
$viewed_id = $_SESSION['viewedID'];
$status = 'pending';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if there is an existing request with the same sender and receiver
$checkQuery = "SELECT * FROM request WHERE (sender_id = ? AND reciever_id = ?) OR (sender_id = ? AND reciever_id = ?)";
$stmtCheck = $conn->prepare($checkQuery);
$stmtCheck->bind_param("ssss", $unique_id, $viewed_id, $viewed_id, $unique_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    // There is an existing request, check the status
    $requestData = $resultCheck->fetch_assoc();
    $retrieved_status = $requestData["status"];
    
    if ($retrieved_status === "pending") {
        // Request is pending, update status to accepted
        $updateQuery = "UPDATE request SET status = 'accepted' WHERE (sender_id = ? AND reciever_id = ?) OR (sender_id = ? AND reciever_id = ?)";
        $stmtUpdate = $conn->prepare($updateQuery);
        $stmtUpdate->bind_param("ssss", $unique_id, $viewed_id, $viewed_id, $unique_id);

        if ($stmtUpdate->execute()) {
            echo "Request automatically accepted";
            $friendship_id1 = bin2hex(random_bytes(6));
            $friendship_id2 = bin2hex(random_bytes(6));
            
            // Insert the friendship_ids and user IDs into the network table
            $networkInsertQuery = "INSERT INTO network (friendship_id, user_id_1, user_id_2) VALUES (?, ?, ?), (?, ?, ?)";
            $stmtNetworkInsert = $conn->prepare($networkInsertQuery);
            $stmtNetworkInsert->bind_param("ssssss", $friendship_id1, $unique_id, $viewed_id, $friendship_id2, $viewed_id, $unique_id);
            
            if ($stmtNetworkInsert->execute()) {
                echo "Friendships added to the network table";
            } else {
                echo "Error inserting into network table";
            }
            
            $stmtNetworkInsert->close();



        } else {
            echo "Error updating request status";
        }

        $stmtUpdate->close();
    } elseif ($retrieved_status === "accepted") {
        echo "Request already exists and is accepted";
    } elseif ($retrieved_status === "rejected") {
        // Request was previously rejected, update status to pending
        $updateQuery = "UPDATE request SET status = 'pending' WHERE (sender_id = ? AND reciever_id = ?) OR (sender_id = ? AND reciever_id = ?)";
        $stmtUpdate = $conn->prepare($updateQuery);
        $stmtUpdate->bind_param("ssss", $unique_id, $viewed_id, $viewed_id, $unique_id);

        if ($stmtUpdate->execute()) {
            echo "Request changed to pending";
        } else {
            echo "Error updating request status";
        }

        $stmtUpdate->close();
    }
} else {
    // No existing request, insert a new one
    $request_id = bin2hex(random_bytes(6));

    $stmtInsert = $conn->prepare("INSERT INTO request (Request_id, sender_id, reciever_id, status, time_stamp) VALUES (?, ?, ?, ?, ?)");
    $stmtInsert->bind_param("sssss", $request_id, $unique_id, $viewed_id, $status, $timestamp);

    if ($stmtInsert->execute()) {
        echo "Request sent successfully";
    } else {
        echo "Error posting request";
    }

    $stmtInsert->close();
}

$stmtCheck->close();
$conn->close();
?>
