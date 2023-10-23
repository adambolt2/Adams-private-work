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
function getIPAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

$userIP = getIPAddress();

function isBanned($conn, $ip) {
    // Check if the IP is banned
    $query = "SELECT * FROM banned WHERE ip_address = ? AND time_stamp > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // IP is banned and ban has not expired yet
        return true;
    }

    
    
    return false;
}
function getButtonClass($ip) {
    return isBanned($ip) ? 'disabled-button' : 'button';
}
function banIP($conn, $ip) {
    // Ban the IP for 15 minutes (900 seconds)
    $expirationTime = date('Y-m-d H:i:s', strtotime('+10 seconds'));
    
    // Insert a new record into the "banned" table
    $query = "INSERT INTO banned (ip_address, time_stamp) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $ip, $expirationTime);
    $stmt->execute();
}
function removeExpiredBannedIPs($conn) {
    // Get the current timestamp
    $currentTimestamp = time();

    // Select banned IPs that have expired
    $query = "SELECT ip_address FROM banned WHERE time_stamp <= NOW()";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    // Remove expired bans from the "banned" table
    while ($row = $result->fetch_assoc()) {
        $ip = $row['ip_address'];
        
        // Delete the banned IP record
        $deleteQuery = "DELETE FROM banned WHERE ip_address = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("s", $ip);
        $deleteStmt->execute();

        // Reset the login attempts for the unbanned IP
        $resetQuery = "DELETE FROM attempts WHERE ip_address = ?";
        $resetStmt = $conn->prepare($resetQuery);
        $resetStmt->bind_param("s", $ip);
        $resetStmt->execute();
    }
}

removeExpiredBannedIPs($conn);
function logLoginAttempt($conn, $ip) {
    // Get the current timestamp and format it as a MySQL-compatible datetime string
    $currentTimestamp = date('Y-m-d H:i:s');

    // Define the time window for counting attempts (1 minute)
    $windowStart = date('Y-m-d H:i:s', strtotime('-1 minute', strtotime($currentTimestamp)));

    // Check if the IP is already in the attempts list within the time window
    $query = "SELECT * FROM attempts WHERE ip_address = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the last attempt is within the time window
        if ($row['time_stamp'] >= $windowStart) {
            // If there are already 5 or more attempts within the window, ban the IP for 15 minutes
            if ($row['attempt_count'] >= 5) {
                banIP($conn, $ip);

                // Return the ban status as JSON
                $response = [
                    'ip' => $ip,
                    'banned' => true,
                    'message' => 'IP Address is banned for 15 minutes due to multiple login attempts.'
                ];

                // Send the response as JSON
                header('Content-Type: application/json');
                echo json_encode($response);
                exit; // Terminate the script after sending the response
            }

            // If there are less than 5 attempts within the window, increment the count
            $newCount = $row['attempt_count'] + 1;

            // Update the attempt count in the database
            $updateQuery = "UPDATE attempts SET attempt_count = ?, time_stamp = NOW() WHERE ip_address = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("is", $newCount, $ip);
            $updateStmt->execute();
        } else {
            // Reset the attempt count and update the timestamp, first delete the old IP record
            $deleteQuery = "DELETE FROM attempts WHERE ip_address = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("s", $ip);
            $deleteStmt->execute();

            // Then insert the new IP record with count 1 and the current timestamp
            $insertQuery = "INSERT INTO attempts (ip_address, attempt_count, time_stamp) VALUES (?, 1, NOW())";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("s", $ip);
            $insertStmt->execute();
        }
    } else {
        // If the IP is not in the attempts list, add it with count 1 and the current timestamp
        $query = "INSERT INTO attempts (ip_address, attempt_count, time_stamp) VALUES (?, 1, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $ip);
        $stmt->execute();
    }
}



// Log the login attempt and check if the IP is banned
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isBanned($conn, $userIP)) {
        logLoginAttempt($conn, $userIP);
    }
}

// IP is not banned, return the IP address and ban status as JSON
$response = [
    'ip' => $userIP,
    'banned' => false,
    'message' => ''
];

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
