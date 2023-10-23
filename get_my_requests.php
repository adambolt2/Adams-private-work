<?php
session_start();

require_once('./config_members.php');
$conn = new mysqli($config["host"], $config["user"], $config["password"], $config["dbname"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userID = $_SESSION['UniqueID'];

if (!$userID) {
    die("Invalid user ID.");
}

// Get group IDs where the user is an admin or creator
$sqlGetGroupIDs = "SELECT group_id FROM group_members WHERE user_id = ? AND (privs = 'admin' OR privs = 'creator')";
$stmtGetGroupIDs = $conn->prepare($sqlGetGroupIDs);
$stmtGetGroupIDs->bind_param("s", $userID);
$stmtGetGroupIDs->execute();
$resultGroupIDs = $stmtGetGroupIDs->get_result();

$groupIDs = array();

while ($row = $resultGroupIDs->fetch_assoc()) {
    $groupIDs[] = $row['group_id'];
}

// Get requests for the retrieved group IDs with user information
$requests = array();

if (!empty($groupIDs)) {
    $groupIDsPlaceholder = str_repeat('?, ', count($groupIDs) - 1) . '?';
    $sqlGetRequests = "SELECT gr.*, 
    (SELECT ent_first_name FROM entrepreneur e WHERE e.unique_id = gr.user_id) AS ent_first_name,
    (SELECT ent_given_name FROM entrepreneur e WHERE e.unique_id = gr.user_id) AS ent_given_name,
    (SELECT entrepreneur_personal_image FROM entrepreneur e WHERE e.unique_id = gr.user_id) AS entrepreneur_personal_image,
    (SELECT bud_first_name FROM buddy b WHERE b.unique_id = gr.user_id) AS bud_first_name,
    (SELECT bud_given_name FROM buddy b WHERE b.unique_id = gr.user_id) AS bud_given_name,
    (SELECT buddy_personal_image FROM buddy b WHERE b.unique_id = gr.user_id) AS buddy_personal_image,
    (SELECT contributor_first_name FROM contributor c WHERE c.unique_id = gr.user_id) AS contributor_first_name,
    (SELECT contributor_given_name FROM contributor c WHERE c.unique_id = gr.user_id) AS contributor_given_name,
    (SELECT contributor_personal_image FROM contributor c WHERE c.unique_id = gr.user_id) AS contributor_personal_image
    FROM group_requests gr WHERE gr.group_id IN ($groupIDsPlaceholder) AND gr.request_status = 'Pending'";


    $stmtGetRequests = $conn->prepare($sqlGetRequests);
    $stmtGetRequests->bind_param(str_repeat('s', count($groupIDs)), ...$groupIDs);
    $stmtGetRequests->execute();
    $resultRequests = $stmtGetRequests->get_result();

    while ($row = $resultRequests->fetch_assoc()) {
        // Define a new structure for each request
        $request = array(
            'group_id' => $row['group_id'],
            'group_title' => $row['group_title'],
            'request_status' => $row['request_status'],
            'time_stamp' => $row['time_stamp'],
            'user_info' => array(
                'unique_id' => $row['user_id'],
                'first_name' => $row['ent_first_name'] ?: $row['bud_first_name'] ?: $row['contributor_first_name'] ?: 'Default First Name',
                'given_name' => $row['ent_given_name'] ?: $row['bud_given_name'] ?: $row['contributor_given_name'] ?: 'Default Given Name',
                'personal_image' => $row['entrepreneur_personal_image'] ?: $row['buddy_personal_image'] ?: $row['contributor_personal_image'] ?: './default.jpg',
            ),
        );
    
        // Filter out null values from the user_info array
        $request['user_info'] = array_filter($request['user_info'], function ($value) {
            return $value !== null;
        });
    
        // Add the request to the results array
        $requests[] = $request;
}
}

// Return the requests as JSON
echo json_encode($requests);

// Close the prepared statements and the database connection
$stmtGetGroupIDs->close();
$stmtGetRequests->close();
$conn->close();
?>
