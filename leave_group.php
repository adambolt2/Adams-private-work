<?php
session_start();

require_once('./config_members.php');
$conn = new mysqli($config["host"], $config["user"], $config["password"], $config["dbname"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'));
$groupID = $data->groupID;
$userID = $_SESSION['UniqueID'];

if (!$groupID || !$userID) {
    die("Invalid group or user ID.");
}

// Initialize variables to track success in both tables
$successGroupMembers = false;
$successGroupInvite = false;
$nextCreatorAppointed = false;
$stmtAppointCreator = null;
$stmtUpdateCreatorID = null;

// Check if the leaving user is the creator
$isCreator = false;
$sqlCheckCreator = "SELECT user_id FROM group_members WHERE group_id = ? AND privs = 'creator' AND user_id = ?";
$stmtCheckCreator = $conn->prepare($sqlCheckCreator);
$stmtCheckCreator->bind_param("ss", $groupID, $userID);
$stmtCheckCreator->execute();
$resultCheckCreator = $stmtCheckCreator->get_result();

if ($resultCheckCreator->num_rows > 0) {
    $isCreator = true;
}

if ($isCreator) {
    // Get the user ID of the user who has been in the group the longest
    $sqlGetNextCreatorID = "SELECT user_id FROM group_members WHERE group_id = ? AND privs != 'creator' ORDER BY time_stamp ASC LIMIT 1";
    $stmtGetNextCreatorID = $conn->prepare($sqlGetNextCreatorID);
    $stmtGetNextCreatorID->bind_param("s", $groupID);
    $stmtGetNextCreatorID->execute();
    $resultNextCreatorID = $stmtGetNextCreatorID->get_result();

    if ($resultNextCreatorID->num_rows > 0) {
        $row = $resultNextCreatorID->fetch_assoc();
        $nextCreatorID = $row['user_id'];

        // Delete the current creator from the group_members table
        $sqlDeleteCreator = "DELETE FROM group_members WHERE group_id = ? AND user_id = ? AND privs = 'creator'";
        $stmtDeleteCreator = $conn->prepare($sqlDeleteCreator);
        $stmtDeleteCreator->bind_param("ss", $groupID, $userID);

        if ($stmtDeleteCreator->execute()) {
            $nextCreatorAppointed = true;
        }

        if ($nextCreatorAppointed) {
            // Appoint the next user as the new creator
            $sqlAppointCreator = "UPDATE group_members SET privs = 'creator' WHERE group_id = ? AND user_id = ?";
            $stmtAppointCreator = $conn->prepare($sqlAppointCreator);
            $stmtAppointCreator->bind_param("ss", $groupID, $nextCreatorID);
            $stmtAppointCreator->execute();

            // Update the group_create table with the new creator's ID
            $sqlUpdateCreatorID = "UPDATE group_create SET creator_id = ? WHERE group_id = ?";
            $stmtUpdateCreatorID = $conn->prepare($sqlUpdateCreatorID);
            $stmtUpdateCreatorID->bind_param("ss", $nextCreatorID, $groupID);
            $stmtUpdateCreatorID->execute();
        }
    } else {
        // If there's no next member to appoint as creator, delete the group from group_create
        $sqlDeleteGroupCreate = "DELETE FROM group_create WHERE group_id = ?";
        $stmtDeleteGroupCreate = $conn->prepare($sqlDeleteGroupCreate);
        $stmtDeleteGroupCreate->bind_param("s", $groupID);
        $stmtDeleteGroupCreate->execute();

        // Also, delete the member from group_members
        $sqlDeleteGroupMember = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
        $stmtDeleteGroupMember = $conn->prepare($sqlDeleteGroupMember);
        $stmtDeleteGroupMember->bind_param("ss", $groupID, $userID);
        $stmtDeleteGroupMember->execute();

        // Delete the requests from group_requests
        $sqlDeleteGroupRequests = "DELETE FROM group_requests WHERE group_id = ? AND user_id = ?";
        $stmtDeleteGroupRequests = $conn->prepare($sqlDeleteGroupRequests);
        $stmtDeleteGroupRequests->bind_param("ss", $groupID, $userID);
        $stmtDeleteGroupRequests->execute();

        $sqlDeleteGroupMessages = "DELETE FROM group_messages WHERE group_id = ?";
        $stmtDeleteGroupMessages = $conn->prepare($sqlDeleteGroupMessages);
        $stmtDeleteGroupMessages->bind_param("s", $groupID);
        $stmtDeleteGroupMessages->execute();

        // Close the statements for deletion
        $stmtDeleteGroupCreate->close();
        $stmtDeleteGroupMessages->close();
        $stmtDeleteGroupMember->close();
        $stmtDeleteGroupRequests->close();

        // Close the prepared statements and the database connection
        $stmtCheckCreator->close();
        if ($stmtAppointCreator) {
            $stmtAppointCreator->close();
        }
        if ($stmtUpdateCreatorID) {
            $stmtUpdateCreatorID->close();
        }
        $stmtGroupMembers->close();
        $stmtGroupInvite->close();
        $conn->close();

        // Return a response
        echo json_encode(array('success' => true, 'message' => 'User left the group, and the group was deleted.'));
        exit;
    }
}

// Delete the user from the group_members table
$sqlGroupMembers = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
$stmtGroupMembers = $conn->prepare($sqlGroupMembers);
$stmtGroupMembers->bind_param("ss", $groupID, $userID);

if ($stmtGroupMembers->execute()) {
    $successGroupMembers = true;
}

// Delete the user from the group_invite table
$sqlGroupInvite = "DELETE FROM group_invite WHERE group_id = ? AND user_id = ?";
$stmtGroupInvite = $conn->prepare($sqlGroupInvite);
$stmtGroupInvite->bind_param("ss", $groupID, $userID);

if ($stmtGroupInvite->execute()) {
    $successGroupInvite = true;
}

// Delete the requests from group_requests
$sqlDeleteGroupRequests = "DELETE FROM group_requests WHERE group_id = ? AND user_id = ?";
$stmtDeleteGroupRequests = $conn->prepare($sqlDeleteGroupRequests);
$stmtDeleteGroupRequests->bind_param("ss", $groupID, $userID);
$stmtDeleteGroupRequests->execute();

// Check if the deletions were successful in both tables
if ($successGroupMembers && $successGroupInvite) {
    // Deletions were successful
    echo json_encode(array('success' => true, 'message' => 'User left the group.'));
} else {
    // Deletions failed
    echo json_encode(array('success' => false, 'error' => 'Failed to leave the group.'));
}

// Close the prepared statements and the database connection
$stmtCheckCreator->close();
if ($stmtAppointCreator) {
    $stmtAppointCreator->close();
}
if ($stmtUpdateCreatorID) {
    $stmtUpdateCreatorID->close();
}
$stmtGroupMembers->close();
$stmtGroupInvite->close();
$conn->close();
?>
    