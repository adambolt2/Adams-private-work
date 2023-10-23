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

// Retrieve user ID from the session
$MyID = $_SESSION['UniqueID'];

// Create arrays to store messages
$mymessages = [];
$theirmessages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the JSON data from the POST body and decode it
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    // Check if JSON decoding was successful
    if ($data === null) {
        echo "Invalid JSON data";
        exit;
    }

    // Extract friend_id from the decoded data
    $IntendedUserID = $data->friend_id;

    // ... Rest of the PHP code remains the same ...
}


$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


$recentMessages = [];

// ... (your previous code)
$userIds = [];
$sqlRecentMessages = "
    SELECT m1.*
    FROM messages m1
    INNER JOIN (
        SELECT
            CASE
                WHEN sender_id = ? THEN reciever_id
                WHEN reciever_id = ? THEN sender_id
            END AS other_user,
            MAX(time_stamp) AS max_time_stamp
        FROM messages
        WHERE sender_id = ? OR reciever_id = ?
        GROUP BY other_user
    ) m2 ON (
        (m1.sender_id = ? AND m1.reciever_id = m2.other_user) OR
        (m1.reciever_id = ? AND m1.sender_id = m2.other_user)
    ) AND m1.time_stamp = m2.max_time_stamp
    ORDER BY m1.time_stamp DESC
";

$stmtRecentMessages = $conn->prepare($sqlRecentMessages);
$stmtRecentMessages->bind_param("ssssss", $MyID, $MyID, $MyID, $MyID, $MyID, $MyID);
$stmtRecentMessages->execute();
$resultRecentMessages = $stmtRecentMessages->get_result();

while ($row = $resultRecentMessages->fetch_assoc()) {
    // Add the message to the recentMessages array with a flag indicating it's your message or theirs
    $row['is_my_message'] = $row['sender_id'] === $MyID;
    $recentMessages[] = $row;
    $userIds[] = $row['sender_id'];
    $userIds[] = $row['reciever_id'];
    
}


$stmtRecentMessages->close();


$recentMessagesWithSenderInfo = [];
$receiverInfoArray = [];
foreach ($recentMessages as $recentMessage) {
    $senderId = $recentMessage['sender_id'];
    $recID = $recentMessage['reciever_id'];
    $senderInfo = array(
        'sender_id' => $senderId,
        'SenderFname' => "",
        'SenderSname' => "",
        'Sender_personal_comment_image' => "",
        'profileLink' => "view_profile.php?unique_id=" . urlencode($senderId) // Add the profile 
    );

    // Query entrepreneur table
    $entrepreneurQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?";
    $stmtEntrepreneur = $conn->prepare($entrepreneurQuery);
    $stmtEntrepreneur->bind_param("s", $senderId);
    $stmtEntrepreneur->execute();
    $entrepreneurResult = $stmtEntrepreneur->get_result();

    if ($entrepreneurRow = $entrepreneurResult->fetch_assoc()) {
        $senderInfo['SenderFname'] = $entrepreneurRow["ent_first_name"];
        $senderInfo['SenderSname'] = $entrepreneurRow["ent_given_name"];
        $senderInfo['Sender_personal_comment_image'] = $entrepreneurRow["entrepreneur_personal_image"];

        if (empty($senderInfo['Sender_personal_comment_image'])) {
            $senderInfo['Sender_personal_comment_image'] = "./Default.jpg";
        }
    }

    // Close the result set and statement for entrepreneur query
    if ($stmtEntrepreneur) {
        mysqli_stmt_close($stmtEntrepreneur);
    }

    if ($entrepreneurResult) {
        mysqli_free_result($entrepreneurResult);
    }

    // Query contributor table
    $contributorQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
    $stmtContributor = $conn->prepare($contributorQuery);
    $stmtContributor->bind_param("s", $senderId);
    $stmtContributor->execute();
    $contributorResult = $stmtContributor->get_result();

    if ($contributorRow = $contributorResult->fetch_assoc()) {
        $senderInfo['SenderFname'] = $contributorRow["contributor_first_name"];
        $senderInfo['SenderSname'] = $contributorRow["contributor_given_name"];
        $senderInfo['Sender_personal_comment_image'] = $contributorRow["contributor_personal_image"];

        if (empty($senderInfo['Sender_personal_comment_image'])) {
            $senderInfo['Sender_personal_comment_image'] = "./Default.jpg";
        }
    }

    // Close the result set and statement for contributor query
    if ($stmtContributor) {
        mysqli_stmt_close($stmtContributor);
    }

    if ($contributorResult) {
        mysqli_free_result($contributorResult);
    }

    // Query buddy table
    $buddyQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
    $stmtBuddy = $conn->prepare($buddyQuery);
    $stmtBuddy->bind_param("s", $senderId);
    $stmtBuddy->execute();
    $buddyResult = $stmtBuddy->get_result();

    if ($buddyRow = $buddyResult->fetch_assoc()) {
        $senderInfo['SenderFname'] = $buddyRow["bud_first_name"];
        $senderInfo['SenderSname'] = $buddyRow["bud_given_name"];
        $senderInfo['Sender_personal_comment_image'] = $buddyRow["buddy_personal_image"];

        if (empty($senderInfo['Sender_personal_comment_image'])) {
            $senderInfo['Sender_personal_comment_image'] = "./Default.jpg";
        }
    }

    // Close the result set and statement for buddy query
    if ($stmtBuddy) {
        mysqli_stmt_close($stmtBuddy);
    }

    if ($buddyResult) {
        mysqli_free_result($buddyResult);
    }

    // Add sender info to the recent message








    $receiverInfo = array(
        'reciever_id' => $recID,
        'recieverFname' => "",
        'recieverSname' => "",
        'reciever_personal_comment_image' => "",
        'profileLink' => "view_profile.php?unique_id=" . urlencode($recID)
    );
    
    // Query entrepreneur table for receiver
    $entrepreneurQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?";
    $stmtEntrepreneur = $conn->prepare($entrepreneurQuery);
    $stmtEntrepreneur->bind_param("s", $recID);
    $stmtEntrepreneur->execute();
    $entrepreneurResult = $stmtEntrepreneur->get_result();
    
    if ($entrepreneurRow = $entrepreneurResult->fetch_assoc()) {
        $receiverInfo['SenderFname'] = $entrepreneurRow["ent_first_name"];
        $receiverInfo['SenderSname'] = $entrepreneurRow["ent_given_name"];
        $receiverInfo['Sender_personal_comment_image'] = $entrepreneurRow["entrepreneur_personal_image"];
    
        if (empty($receiverInfo['Sender_personal_comment_image'])) {
            $receiverInfo['Sender_personal_comment_image'] = "./Default.jpg";
        }
    }
    $contributorQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
    $stmtContributor = $conn->prepare($contributorQuery);
    $stmtContributor->bind_param("s", $recID);
    $stmtContributor->execute();
    $contributorResult = $stmtContributor->get_result();

    if ($contributorRow = $contributorResult->fetch_assoc()) {
       $receiverInfo['SenderFname']= $contributorRow["contributor_first_name"];
        $receiverInfo['SenderSname'] = $contributorRow["contributor_given_name"];
        $receiverInfo['Sender_personal_comment_image'] = $contributorRow["contributor_personal_image"];
      
      
        if (empty($receiverInfo['Sender_personal_comment_image'])) {
            $receiverInfo['Sender_personal_comment_image'] = "./Default.jpg";
        }
    }


    $buddyQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
    $stmtBuddy = $conn->prepare($buddyQuery);
    $stmtBuddy->bind_param("s", $senderId);
    $stmtBuddy->execute();
    $buddyResult = $stmtBuddy->get_result();

    if ($buddyRow = $buddyResult->fetch_assoc()) {
          $receiverInfo['SenderFname'] = $buddyRow["bud_first_name"];
        $receiverInfo['SenderSname'] = $buddyRow["bud_given_name"];
      $receiverInfo['Sender_personal_comment_image'] = $buddyRow["buddy_personal_image"];

      if (empty($receiverInfo['Sender_personal_comment_image'])) {
        $receiverInfo['Sender_personal_comment_image'] = "./Default.jpg";
    }
    }

    // Close the result set and statement for buddy query
    if ($stmtBuddy) {
        mysqli_stmt_close($stmtBuddy);
    }

    if ($buddyResult) {
        mysqli_free_result($buddyResult);
    }
    // Close the result set and statement for contributor query
    if ($stmtContributor) {
        mysqli_stmt_close($stmtContributor);
    }

    if ($contributorResult) {
        mysqli_free_result($contributorResult);
    }
    
    // Close the result set and statement for entrepreneur query
    if ($stmtEntrepreneur) {
        mysqli_stmt_close($stmtEntrepreneur);
    }
    
    if ($entrepreneurResult) {
        mysqli_free_result($entrepreneurResult);
    }


    
    $recentMessage['receiver_info'] = $receiverInfo;

   

    $recentMessage['sender_info'] = $senderInfo;

    $recentMessagesWithSenderInfo[] = $recentMessage;


 



}
// we really need to find another way later on of a way to get the names quicker than searching the 


// all messages part, need to get the to lazy load 



$allMessages = [];

// Parameters for lazy loading
// Parameters for lazy loading
$inputJSON = file_get_contents('php://input');

// Decode the JSON data
$input = json_decode($inputJSON);

// Extract the 'page' parameter
$page =  1;

$messagesPerPage = isset($input->page) ? intval($input->page) : 10;  // Set the number of messages to load per page

// Calculate the offset to fetch the appropriate set of messages
$offset = ($page - 1) * $messagesPerPage;


// Select messages where the sender is the current user and the receiver is the intended user
$sqlMyMessages = "SELECT * FROM messages WHERE sender_id = ? AND reciever_id = ? ORDER BY time_stamp DESC LIMIT ?, ?";
$stmtMyMessages = $conn->prepare($sqlMyMessages);
$stmtMyMessages->bind_param("ssii", $MyID, $IntendedUserID, $offset, $messagesPerPage); // Set IntendedUserID as the intended receiver
$stmtMyMessages->execute();
$resultMyMessages = $stmtMyMessages->get_result();

while ($row = $resultMyMessages->fetch_assoc()) {
    // Add the message to the allMessages array with a flag indicating it's your message
    $row['is_my_message'] = true;
    $allMessages[] = $row;
}

$stmtMyMessages->close();

// Select messages where the receiver is the current user and the sender is the intended user
$sqlTheirMessages = "SELECT * FROM messages WHERE reciever_id = ? AND sender_id = ? ORDER BY time_stamp DESC LIMIT ?, ?";
$stmtTheirMessages = $conn->prepare($sqlTheirMessages);
$stmtTheirMessages->bind_param("ssii", $MyID, $IntendedUserID, $offset, $messagesPerPage); // Set IntendedUserID as the intended sender
$stmtTheirMessages->execute();
$resultTheirMessages = $stmtTheirMessages->get_result();

while ($row = $resultTheirMessages->fetch_assoc()) {
    // Add the message to the allMessages array with a flag indicating it's their message
    $row['is_my_message'] = false;
    $allMessages[] = $row;
}

$stmtTheirMessages->close();

// Sort all messages by time_stamp in descending order (most recent first)
usort($allMessages, function($a, $b) {
    return strtotime($b['time_stamp']) - strtotime($a['time_stamp']);
});


$olderThreshold = 20;
foreach ($allMessages as $index => $message) {
    if ($index >= $olderThreshold) {
        $allMessages[$index]['older'] = true;
    }
}

// Close the database connection
$conn->close();

// Return JSON response with all messages
$response = [
    'all_messages' => $allMessages,
    'recent_messages' => $recentMessagesWithSenderInfo,
    'receiver_info' => $receiverInfoArray,
    'offset' => $offset,
    'page' => $page,
];

header('Content-Type: application/json');
echo json_encode($response);


?>
