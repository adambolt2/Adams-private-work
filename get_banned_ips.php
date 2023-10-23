<?php
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

function getBannedIPs($conn) {
    // Select all banned IP addresses from the "banned" table
    $query = "SELECT ip_address FROM banned";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $bannedIPs = [];

    while ($row = $result->fetch_assoc()) {
        $bannedIPs[] = $row['ip_address'];
    }

    return $bannedIPs;
}

// Get the list of banned IP addresses
$bannedIPs = getBannedIPs($conn);

// Send the list as a JSON response
header('Content-Type: application/json');
echo json_encode($bannedIPs);

// Close the database connection
$conn->close();
?>
