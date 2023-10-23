<?php
// Assuming you have already established a database connection
session_start();
require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];

// Database connection
$connection = mysqli_connect($serv, $us, $wrd, $nmedb);


$myID = $_SESSION['UniqueID']; // Replace this with the actual variable containing your user ID

// Query to fetch group invites matching the user's ID
$sql = "SELECT gi.group_id, gi.invite_status, gc.group_title, gc.group_desc, gc.group_img
        FROM group_invite gi
        JOIN group_create gc ON gi.group_id = gc.group_id
        WHERE gi.user_id = ?";

// Create a prepared statement
$stmt = mysqli_prepare($connection, $sql);

if ($stmt) {
    // Bind the parameter
    mysqli_stmt_bind_param($stmt, "s", $myID);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // Get the results
        $result = mysqli_stmt_get_result($stmt);

        $groupInvites = array();

        while ($row = mysqli_fetch_assoc($result)) {
            // Store the group invites in an array
            $groupInvites[] = array(
                'group_id' => $row['group_id'],
                'invite_status' => $row['invite_status'],
                'group_title' => $row['group_title'],
                'group_desc' => $row['group_desc'],
                'group_img' => $row['group_img']
            );
        }

        // Free the result and close the statement
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);

        // Now, $groupInvites contains the group invites matching the user's ID
    } else {
        echo "Error executing the query: " . mysqli_error($connection);
    }
} else {
    echo "Error preparing the statement: " . mysqli_error($connection);
}


echo json_encode($groupInvites);
// You can now use the $groupInvites array to display or process the group invites.
?>
