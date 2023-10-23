<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve the values from the POST request
    $reason = $_POST["reason"];
    $reportText = $_POST["reportText"];
    $reported_id = $_SESSION['viewedID'];
    $reporter_id = $_SESSION['UniqueID'];
    $report_id = bin2hex(random_bytes(6));

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

    // Check if the reporter_id and reported_id exist in the same row
    $sql = "SELECT * FROM user_report WHERE reporter_id = ? AND reported_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $reporter_id, $reported_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        // Check if the reported_id exists
        $sql = "SELECT * FROM user_report WHERE reported_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $reported_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            // Increment report_count for all rows with the same reported_id
            $sql = "UPDATE user_report SET report_count = report_count + 1 WHERE reported_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $reported_id);
            $stmt->execute();
            $stmt->close();
        }

        // Insert a new row with the provided information
        $sql = "INSERT INTO user_report (report_id, reporter_id, reported_id, type, reason, report_count) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $report_id, $reporter_id, $reported_id, $reason, $reportText);
        $stmt->execute();
        $stmt->close();
    }

    // Optionally, you can send a response back to the JavaScript code
    $response = ["message" => "Report received successfully"];
    echo json_encode($response);
} else {
    // Handle other HTTP request methods or show an error message
    http_response_code(405); // Method Not Allowed
    echo "Invalid request method";
}
?>
