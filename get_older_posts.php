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

$MyID = $_SESSION['UniqueID'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SQL query using a prepared statement
$sql = "SELECT user_id_1, user_id_2 FROM network WHERE user_id_1 = ? OR user_id_2 = ?";
$stmt = $conn->prepare($sql);

// Bind the parameter (MyID)
$stmt->bind_param("ss", $MyID, $MyID);

// Execute the query
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Create an array to store unique user IDs
$userIdsArray = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Check which user_id matches $MyID and store the other user_id
        if ($row['user_id_1'] == $MyID) {
            $userIdsArray[] = $row['user_id_2'];
        } else {
            $userIdsArray[] = $row['user_id_1'];
        }
    }

    // Remove duplicates by converting the array to a set and back to an array
    $userIdsArray = array_values(array_unique($userIdsArray));
}

// Create an array to store sender information
// ... (previous code)

// Create an array to store sender information
$senderInfoArray = [];

// Loop through each user ID
foreach ($userIdsArray as $senderId) {
    $senderInfo = [];

    // Initialize variables for buddy table query
    $stmtBuddy = null;
    $buddyResult = null;

    // Check entrepreneur table
    $entrepreneurQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?";
    $stmtEntrepreneur = $conn->prepare($entrepreneurQuery);
    $stmtEntrepreneur->bind_param("s", $senderId);
    $stmtEntrepreneur->execute();
    $entrepreneurResult = $stmtEntrepreneur->get_result();

    if ($entrepreneurRow = $entrepreneurResult->fetch_assoc()) {
        $senderInfo['SenderFname'] = $entrepreneurRow["ent_first_name"];
        $senderInfo['SenderSname'] = $entrepreneurRow["ent_given_name"];
        $senderInfo['Sender_personal_comment_image'] = $entrepreneurRow["entrepreneur_personal_image"];
        $senderInfo['UniqueID'] = $senderId;

        if (empty($senderInfo['Sender_personal_comment_image'])) {
            $senderInfo['Sender_personal_comment_image'] = "./Default.jpg";
        }

        // Add membership type
        $senderInfo['MembershipType'] = "Entrepreneur";
    }

    // Check contributor table if entrepreneur info not found
    if (empty($senderInfo)) {
        $contributorQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
        $stmtContributor = $conn->prepare($contributorQuery);
        $stmtContributor->bind_param("s", $senderId);
        $stmtContributor->execute();
        $contributorResult = $stmtContributor->get_result();
        $senderInfo['UniqueID'] = $senderId;

        if ($contributorRow = $contributorResult->fetch_assoc()) {
            $senderInfo['SenderFname'] = $contributorRow["contributor_first_name"];
            $senderInfo['SenderSname'] = $contributorRow["contributor_given_name"];
            $senderInfo['Sender_personal_comment_image'] = $contributorRow["contributor_personal_image"];

            if (empty($senderInfo['Sender_personal_comment_image'])) {
                $senderInfo['Sender_personal_comment_image'] = "./Default.jpg";
            }

            // Add membership type
            $senderInfo['MembershipType'] = "Contributor";
        }
    }

    // Check buddy table if contributor info not found
    if (empty($senderInfo)) {
        $buddyQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
        $stmtBuddy = $conn->prepare($buddyQuery);
        $stmtBuddy->bind_param("s", $senderId);
        $stmtBuddy->execute();
        $buddyResult = $stmtBuddy->get_result();

        if ($buddyRow = $buddyResult->fetch_assoc()) {
            $senderInfo['SenderFname'] = $buddyRow["bud_first_name"];
            $senderInfo['SenderSname'] = $buddyRow["bud_given_name"];
            $senderInfo['Sender_personal_comment_image'] = $buddyRow["buddy_personal_image"];
            $senderInfo['UniqueID'] = $senderId;

            if (empty($senderInfo['Sender_personal_comment_image'])) {
                $senderInfo['Sender_personal_comment_image'] = "./Default.jpg";
            }

            // Add membership type
            $senderInfo['MembershipType'] = "Buddy";
        }
    }

    // Fetch the user's posts
    $posts = [];
    $twentyFourHoursAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));
    $seventyTwoHoursAgo = date('Y-m-d H:i:s', strtotime('-720 hours'));
    
    // Modify your query to select posts between 24 and 72 hours old
    $postsQuery = "SELECT post_id, post_image, post_desc, post_time FROM posts WHERE user_id = ? AND post_time BETWEEN ? AND ? ORDER BY post_time DESC";
    $stmtPosts = $conn->prepare($postsQuery);
    $stmtPosts->bind_param("sss", $senderId, $seventyTwoHoursAgo, $twentyFourHoursAgo);
    $stmtPosts->execute();
    
    $postsResult = $stmtPosts->get_result();

    while ($postRow = $postsResult->fetch_assoc()) {
        $post = [
            'post_id' => $postRow['post_id'],
            'post_image' => $postRow['post_image'],
            'post_desc' => $postRow['post_desc'],
            'post_time' => $postRow['post_time'],
            'comments' => [], // Initialize an empty array to store comments
        ];
    
        // Fetch comments for this post
        $commentsQuery = "SELECT comm_id, comm, user_id, comm_time FROM comments WHERE post_id = ?  ORDER BY comm_time DESC";
        $stmtComments = $conn->prepare($commentsQuery);
        $stmtComments->bind_param("s", $postRow['post_id']);
        $stmtComments->execute();
        $commentsResult = $stmtComments->get_result();
        while ($commentRow = $commentsResult->fetch_assoc()) {
            $comment = [
                'comm_id' => $commentRow['comm_id'],
                'comm' => $commentRow['comm'],
                'user_id' => $commentRow['user_id'],
                'comm_time' => $commentRow['comm_time'],
                'first_name' => null, // Initialize first name
                'last_name' => null,  // Initialize last name
                'personal_image' => null,  // Initialize personal image
                'MembershipType' => null, // Initialize membership type
                'replies' => [], // Initialize an empty array to store replies
            ];
        
            // Fetch the first name, last name, and personal image of the comment user
        
            // Check entrepreneur table for comment user info
            $commentUserQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?";
            $stmtCommentUser = $conn->prepare($commentUserQuery);
            $stmtCommentUser->bind_param("s", $commentRow['user_id']);
            $stmtCommentUser->execute();
            $commentUserResult = $stmtCommentUser->get_result();
        
            if ($commentUserRow = $commentUserResult->fetch_assoc()) {
                $comment['first_name'] = $commentUserRow['ent_first_name'];
                $comment['last_name'] = $commentUserRow['ent_given_name'];
                $comment['personal_image'] = $commentUserRow['entrepreneur_personal_image'];
                $comment['MembershipType'] = "Entrepreneur";

                if (empty($comment['personal_image'])) {
                    $comment['personal_image'] = "./Default.jpg";
                }
        
            }
        
            // Check contributor table if entrepreneur info not found
            if (empty($comment['first_name'])) {
                $commentUserQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
                $stmtCommentUser = $conn->prepare($commentUserQuery);
                $stmtCommentUser->bind_param("s", $commentRow['user_id']);
                $stmtCommentUser->execute();
                $commentUserResult = $stmtCommentUser->get_result();
        
                if ($commentUserRow = $commentUserResult->fetch_assoc()) {
                    $comment['first_name'] = $commentUserRow['contributor_first_name'];
                    $comment['last_name'] = $commentUserRow['contributor_given_name'];
                    $comment['personal_image'] = $commentUserRow['contributor_personal_image'];
                    $comment['MembershipType'] = "Contributor";

                    if (empty($comment['personal_image'])) {
                        $comment['personal_image'] = "./Default.jpg";
                    }
                }
            }
        
            // Check buddy table if contributor info not found
            if (empty($comment['first_name'])) {
                $commentUserQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
                $stmtCommentUser = $conn->prepare($commentUserQuery);
                $stmtCommentUser->bind_param("s", $commentRow['user_id']);
                $stmtCommentUser->execute();
                $commentUserResult = $stmtCommentUser->get_result();
        
                if ($commentUserRow = $commentUserResult->fetch_assoc()) {
                    $comment['first_name'] = $commentUserRow['bud_first_name'];
                    $comment['last_name'] = $commentUserRow['bud_given_name'];
                    $comment['personal_image'] = $commentUserRow['buddy_personal_image'];
                    $comment['MembershipType'] = "Buddy";
                    if (empty($comment['personal_image'])) {
                        $comment['personal_image'] = "./Default.jpg";
                    }
                }
            }
        
            // Fetch replies for this comment
            $replies = [];
        
            $repliesQuery = "SELECT reply_id, user_id, reply, time_stamp FROM reply WHERE comm_id = ? ORDER BY time_stamp DESC ";
            $stmtReplies = $conn->prepare($repliesQuery);
            $stmtReplies->bind_param("s", $commentRow['comm_id']);
            $stmtReplies->execute();
            $repliesResult = $stmtReplies->get_result();
        
            while ($replyRow = $repliesResult->fetch_assoc()) {
                $reply = [
                    'reply_id' => $replyRow['reply_id'],
                    'user_id' => $replyRow['user_id'],
                    'reply' => $replyRow['reply'],
                    'time_stamp' => $replyRow['time_stamp'],
                    'reply_first_name' => null, // Initialize reply user first name
                    'reply_last_name' => null,  // Initialize reply user last name
                    'reply_personal_image' => null,  // Initialize reply user personal image
                    'reply_MembershipType' => null, // Initialize reply user membership type
                ];
        
                // Fetch the first name, last name, and personal image of the reply user
        
                // Check entrepreneur table for reply user info
                $replyUserQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?";
                $stmtReplyUser = $conn->prepare($replyUserQuery);
                $stmtReplyUser->bind_param("s", $replyRow['user_id']);
                $stmtReplyUser->execute();
                $replyUserResult = $stmtReplyUser->get_result();
        
                if ($replyUserRow = $replyUserResult->fetch_assoc()) {
                    $reply['reply_first_name'] = $replyUserRow['ent_first_name'];
                    $reply['reply_last_name'] = $replyUserRow['ent_given_name'];
                    $reply['reply_personal_image'] = $replyUserRow['entrepreneur_personal_image'];
                    $reply['reply_MembershipType'] = "Entrepreneur";
                
                    if (empty($reply['reply_personal_image'])) {
                        $reply['reply_personal_image'] = "./Default.jpg";
                    }
                }
        
                // Check contributor table if entrepreneur info not found
                if (empty($reply['reply_first_name'])) {
                    $replyUserQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
                    $stmtReplyUser = $conn->prepare($replyUserQuery);
                    $stmtReplyUser->bind_param("s", $replyRow['user_id']);
                    $stmtReplyUser->execute();
                    $replyUserResult = $stmtReplyUser->get_result();
        
                    if ($replyUserRow = $replyUserResult->fetch_assoc()) {
                        $reply['reply_first_name'] = $replyUserRow['contributor_first_name'];
                        $reply['reply_last_name'] = $replyUserRow['contributor_given_name'];
                        $reply['reply_personal_image'] = $replyUserRow['contributor_personal_image'];
                        $reply['reply_MembershipType'] = "Contributor";
                        if (empty($reply['reply_personal_image'])) {
                            $reply['reply_personal_image'] = "./Default.jpg";
                        }
                    }
                }
        
                // Check buddy table if contributor info not found
                if (empty($reply['reply_first_name'])) {
                    $replyUserQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
                    $stmtReplyUser = $conn->prepare($replyUserQuery);
                    $stmtReplyUser->bind_param("s", $replyRow['user_id']);
                    $stmtReplyUser->execute();
                    $replyUserResult = $stmtReplyUser->get_result();
        
                    if ($replyUserRow = $replyUserResult->fetch_assoc()) {
                        $reply['reply_first_name'] = $replyUserRow['bud_first_name'];
                        $reply['reply_last_name'] = $replyUserRow['bud_given_name'];
                        $reply['reply_personal_image'] = $replyUserRow['buddy_personal_image'];
                        $reply['reply_MembershipType'] = "Buddy";
                        if (empty($reply['reply_personal_image'])) {
                            $reply['reply_personal_image'] = "./Default.jpg";
                        }
                    }
                }
        
                // Add the reply to the comment's replies array
                $replies[] = $reply;
        
                // Close the result sets and statements for reply user
                mysqli_stmt_close($stmtReplyUser);
                mysqli_free_result($replyUserResult);
            }
        
            // Add the replies to the comment
            $comment['replies'] = $replies;
        
            // Add the comment to the post's comments array
            $post['comments'][] = $comment;
        
            // Close the result sets and statements for replies
            mysqli_stmt_close($stmtReplies);
            mysqli_free_result($repliesResult);
        }
    
        // Close the result sets and statements for comments
        mysqli_stmt_close($stmtComments);
        mysqli_free_result($commentsResult);
    
        // Add the post to the user's posts array
        $posts[] = $post;
    }
    
    // Close the result sets and statements for posts

    // Add posts to sender info
    $senderInfo['Posts'] = $posts;
    
    // Add sender info to the array
    $senderInfoArray[] = $senderInfo;
    
    // Close the result sets and statements for posts
    mysqli_stmt_close($stmtPosts);
    mysqli_free_result($postsResult);

    // Add posts to sender info
    $senderInfo['Posts'] = $posts;

    // Add sender info to the array
    
    $senderInfoArray[] = $senderInfo;
    
}

// Close the database connection
// After collecting all senderInfo objects, remove duplicates based on UniqueID
$uniqueSenderInfoArray = [];

foreach ($senderInfoArray as $senderInfo) {
    $uniqueSenderInfoArray[$senderInfo['UniqueID']] = $senderInfo;
}

// Re-index the array to have sequential numeric keys
$uniqueSenderInfoArray = array_values($uniqueSenderInfoArray);

// Close the database connection
mysqli_close($conn);

// Encode the object as JSON and send the response
header('Content-Type: application/json');
echo json_encode($uniqueSenderInfoArray);

?>

