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
$unique_id = $_SESSION['UniqueID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in or has the necessary permissions
    if (!isset($_SESSION['UniqueID'])) {
        echo "You are not authorized to perform this action.";
        exit;
    }
    
    // Get the post ID from the form submission
    $post_id = $_POST['post_id'];
    
    // Create a database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the post image path from the database
    $sql = "SELECT post_image FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $post_id);
    $stmt->execute();
    $stmt->bind_result($post_image);
    $stmt->fetch();
    $stmt->close();

    // Delete the post from the database
    $sql = "DELETE FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $post_id);

    if ($stmt->execute()) {
        // Delete the associated image file
        if (unlink($post_image)) {
            echo "Post and image deleted successfully!";

            $Profiletoken = $_SESSION["ProfileTokens"];
            
            header("Location: MyProfile.php?token=$Profiletoken");
        } else {

            echo "Post deleted, but there was an error deleting the image.";
        }
    } else {
        echo "Error deleting post: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
