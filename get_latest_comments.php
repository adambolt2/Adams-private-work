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

// Establish a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming you have a POST request with the post_id you want to fetch comments for
if (isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    // Prepare the SQL query to retrieve comments
    $sql = "SELECT comm, post_id, user_id, comm_time, comm_id FROM comments WHERE post_id = ? ORDER BY comm_time DESC";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the parameter (post_id) to the statement
        $stmt->bind_param("s", $post_id);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Create an array to store the comments
        $comments = [];

        // Fetch comments and add them to the array as objects
        while ($row = $result->fetch_assoc()) {
            $commentObject = [
                'comm' => $row['comm'],
                'post_id' => $row['post_id'],
                'user_id' => $row['user_id'],
                'comm_time' => $row['comm_time'],
                'comm_id' => $row['comm_id'],
                'replies' => [], // Initialize an empty replies array
            ];

            // Now, retrieve the user information based on the user_id from three different tables

            // First, retrieve from 'entreprenur' table
            $sql_ent = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?";
            $stmt_ent = $conn->prepare($sql_ent);
            $stmt_ent->bind_param("s", $row['user_id']);
            $stmt_ent->execute();
            $result_ent = $stmt_ent->get_result();
            $row_ent = $result_ent->fetch_assoc();

            if ($row_ent) {
                $commentObject['first_name'] = $row_ent['ent_first_name'];
                $commentObject['last_name'] = $row_ent['ent_given_name'];
                $commentObject['personal_image'] = $row_ent['entrepreneur_personal_image'];
                if(empty($commentObject['personal_image'])){
                    $commentObject['personal_image'] = "./Default.jpg";
                }
            } else {
                // If not found in 'entreprenur' table, check 'contributor' table
                $sql_contrib = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
                $stmt_contrib = $conn->prepare($sql_contrib);
                $stmt_contrib->bind_param("s", $row['user_id']);
                $stmt_contrib->execute();
                $result_contrib = $stmt_contrib->get_result();
                $row_contrib = $result_contrib->fetch_assoc();

                if ($row_contrib) {
                    $commentObject['first_name'] = $row_contrib['contributor_first_name'];
                    $commentObject['last_name'] = $row_contrib['contributor_given_name'];
                    $commentObject['personal_image'] = $row_contrib['contributor_personal_image'];
                    if(empty($commentObject['personal_image'])){
                        $commentObject['personal_image'] = "./Default.jpg";
                    }
                } else {
                    // If not found in 'contributor' table, check 'buddy' table
                    $sql_buddy = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
                    $stmt_buddy = $conn->prepare($sql_buddy);
                    $stmt_buddy->bind_param("s", $row['user_id']);
                    $stmt_buddy->execute();
                    $result_buddy = $stmt_buddy->get_result();
                    $row_buddy = $result_buddy->fetch_assoc();

                    if ($row_buddy) {
                        $commentObject['first_name'] = $row_buddy['bud_first_name'];
                        $commentObject['last_name'] = $row_buddy['bud_given_name'];
                        $commentObject['personal_image'] = $row_buddy['buddy_personal_image'];
                        if(empty($commentObject['personal_image'])){
                            $commentObject['personal_image'] = "./Default.jpg";
                        }
                    }
                }
            }
            $replies = [];
            $repliesQuery = "SELECT reply_id, user_id, reply, time_stamp FROM reply WHERE comm_id = ? ORDER BY time_stamp DESC";
            $stmtReplies = $conn->prepare($repliesQuery);
            $stmtReplies->bind_param("s", $row['comm_id']);
            $stmtReplies->execute();
            $resultReplies = $stmtReplies->get_result();

            while ($rowReply = $resultReplies->fetch_assoc()) {
                $replyObject = [
                    'reply_id' => $rowReply['reply_id'],
                    'user_id' => $rowReply['user_id'],
                    'reply' => $rowReply['reply'],
                    'reply_time_stamp' => $rowReply['time_stamp'],
                    'reply_first_name' => null, // Initialize reply user first name
                    'reply_last_name' => null,  // Initialize reply user last name
                    'reply_personal_image' => null,  // Initialize reply user personal image
                ];

                // Fetch the first name, last name, and personal image of the reply user

                // Check entrepreneur table for reply user info
                $replyUserQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE unique_id = ?";
                $stmtReplyUser = $conn->prepare($replyUserQuery);
                $stmtReplyUser->bind_param("s", $rowReply['user_id']);
                $stmtReplyUser->execute();
                $resultReplyUser = $stmtReplyUser->get_result();

                if ($rowReplyUser = $resultReplyUser->fetch_assoc()) {
                    $replyObject['reply_first_name'] = $rowReplyUser['ent_first_name'];
                    $replyObject['reply_last_name'] = $rowReplyUser['ent_given_name'];
                    $replyObject['reply_personal_image'] = $rowReplyUser['entrepreneur_personal_image'];

                    if (empty($replyObject['reply_personal_image'])) {
                        $replyObject['reply_personal_image'] = "./Default.jpg";
                    }
                }

                // Check contributor table if entrepreneur info not found
                if (empty($replyObject['reply_first_name'])) {
                    $replyUserQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
                    $stmtReplyUser = $conn->prepare($replyUserQuery);
                    $stmtReplyUser->bind_param("s", $rowReply['user_id']);
                    $stmtReplyUser->execute();
                    $resultReplyUser = $stmtReplyUser->get_result();

                    if ($rowReplyUser = $resultReplyUser->fetch_assoc()) {
                        $replyObject['reply_first_name'] = $rowReplyUser['contributor_first_name'];
                        $replyObject['reply_last_name'] = $rowReplyUser['contributor_given_name'];
                        $replyObject['reply_personal_image'] = $rowReplyUser['contributor_personal_image'];

                        if (empty($replyObject['reply_personal_image'])) {
                            $replyObject['reply_personal_image'] = "./Default.jpg";
                        }
                    }
                }

                // Check buddy table if contributor info not found
                if (empty($replyObject['reply_first_name'])) {
                    $replyUserQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
                    $stmtReplyUser = $conn->prepare($replyUserQuery);
                    $stmtReplyUser->bind_param("s", $rowReply['user_id']);
                    $stmtReplyUser->execute();
                    $resultReplyUser = $stmtReplyUser->get_result();

                    if ($rowReplyUser = $resultReplyUser->fetch_assoc()) {
                        $replyObject['reply_first_name'] = $rowReplyUser['bud_first_name'];
                        $replyObject['reply_last_name'] = $rowReplyUser['bud_given_name'];
                        $replyObject['reply_personal_image'] = $rowReplyUser['buddy_personal_image'];

                        if (empty($replyObject['reply_personal_image'])) {
                            $replyObject['reply_personal_image'] = "./Default.jpg";
                        }
                    }
                }

                // Add the reply to the comment's replies array
                $replies[] = $replyObject;
            }

            $commentObject['replies'] = $replies;

            $comments[] = $commentObject;
        }

        // Close the statement
        $stmt->close();

        // Close the database connection
        $conn->close();

        // Return the comments as a JSON response
        header('Content-Type: application/json');
        echo json_encode(['comments' => $comments]);
        exit;
    } else {
        echo "Error in preparing the SQL statement: " . $conn->error;
    }
} else {
    echo "Invalid request. Missing post_id parameter.";
}
?>
