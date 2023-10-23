<?php
session_start();
$user_email = $_SESSION['LoginMail'];
$membership_type = $_SESSION['Member'];

// Your database connection settings
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

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Retrieve the file path from the corresponding table based on membership type
$file_path_column = '';
if ($membership_type === "entrepreneur") {
    $file_path_column = "entrepreneur_personal_image";
} elseif ($membership_type === "buddy") {
    $file_path_column = "buddy_personal_image";
} elseif ($membership_type === "contributor") {
    $file_path_column = "contributor_personal_image";
}

if (!empty($file_path_column)) {
    $sql = "SELECT {$file_path_column} FROM {$membership_type} WHERE email_address = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();
    var_dump($file_path,$membership_type);
    // Delete the image file if the file path is not empty
    if (!empty($file_path)) {
        if (unlink($file_path)) {
            // Update the file path to be empty
            $sql_update = "UPDATE {$membership_type} SET {$file_path_column} = '' WHERE email_address = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("s", $user_email);
            if ($stmt_update->execute()) {
                echo "Image deleted and file path updated successfully.";
            } else {
                echo "Error updating file path: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            echo "Error deleting image file.";
        }
    } else {
        echo "No image found for deletion.";
    }
} else {
    echo "Membership type not recognized.";
}

$conn->close();
?>
