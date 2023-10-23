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

$reply_id = bin2hex(random_bytes(6));


$timestamp = date('Y-m-d H:i:s');

// this works and stays like this forever
$unique_id = $_SESSION['UniqueID'];

$commentId = $_POST['commentId'];
$reply = $_POST['reply'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert the reply into your database or perform other actions
// Here, you might use prepared statements to insert the data safely


$stmt = $conn->prepare("INSERT INTO reply (reply_id, comm_id,user_id, reply, time_stamp) VALUES (?, ?, ?, ?,?)");
$stmt->bind_param("sssss", $reply_id, $commentId, $unique_id,$reply, $timestamp);



if ($stmt->execute()) {
    echo "Comment posted successfully";
    var_dump($_POST);
} else {
    echo "Error posting comment";
}

$stmt->close();
$conn->close();

?>