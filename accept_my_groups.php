<?php
session_start();

require_once('./config_members.php');
$conn = new mysqli($config["host"], $config["user"], $config["password"], $config["dbname"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$data = json_decode(file_get_contents('php://input'), true);

// Get the request ID and unique ID from the POST data
if (isset($data['requestId']) && isset($data['uniqueId'])) {
    $requestId = $data['requestId'];
    $uniqueId = $data['uniqueId'];

    // Update the group_requests table to set the request_status to 'Accepted'
    $sqlUpdateRequest = "UPDATE group_requests SET request_status = 'Accepted' WHERE group_id = ? AND user_id = ?";
    $stmtUpdateRequest = $conn->prepare($sqlUpdateRequest);
    $stmtUpdateRequest->bind_param("ss", $requestId, $uniqueId);

    if ($stmtUpdateRequest->execute()) {
        // Insert a new record into group_members
        $priv = 'Member';
        $timestamp = date('Y-m-d H:i:s');

        $sqlInsertMember = "INSERT INTO group_members (group_id, user_id, privs, time_stamp) VALUES (?, ?, ?, ?)";
        $stmtInsertMember = $conn->prepare($sqlInsertMember);
        $stmtInsertMember->bind_param("ssss", $requestId, $uniqueId, $priv, $timestamp);

        if ($stmtInsertMember->execute()) {
            // Insert a new record into group_invite
            $inviteStatus = 'Accepted';

            $sqlInsertInvite = "INSERT INTO group_invite (group_id, user_id, invite_status) VALUES (?, ?, ?)";
            $stmtInsertInvite = $conn->prepare($sqlInsertInvite);
            $stmtInsertInvite->bind_param("sss", $requestId, $uniqueId, $inviteStatus);

            if ($stmtInsertInvite->execute()) {
                echo json_encode(array('success' => true, 'message' => 'Request accepted successfully.'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to insert into group_invite.'));
            }
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to insert into group_members.'));
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to update group_requests.'));
    }

    // Close the prepared statements and the database connection
    $stmtUpdateRequest->close();
    $stmtInsertMember->close();
    $stmtInsertInvite->close();
}
$conn->close();
?>
