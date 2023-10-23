<?php
session_start();
unset($_SESSION['VerificationToken']);
unset($_SESSION['viewedID']);
require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];

$servername = $serv;
$username = $us;
$password = $wrd;
$dbname = $nmedb;
$loginToken = isset($_SESSION['Logintoken']) ? $_SESSION['Logintoken'] : null;
$activationToken = isset($_SESSION['ActivationToken']) ? $_SESSION['ActivationToken'] : null;
$GroupToken = isset($_SESSION['GroupToken']) ? $_SESSION['GroupToken'] : null;

$user_email = $_SESSION['LoginMail']; 


$unique_id =  $_SESSION['UniqueID'];
        
if (isset($_SESSION['GroupToken'])) {
    // The GroupToken is set
    
    $GroupToken = $_SESSION['GroupToken'];
   // echo 'GroupToken is set: ' . $GroupToken;
} else {
    // The GroupToken is not set
    header("Location: index.php");
    exit;


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>SampleText</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/213591b2af.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/control_panel/Create_Group.css">
   
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-12 header">
            <div class="logo-container">
                <h1 class="TextLogo">SampleText</h1>
            </div>
            <div class = "centered-text-container">
                <h2 class="TextLogo">Create Group</h2>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12 form-container">
            <form action="process_group.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" id="groupImageDrop" name="groupImageDrop" accept="image/*" style="display: none;">
                    <div id="drop-area">
                        <div id="image-container">
                            <img id="image-preview" src="" alt="Image Preview">
                            <label for="groupImageDrop" id="image-text">Drag & Drop Image</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="groupImage">Co-worker Group Image</label>
                    <input type="file" id="groupImage" name="groupImage" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="groupTitle">Co-worker Group Title</label>
                    <input type="text" id="groupTitle" name="groupTitle" required>
                </div>
                <div class="form-group">
                    <label for="groupDescription">Co-worker Group Description</label>
                    <textarea id="groupDescription" name="groupDescription" required></textarea>
                </div>


                <div class="form-group">
                    <button type="submit">Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const dropArea = document.getElementById("drop-area");
    const fileInput = document.getElementById("groupImage");
    const fileInputDrop = document.getElementById("groupImageDrop");
    const imagePreview = document.getElementById("image-preview");
    const imageText = document.getElementById("image-text");

    // Update the file input when a file is selected through drag and drop
    fileInputDrop.addEventListener('change', handleFileSelect, false);

    // Update the drag and drop area when a file is selected through the file input
    fileInput.addEventListener('change', handleFileSelectReverse, false);

    function handleFileSelect(e) {
        const files = e.target.files;
        fileInput.files = files;
        fileInputDrop.files = files;

        // Show image preview
        imagePreview.src = URL.createObjectURL(files[0]);
        imagePreview.style.display = "block";

        // Remove drag and drop text
        imageText.style.display = "none";
    }

    function handleFileSelectReverse(e) {
        const files = e.target.files;
        fileInput.files = files;
        fileInputDrop.files = files;

        // Show image preview
        imagePreview.src = URL.createObjectURL(files[0]);
        imagePreview.style.display = "block";

        // Remove drag and drop text
        imageText.style.display = "none";
    }

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop area when a file is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    dropArea.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropArea.classList.add('drag');
    }

    function unhighlight() {
        dropArea.classList.remove('drag');
    }

    function handleDrop(e) {
        e.preventDefault();
        const dt = e.dataTransfer;
        const files = dt.files;

        // Update the file input element (file input for drag and drop)
        fileInput.files = files;
        fileInputDrop.files = files;

        // Show image preview
        imagePreview.src = URL.createObjectURL(files[0]);
        imagePreview.style.display = "block";

        // Remove drag and drop text
        imageText.style.display = "none";
    }
</script>
</body>
</html>
