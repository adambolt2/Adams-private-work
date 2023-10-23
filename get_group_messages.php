<?php
session_start();

require_once('./config_members.php');
$conn = new mysqli($config["host"], $config["user"], $config["password"], $config["dbname"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'));
$groupID = $data->groupid;
$userID = $_SESSION['UniqueID'];
$limit = isset($data->limit) ? intval($data->limit) : 10; // Default to 10 if limit is not provided


if (!$groupID || !$userID) {
    die("Invalid group or user ID.");
}

$sql = "SELECT * FROM group_messages WHERE group_id = ? ORDER BY time_stamp DESC LIMIT ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $groupID, $limit); // Bind the limit parameter
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $myMessages = array();
    $theirMessages = array();

    while ($row = $result->fetch_assoc()) {
        $message = array(
            'message_id' => $row['message_id'],
            'user_id' => $row['user_id'],
            'message' => $row['message'],
            'timestamp' => $row['time_stamp']
        );

        if ($row['user_id'] == $userID) {
            $myMessages[] = $message;
        } else {
            $senderFirstName = '';
            $senderLastName = '';
            $senderPersonalImage = '';

            // Check buddy table
            $stmtBuddy = $conn->prepare("SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?");
            $stmtBuddy->bind_param("s", $row['user_id']);
            $stmtBuddy->execute();
            $resultBuddy = $stmtBuddy->get_result();

            if ($resultBuddy && $resultBuddy->num_rows > 0) {
                $senderRow = $resultBuddy->fetch_assoc();
                $senderFirstName = $senderRow['bud_first_name'];
                $senderLastName = $senderRow['bud_given_name'];
                $senderPersonalImage = $senderRow['buddy_personal_image'];
            }

            // Check contributor table
            $stmtContributor = $conn->prepare("SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?");
            $stmtContributor->bind_param("s", $row['user_id']);
            $stmtContributor->execute();
            $resultContributor = $stmtContributor->get_result();

            if ($resultContributor && $resultContributor->num_rows > 0) {
                $senderRow = $resultContributor->fetch_assoc();
                $senderFirstName = $senderRow['contributor_first_name'];
                $senderLastName = $senderRow['contributor_given_name'];
                $senderPersonalImage = $senderRow['contributor_personal_image'];
            }

            // Check entrepreneur table
            $stmtEntrepreneur = $conn->prepare("SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?");
            $stmtEntrepreneur->bind_param("s", $row['user_id']);
            $stmtEntrepreneur->execute();
            $resultEntrepreneur = $stmtEntrepreneur->get_result();

            if ($resultEntrepreneur && $resultEntrepreneur->num_rows > 0) {
                $senderRow = $resultEntrepreneur->fetch_assoc();
                $senderFirstName = $senderRow['ent_first_name'];
                $senderLastName = $senderRow['ent_given_name'];
                $senderPersonalImage = $senderRow['entrepreneur_personal_image'];
            }

            $message['sender_info'] = array(
                'first_name' => $senderFirstName,
                'last_name' => $senderLastName,
                'personal_image' => $senderPersonalImage
            );

            $theirMessages[] = $message;
        }
    }

    $stmt->close();
    if (isset($stmtBuddy)) {
        $stmtBuddy->close();
    }
    if (isset($stmtContributor)) {
        $stmtContributor->close();
    }
    if (isset($stmtEntrepreneur)) {
        $stmtEntrepreneur->close();
    }
    $conn->close();

    $response = array(
        'success' => true,
        'myMessages' => $myMessages,
        'theirMessages' => $theirMessages
    );

    echo json_encode($response);
} else {
    echo json_encode(array('success' => false, 'error' => 'Failed to retrieve messages.'));
}
