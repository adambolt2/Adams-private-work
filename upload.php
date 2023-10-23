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

$user_email = $_SESSION['LoginMail'];

if (isset($_FILES["image"])) {
    $targetDir = "ProfileImages/";
    $targetFile = $targetDir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["image"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, and PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            echo "The file " . basename($_FILES["image"]["name"]) . " has been uploaded.";


            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Database connection failed: " . $conn->connect_error);
            }

            // Check which table the user is in and update accordingly
            $sql_membership = "SELECT * FROM entrepreneur WHERE email_address = '$user_email'";
            $result_membership = $conn->query($sql_membership);

            if ($result_membership->num_rows > 0) {
                $membership_type = "entrepreneur";
            } else {
                $sql_membership = "SELECT * FROM buddy WHERE email_address = '$user_email'";
                $result_membership = $conn->query($sql_membership);

                if ($result_membership->num_rows > 0) {
                    $membership_type = "buddy";
                } else {
                    $sql_membership = "SELECT * FROM contributor WHERE email_address = '$user_email'";
                    $result_membership = $conn->query($sql_membership);

                    if ($result_membership->num_rows > 0) {
                        $membership_type = "contributor";
                    }
                }
            }

            $existing_image_path = "";
    
            if ($membership_type === "entrepreneur") {
                // Retrieve existing image path from entrepreneur table
                $sql_existing_path = "SELECT entrepreneur_personal_image FROM entrepreneur WHERE email_address = '$user_email'";
                $result_existing_path = $conn->query($sql_existing_path);
                if ($result_existing_path->num_rows > 0) {
                    $row = $result_existing_path->fetch_assoc();
                    $existing_image_path = $row["entrepreneur_personal_image"];
                }
            } elseif ($membership_type === "buddy") {


                $sql_existing_path = "SELECT buddy_personal_image FROM buddy WHERE email_address = '$user_email'";
                $result_existing_path = $conn->query($sql_existing_path);
                if ($result_existing_path->num_rows > 0) {
                    $row = $result_existing_path->fetch_assoc();
                    $existing_image_path = $row["buddy_personal_image"];
                }
                // Retrieve existing image path from buddy table
                // ...
            } elseif ($membership_type === "contributor") {
               
                $sql_existing_path = "SELECT contributor_personal_image FROM contributor WHERE email_address = '$user_email'";
                $result_existing_path = $conn->query($sql_existing_path);
                if ($result_existing_path->num_rows > 0) {
                    $row = $result_existing_path->fetch_assoc();
                    $existing_image_path = $row["contributor_personal_image"];
                }
               
                // Retrieve existing image path from contributor table
                // ...
            }
        
            // Delete existing image file if a path is present
            if (!empty($existing_image_path)) {
                unlink($existing_image_path); // Delete the file
            }
        
            // Upload new image path to the database
            $file_path = $targetFile;
            $sql_update = "";
        
            if ($membership_type === "entrepreneur") {
                $sql_update = "UPDATE entrepreneur SET entrepreneur_personal_image = '$file_path' WHERE email_address = '$user_email'";
            } elseif ($membership_type === "buddy") {
                $sql_update = "UPDATE buddy SET buddy_personal_image = '$file_path' WHERE email_address = '$user_email'";
            } elseif ($membership_type === "contributor") {
                $sql_update = "UPDATE contributor SET contributor_personal_image = '$file_path' WHERE email_address = '$user_email'";
            }
        
            if ($sql_update) {
                if ($conn->query($sql_update) === TRUE) {
                    echo "File path updated in the corresponding table.";
                } else {
                    echo "Error updating file path in the corresponding table: " . $conn->error;
                }
            
        
        } else {
            echo "Membership type not recognized.";
        }

            $conn->close();
            $Profiletoken = $_SESSION["ProfileTokens"];
            
            header("Location: MyProfile.php?token=$Profiletoken");





            
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

}




error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
