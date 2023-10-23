<?php
// Get the verification code entered by the user


session_start();

$userVerificationCode = $_POST['verificationCodeUser'];
$verificationToken = $_SESSION['VerificationToken'];
// Retrieve the saved verification code from the database based on the user's email
// (You need to implement your database connection and query here)


// Get the verification code entered by the user
$savedVerificationCode = $_SESSION['verification_code'];
//echo $savedVerificationCode;
// Compare the entered code with the saved code
if ($userVerificationCode === $savedVerificationCode) {
    // Verification successful, activate the user account
    // (You need to implement the account activation logic and update the database accordingly)
    $activationToken = bin2hex(random_bytes(16));
    $_SESSION['ActivationToken'] = $activationToken;
    // Redirect the user to the login page or dashboard
    header("Location: home_page_control_panel2.php?token=$activationToken");
    
    exit;
} else {
    // Verification failed, display an error message
    $error = 'Invalid verification code. Please try again.';
    echo "INCORRECT";
    header("Location: verification.php?token=$verificationToken");
}
?>
