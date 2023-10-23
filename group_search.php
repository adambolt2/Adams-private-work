<?php
// Check if the 'search' parameter is present in the request
if (isset($_GET['search'])) {
    // Get the search value from the request
    $searchValue = $_GET['search'];

    // Perform a search operation in your database
    // Replace this with your actual database connection and search logic
    $results = searchGroups($searchValue); // Function to search for groups

    // Return the search results as JSON
    echo json_encode($results);
} else {
    // Handle the case where 'search' parameter is missing
    echo json_encode(array('error' => 'Search parameter is missing'));
}

// Replace this function with your actual search logic
function searchGroups($searchValue) {
    // Example: Connect to your database


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

    // Prepare and execute your database query based on the search value
    // Replace this with your specific SQL query
    $sql = "SELECT group_id, group_title, group_desc,group_img FROM group_create WHERE group_title LIKE ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $searchValue = '%' . $searchValue . '%'; // Add wildcards for a partial search
        $stmt->bind_param("s", $searchValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $results = array();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        // Close the database connection and statement
        $stmt->close();
        $conn->close();

        return $results;
    } else {
        return array('error' => 'Error preparing the statement');
    }
}
