<?php
session_start();


if (isset($_GET['token'])) {
    $urlToken = $_GET['token'];

    // Check if the token is set in the session
    if (isset($_SESSION['token']) && $_SESSION['token'] === $urlToken) {
        // Token matches, allow the user to reset the password
       
        // ...
    } else {
        // Token doesn't match or is not set, handle the error or redirect
        header("Location: index.php");
        exit;
    }
} else {
    // Token not provided in the URL, handle the error or redirect
    header("Location: index.php");
    exit;
}





$newToken = bin2hex(random_bytes(16));

// Store the new token in a session variable
$_SESSION['newToken'] = $newToken;


// Function to generate random verification code
function generateVerificationCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function is_ip_banned($ip) {
    $banned_ips_file = 'banned_password_ips.txt';
    if (file_exists($banned_ips_file)) {
        $banned_ips_data = file_get_contents($banned_ips_file);

        // Check if the file is empty or contains invalid data
        if (!empty($banned_ips_data)) {
            $banned_ips = json_decode($banned_ips_data, true);

            // Check if JSON decoding was successful and the data is an array
            if (is_array($banned_ips)) {
                // For debugging, return the list of banned IPs
                //print_r($banned_ips);
                
                // Check if the IP is banned
                if (isset($banned_ips[$ip]) && $banned_ips[$ip] > time()) {
                    return true;
                }
            }
        }
    }
    return false;
}

// Function to ban an IP// Function to ban an IP
function ban_ip($ip, $duration = 20) {
    $banned_ips_file = 'banned_password_ips.txt';
    $banned_ips = array();

    // Check if the file exists and contains valid data
    if (file_exists($banned_ips_file)) {
        $banned_ips_data = file_get_contents($banned_ips_file);

        if (!empty($banned_ips_data)) {
            $banned_ips = json_decode($banned_ips_data, true);
        }
    }

    // Ban the IP for the specified duration
    $banned_ips[$ip] = time() + $duration;

    // Encode as JSON and store the updated data
    file_put_contents($banned_ips_file, json_encode($banned_ips));
}


// 

$max_attempts = 4; // Maximum number of attempts allowed
$lockout_duration = 10; // Lockout duration in seconds (5 minutes)

require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];

$servername = $serv;
$username = $us;
$password = $wrd;
$dbname = $nmedb;

// Include the PHPMailer library
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = ''; // Initialize a variable to store the message



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Get the user's email address
        $user_email = $_POST['email'];
        $_SESSION['UserEmail'] = $user_email;
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check if the connection was successful
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }

        // Prepare the SQL query to select all emails from the four user type tables
        $the_membership_type = array("contributor", "associate", "buddy", "entrepreneur");
        $number_of_user_type_tables = 4;
        $all_emails = array();

        for ($n = 0; $n < $number_of_user_type_tables; $n++) {
            $sql = "SELECT email_address FROM {$the_membership_type[$n]}";
            $result = mysqli_query($conn, $sql) or die("Couldn't execute the query");

            while ($row = mysqli_fetch_assoc($result)) {
                $all_emails[] = $row['email_address'];
            }
        }

        // Close the database connection
        $conn->close();

        // Check if the email exists in the array of all emails
        if (in_array($user_email, $all_emails)) {
            // Continue with sending the reset code...
            $_SESSION['reset_attempts'] = 0; // Reset the attempts counter

            $verificationCode = generateVerificationCode();
            $_SESSION['Password_code'] = $verificationCode;
     
            $mail = new PHPMailer();
            $mail->isSMTP();

            // Set the SMTP server address and port for Hotmail (Outlook)
            $mail->Host = 'smtp-mail.outlook.com';
            $mail->Port = 587;

            // Set the encryption to TLS
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            // Set the SMTP username (your Hotmail/Outlook email)
            $mail->Username = 'adamtester12@hotmail.com';
            // Set the SMTP password (your Hotmail/Outlook app password)
            $mail->Password = 'Intelcorei7';

            // Set the "From" email address and name
            $mail->setFrom('adamtester12@hotmail.com', 'BCI');

            // Set the recipient's email address
            $mail->addAddress($user_email);

            // Set the email subject and body
            $mail->Subject = 'Password Reset BCI';
            $mail->Body = "Hello,\n\nYour verification code to reset your password is: $verificationCode\n\n Please enter this code on the website to reset your password.\n\nThank you!";

            // Send the email
            if ($mail->send()) {
                
                
                  unset($_SESSION['token']);
                 header('HTTP/1.1 303 See Other');
                 header('Location: password_verify.php?email=' . urlencode($user_email) . '&newToken=' . urlencode($newToken));                // Email sent successfully
                // Redirect the user to the verification page
               
                exit(); // Make sure to exit after redirection to prevent further script execution
            } else {
                // Email not sent
                header('Location: password_verify.php?email=' . urlencode($user_email) . '&newToken=' . urlencode($newToken));                // Email sent successfully
                
                unset($_SESSION['token']);
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            }
        } else {
            // Email not found, increment attempts counter and check for lockout
            if (!isset($_SESSION['reset_attempts'])) {
                $_SESSION['reset_attempts'] = 1;
            } else {
                $_SESSION['reset_attempts']++;
            }

            if ($_SESSION['reset_attempts'] >= $max_attempts) {
                // Lockout the user for the specified duration
                $user_ip = $_SERVER['REMOTE_ADDR'];
                ban_ip($user_ip, $lockout_duration);
                $_SESSION['reset_last_attempt'] = time();
                $_SESSION['reset_attempts'] = 0; // Reset the attempts counter after lockout
            }
            
            $remaining_attempts = max(0, $max_attempts - $_SESSION['reset_attempts']);
            $message = "Sorry, the email address does not exist. Please try again.";
            
        }
    }
}

if (isset($_SESSION['reset_last_attempt']) && (time() - $_SESSION['reset_last_attempt']) < $lockout_duration) {
    // User is locked out, display a message
    $remaining_lockout_time = $lockout_duration - (time() - $_SESSION['reset_last_attempt']);
   // $message = "Too many unsuccessful attempts.";

    // Check if the user's IP is banned
    $user_ip = $_SERVER['REMOTE_ADDR'];
    if (is_ip_banned($user_ip)) {
        $message = "Too many unsuccessful attempts from your IP. Please try again later.";
    }
}
$remainingAttempts = $max_attempts - $_SESSION['reset_attempts'];
if ($remainingAttempts < 0) {
    $remainingAttempts = 0;
}



//echo "MESSAGE: " . $message;
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
<h1>Reset Password</h1>
    <div id="message">
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    </div>
    <?php if (isset($_SESSION['reset_last_attempt']) && (time() - $_SESSION['reset_last_attempt']) >= $lockout_duration): ?>
        <p>Attempts remaining: <?php echo max(0, $max_attempts - $_SESSION['reset_attempts']); ?></p>
    <?php endif; ?>
    <form method="post" id="reset-form">
        <label for="email">Enter your email address:</label>
        <input type="email" name="email" required>
        <button type="submit"id="reset-button">Send Reset Code</button>
    </form>
    <div id="countdown"></div> <!-- This is the "countdown" div -->
    <div class="box_before_footer">
    <div class="box-content">
        <p>A single place for finding a Business Buddy & Collaboration.<br> Business Formation Or Setting up as an Independent Collaboration Working Group - Law, Tax. Finding 
        Grants, Premises, and the cornerstone for Contribution<br></p>
        <p class="bottom-text">M A K I N G - DOING BUSINESS - E A S I E R</p>
    </div>
</div>
<div class="row">
    <div class="footer">        
        <p><a href=" ">BuddyChippedin &copy </a> <a href ="">   .... </a><a href ="">   User Agreement </a> <a href ="">   .... </a><a href ="">   Cookie Policy </a> <a href ="">   .... </a><a href ="">   Copyright Policy </a> </p>       
    </div>
</div> 
    <script>
        
        function startCountdown(secondsLeft, remainingAttempts) {
        const countdownDiv = document.getElementById('countdown');
        const resetButton = document.getElementById('reset-button');
        const messager = document.getElementById('message');
        resetButton.disabled = true; // Disable the button
        let countdownTimer = setInterval(() => {
            secondsLeft--;
            countdownDiv.style.display = 'block';
            countdownDiv.textContent = `Retry in ${secondsLeft} seconds.`;
            if (secondsLeft <= 0) {
                clearInterval(countdownTimer);
                countdownDiv.textContent = '';
                countdownDiv.style.display = 'none';
                resetButton.disabled = false;
                messager.style.display = 'none';
                // Check if there are any remaining attempts
                if (remainingAttempts > 0) {
                    countdownDiv.style.display = 'block';
                    countdownDiv.textContent = `Remaining attempts: ${remainingAttempts}`;
                } else {
                   // countdownDiv.style.display = 'none'; // Hide the countdown div if no more attempts are left
                }
            }
        }, 1000);
        localStorage.setItem('countdownActive', true);
        localStorage.setItem('remainingTime', secondsLeft);
    }

    // Remove the localStorage variables on page load
    document.addEventListener('DOMContentLoaded', function () {
        localStorage.removeItem('countdownActive');
        localStorage.removeItem('remainingTime');
    });

    

    // Replace the PHP code for calling startCountdown with JavaScript variables
    <?php
    if (isset($_SESSION['reset_last_attempt']) && (time() - $_SESSION['reset_last_attempt']) < $lockout_duration):
        // User is locked out, show the remaining lockout time
        $remainingLockoutTime = $lockout_duration - (time() - $_SESSION['reset_last_attempt']);
        // Start the countdown with the remaining lockout time
        echo "startCountdown({$remainingLockoutTime}, {$remainingAttempts});";
    elseif (isset($_SESSION['reset_attempts']) && $_SESSION['reset_attempts'] >= $max_attempts):
        // User is out of attempts, start the countdown with the desired duration (e.g., 10 seconds)
        echo "startCountdown(10, {$remainingAttempts});"; // Replace 10 with the desired countdown duration in seconds
    else:
        // User has not exceeded the max attempts, hide the countdown
        echo "const countdownDiv = document.getElementById('countdown'); countdownDiv.style.display = 'none';";
    endif;
    ?>

    // Check if the form was submitted (after page load)
    document.getElementById('reset-form').addEventListener('submit', function (event) {
        const resetButton = document.getElementById('reset-button');
        resetButton.disabled = true;
    });

    if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}



    </script>

    

</body>
