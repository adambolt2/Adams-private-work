<?php
// Check if the VerificationToken is set in the query parameter



session_start();

// Check if the VerificationToken is set in the session
if (!isset($_SESSION['VerificationToken'])) {
  //  header("Location: index.php");
    exit;
}

$verificationToken = $_SESSION['VerificationToken'];
echo $_SESSION['verification_code'];

// Unset the token when the verification page is loaded
//unset($_SESSION['VerificationToken']);

// Check if the token is passed as a query parameter
if (isset($_GET['token']) && $_GET['token'] === $verificationToken) {
    // Token matches, proceed with the verification process

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle the form submission
        $userEnteredCode = $_POST['verificationCodeUser'];
        $generatedCode = $_SESSION['verification_code'];

        if ($userEnteredCode === $generatedCode) {
            // Verification successful
            // ... (rest of your verification code)
        } else {
            // Verification failed
            header("Location: verification.php?token=$verificationToken");
            exit;
        }
    }
} else {
    // Token doesn't match, handle the error or redirect
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<META NAME="robots" CONTENT="noindex">
    <META NAME="robots" CONTENT="nofollow">
          <title> Buddy ChippedIn</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Replace favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
        <link rel="shortcut icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.0/jquery.min.js">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
     
  
  
    <link rel ="stylesheet" href="css/start_the_reg/resetpassword.css">
    
</head>
<body>
<div class="container"><!-- NOTE THIS COULD HAVE BEEN "container-fluid"  -->
            <div class="row">
                <div class =" body1">
                    <div class="logo_position">  
                        <div class ="col-sm-12 header">                                                   
                            <svg height="100" width="400">
                                <ellipse class="CircleLogo" cx="100" cy="70" rx="85" ry="96"  />
                                <text class="TextLogo" fill="white" font-size="35" font-family="Verdana"
                                x="40" y="70">BuddyChippedIn</text>
                            </svg>   

                        </div>

    <h1>Account Verification</h1>
    <form name="form_verification" method="post" action="verify_process.php" autocomplete="off">
        <label for="verificationCode">Verification Code:</label>
        <input type="text" id="verificationCode" name="verificationCodeUser" required><br>

        <button type="submit" id ="reset-button">Verify</button> <!-- Ignore the fact its called reset-button just for css -->
    </form>


    <div class="box_before_footer">
    <div class="box-content">
        <p>A single place for finding a Business Buddy & Collaboration.<br> Business Formation Or Setting up as an Independent Collaboration Working Group - Law, Tax. Finding 
        Grants, Premises, and the cornerstone for Contribution<br></p>
        <p class="bottom-text">M A K I N G - DOING BUSINESS - E A S I E R</p>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 footer">        
        <p><a href=" ">BuddyChippedin &copy </a> <a href ="">   .... </a><a href ="">   User Agreement </a> <a href ="">   .... </a><a href ="">   Cookie Policy </a> <a href ="">   .... </a><a href ="">   Copyright Policy </a> </p>       
    </div>
</div> 
</body>
</html>
