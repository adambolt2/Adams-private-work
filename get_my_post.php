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

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id =  $_SESSION['UniqueID'] ; // Replace with the actual user ID

$selectQuery = "SELECT * FROM posts WHERE user_id = ? ORDER BY post_time DESC LIMIT 1";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$posts = array();


// Create an array to store user data
while ($row = $result->fetch_assoc()) {
    $post_id = $row['post_id'];
    $post_image = $row['post_image'];
    $post_desc = $row['post_desc'];
    $post_time = $row['post_time'];

    // Fetch comments for each post
    $commentsQuery = "SELECT * FROM comments WHERE post_id = ? ORDER BY comm_time DESC";
    $stmtComments = $conn->prepare($commentsQuery);
    $stmtComments->bind_param("s", $post_id);
    $stmtComments->execute();

    $commentsResult = $stmtComments->get_result();
    $comments = array();

 // Inside your loop that retrieves comments for each post
while ($commentRow = $commentsResult->fetch_assoc()) {
    $comment_id = $commentRow['comm_id']; // Added for reference
   // echo $comment_id;
    $comment = array(
        'comm' => $commentRow["comm"],
        'user_id' => $commentRow["user_id"],
        'comm_time' => $commentRow["comm_time"],
        'comm_id' => $commentRow["comm_id"]
    );

    // Fetch user's first and last name
    $userId = $commentRow["user_id"];
    $firstName = "";
    $lastName = "";
    $personal_comment_image = "";

    // Query entrepreneur table
    $entrepreneurQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image  FROM entrepreneur WHERE unique_id = ?";
    $stmtEntrepreneur = $conn->prepare($entrepreneurQuery);
    $stmtEntrepreneur->bind_param("s", $userId);
    $stmtEntrepreneur->execute();
    $entrepreneurResult = $stmtEntrepreneur->get_result();

    if ($entrepreneurRow = $entrepreneurResult->fetch_assoc()) {
        $firstName = $entrepreneurRow["ent_first_name"];
        $lastName = $entrepreneurRow["ent_given_name"];
        $personal_comment_image =  $entrepreneurRow["entrepreneur_personal_image"];
        if(empty($personal_comment_image)){
            $personal_comment_image = "./Default.jpg";
        }
    } else {
        // Query contributor table
        $contributorQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
        $stmtContributor = $conn->prepare($contributorQuery);
        $stmtContributor->bind_param("s", $userId);
        $stmtContributor->execute();
        $contributorResult = $stmtContributor->get_result();

        if ($contributorRow = $contributorResult->fetch_assoc()) {
            $firstName = $contributorRow["contributor_first_name"];
            $lastName = $contributorRow["contributor_given_name"];
            $personal_comment_image =  $contributorRow["contributor_personal_image"];
            if(empty($personal_comment_image)){
                $personal_comment_image = "./Default.jpg";
            }
        } else {
            // Query buddy table
            $buddyQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
            $stmtBuddy = $conn->prepare($buddyQuery);
            $stmtBuddy->bind_param("s", $userId);
            $stmtBuddy->execute();
            $buddyResult = $stmtBuddy->get_result();

            if ($buddyRow = $buddyResult->fetch_assoc()) {
                $firstName = $buddyRow["bud_first_name"];
                $lastName = $buddyRow["bud_given_name"];
                $personal_comment_image =  $buddyRow["buddy_personal_image"];
                if(empty($personal_comment_image)){
                    $personal_comment_image = "./Default.jpg";
                }
            }
        }
    }


    // here is where we get our replies
    $repliesQuery = "SELECT * FROM reply WHERE comm_id = ? ORDER BY time_stamp DESC";
    $stmtReplies = $conn->prepare($repliesQuery);
    $stmtReplies->bind_param("s", $comment_id);
    $stmtReplies->execute();

    $repliesResult = $stmtReplies->get_result();
    $replies = array();

    // Inside your loop that retrieves replies for each comment
    while ($replyRow = $repliesResult->fetch_assoc()) {
        $ReplyUserID = $replyRow["user_id"];
        
        $ReplierFname = "";
        $ReplierSname = "";
        $Replier_personal_comment_image = "";

        // Query entrepreneur table
        $entrepreneurQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image  FROM entrepreneur WHERE unique_id = ?";
        $stmtEntrepreneur = $conn->prepare($entrepreneurQuery);
        $stmtEntrepreneur->bind_param("s", $ReplyUserID);
        $stmtEntrepreneur->execute();
        $entrepreneurResult = $stmtEntrepreneur->get_result();

        if ($entrepreneurRow = $entrepreneurResult->fetch_assoc()) {
            $ReplierFname = $entrepreneurRow["ent_first_name"];
            $ReplierSname = $entrepreneurRow["ent_given_name"];
            $Replier_personal_comment_image =  $entrepreneurRow["entrepreneur_personal_image"];

            if(empty($Replier_personal_comment_image)){
                $Replier_personal_comment_image = "./Default.jpg";
            }
        } else {
            // Query contributor table
            $contributorQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
            $stmtContributor = $conn->prepare($contributorQuery);
            $stmtContributor->bind_param("s", $ReplyUserID);
            $stmtContributor->execute();
            $contributorResult = $stmtContributor->get_result();

            if ($contributorRow = $contributorResult->fetch_assoc()) {
                $ReplierFname = $contributorRow["contributor_first_name"];
                $ReplierSname= $contributorRow["contributor_given_name"];
                $Replier_personal_comment_image =  $contributorRow["contributor_personal_image"];
                if(empty($Replier_personal_comment_image)){
                    $Replier_personal_comment_image = "./Default.jpg";
                }
            } else {
                // Query buddy table
                $buddyQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
                $stmtBuddy = $conn->prepare($buddyQuery);
                $stmtBuddy->bind_param("s", $ReplyUserID);
                $stmtBuddy->execute();
                $buddyResult = $stmtBuddy->get_result();

                if ($buddyRow = $buddyResult->fetch_assoc()) {
                    $ReplierFname = $buddyRow["bud_first_name"];
                    $ReplierSname= $buddyRow["bud_given_name"];
                    $Replier_personal_comment_image =  $buddyRow["buddy_personal_image"];
                    if(empty($Replier_personal_comment_image)){
                        $Replier_personal_comment_image = "./Default.jpg";
                    }
                }
            }
        }

        // Add reply data to the replies array
        $replies[] = array(
            'reply_id' => $replyRow["reply_id"],
            'comment_id' => $commentRow["comm_id"],
            'user_id' => $ReplyUserID,
            'reply' => $replyRow["reply"],
            'first_name' => $ReplierFname,  // Add first name
            'last_name' => $ReplierSname,    // Add last name
            'Personal_image' => $Replier_personal_comment_image // Add image
        );
    }
 
    $comment['first_name'] = $firstName;
    $comment['last_name'] = $lastName;
    $comment['Personal_image'] = $personal_comment_image;
    $comment['replies'] = $replies;
  


    // Add first and last name to the comment




    $comments[] = $comment;
}


    // Store post and its comments in the array
    $posts[] = array(
        'post_id' => $post_id,
        'post_image' => $post_image,
        'post_desc' => $post_desc,
        'post_time' => $post_time,
        'comments' => $comments
    );
}

// Encode the entire array as JSON
$postData = json_encode($posts);
echo $postData;
?>