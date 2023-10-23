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

    // Delete the row from group_requests where group_id and user_id match
    $sqlDeleteRequest = "DELETE FROM group_requests WHERE group_id = ? AND user_id = ?";
    $stmtDeleteRequest = $conn->prepare($sqlDeleteRequest);
    $stmtDeleteRequest->bind_param("ss", $requestId, $uniqueId);

    if ($stmtDeleteRequest->execute()) {
        echo json_encode(array('success' => true, 'message' => 'Request declined and removed.'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to delete the request.'));
    }

    // Close the prepared statement and the database connection
    $stmtDeleteRequest->close();
}

$conn->close();
?>
