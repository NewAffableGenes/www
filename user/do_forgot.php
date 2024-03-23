<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
require $path . '../vendor/autoload.php';
// Log out before register
if (isset($_SESSION['userId'])) {
    unset($_SESSION['userId']);
    unset($_SESSION['timeout']);
}
include $path . "includes/globals.php";

$userId = -1;
// Sanitize and validate the data passed in
$error = "";
$n_fail = 0;

$username = filter_input(INPUT_POST, 'username');
$email = filter_input(INPUT_POST, 'email');
if ($username == null) {
    $error .= "Invalid username or email<br>";
    $username = "null";
} elseif ($username == false) {
    $error .= "Invalid username or email<br>";
    $username = "false";
} else {
    $userId = get_user_by_username($mysqli, $username);
    if ($userId != null) {
        $row = read_assoc($mysqli, 'user', $userId);
        $n_fail = $row['n_fail'];
        $last_attempt = strtotime($row['last_attempt']);
        $session_id = $row['session_id'];
        if ($n_fail >= 3) {
            $current_time = time();
            $diff = $current_time - $last_attempt;
            if ($diff < 60 * 60) {
                $error .= "You have failed to provide your data 3 or more times.<br>"
                        . "Please wait for 1 hour from now.<br>"
                        . "This is irritating but it is to protect your data.<br>";
            }
        } else {
            if ($row['email'] !== $email) {
                $error .= "Invalid username or email<br>";
            }
            // All checks ok - so keep $error == ""
            // That will trigger email to be sent
        }
    } else {
        $error .= "Invalid username or email<br>";
    }
}

include $path . "includes/header.php";
$errorTitle = "Forgotten Password";
$errorRedirect = "/user/login.php";
if (strlen($error) > 0) {
    $errorMessage = $error . "<br><br>Remember: If you fail to log in 3 times in a row, you will have to wait 1 hour until your next attempt.";
    do_log($mysqli, "Failed forgotten password: $username");
    if ($userId >= 0) {
        $current_time = date("Y-m-d H:i:s");
        $n_fail++;
        update_assoc($mysqli, 'user', $userId, ['n_fail' => $n_fail, 'last_attempt' => $current_time]);
        // be extra sure we've logged out
        if (isset($_SESSION['userId'])) {
            unset($_SESSION['userId']);
            unset($_SESSION['timeout']);
        }
    }
} else {
    $row = read_assoc($mysqli, 'user', $userId);
    $crypt = substr(hash('sha512', 'Some forgotten spice! ' . $row['username'] . $row['email']), 0, 16);
    $linkurl = 'www.affablegenes.com/reset.php?u=' . strval($userId) . '&sec=' . $crypt;

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->setFrom('affablegenes@gmail.com', 'Affable Genes');
    $mail->addAddress($row['email'], $row['username']);
    $mail->Username = 'AKIAI3CZTNEUQQRZ3XQA';
    $mail->Password = 'AgKIG5gnFTZzG+T6MXLwPbVZ6RawEt7ulddIbO55Cfoz';

// Specify a configuration set. If you do not want to use a configuration
// set, comment or remove the next line.
// $mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'ConfigSet');

    $mail->Host = 'email-smtp.eu-west-1.amazonaws.com';
    $mail->Subject = 'Affable Genes password reset';
    $mail->Body = '<h1>Password reset</h1>' .
            '<p>This email was sent by ' .
            '<a href="http://www.affablegenes.com">affablegenes.com</a> ' .
            'so that you can reset your password.</p>' .
            '<p>To reset your password click this ' .
            '<a href="' . $linkurl . '">link</a> or copy the URL below into your browser:</p>' .
            '<p>' . $linkurl . '</p>';

    $mail->SMTPAuth = true;
// Enable TLS encryption over port 587
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->isHTML(true);

// The alternative email body; this is only displayed when a recipient
// opens the email in a non-HTML email client. The \r\n represents a 
// line break.
    $mail->AltBody = "This email was sent by affablegenes.com so that you can reset your password.\r\n" .
            "Please copy the URL below into your browser to reset your password:\r\n" .
            $linkurl . "\r\n";

    if (!$mail->send()) {
        $errorMessage = "An error has occurred sending the email: " . $mail->ErrorInfo;
    } else {
        $errorMessage = "We will send you an email to reset your password.<br>Please check your email!";
    }
}
include $path . "includes/error.php";
include $path . 'includes/footer.php';
