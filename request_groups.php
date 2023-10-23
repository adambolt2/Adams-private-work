<?php
session_start();

require_once('./config_members.php');
$conn = new mysqli($config["host"], $config["user"], $config["password"], $config["dbname"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'));
$stmtInsertRequest = null;
$stmtCheckRequest = null;
$groupID = $data->group_id;
$groupTitle = $data->group_title;
$userID = $_SESSION['UniqueID'];

if (!$groupID || !$groupTitle || !$userID) {
    die("Invalid data.");
}

// Check if the user is already a member of this group
$sqlCheckMember = "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?";
$stmtCheckMember = $conn->prepare($sqlCheckMember);
$stmtCheckMember->bind_param("ss", $groupID, $userID);
$stmtCheckMember->execute();
$resultCheckMember = $stmtCheckMember->get_result();

if ($resultCheckMember->num_rows > 0) {
    // User is already a member of this group
    echo json_encode(array('success' => false, 'message' => 'You are already a member of this group.'));
} else {
    // Check if a request from the user for this group already exists
    $sqlCheckRequest = "SELECT * FROM group_requests WHERE group_id = ? AND user_id = ?";
    $stmtCheckRequest = $conn->prepare($sqlCheckRequest);
    $stmtCheckRequest->bind_param("ss", $groupID, $userID);
    $stmtCheckRequest->execute();
    $resultCheckRequest = $stmtCheckRequest->get_result();

    if ($resultCheckRequest->num_rows > 0) {
        // User has already sent a request to this group
        echo json_encode(array('success' => false, 'message' => 'Request already sent.'));
    } else {
        // Insert a new request
        $requestStatus = 'Pending';
        $timestamp = date('Y-m-d H:i:s'); // Get the current timestamp

        $sqlInsertRequest = "INSERT INTO group_requests (group_id, user_id, group_title, request_status, time_stamp) VALUES (?, ?, ?, ?, ?)";
        $stmtInsertRequest = $conn->prepare($sqlInsertRequest);
        $stmtInsertRequest->bind_param("sssss", $groupID, $userID, $groupTitle, $requestStatus, $timestamp);

        if ($stmtInsertRequest->execute()) {
            echo json_encode(array('success' => true, 'message' => 'Request sent successfully.'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to send request.'));
        }
    }
}

// Close the prepared statements and the database connection
$stmtCheckMember->close();
if ($stmtCheckRequest) {
    $stmtCheckRequest->close();
}
if ($stmtInsertRequest) {
    $stmtInsertRequest->close();
}
$conn->close();
?>
