<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
require $path . '../vendor/autoload.php';
include $path . "includes/globals.php";
include $path . "includes/header.php";

if ((strlen($errorTitle) > 0) || ($userId < 0)) {
    if (strlen($errorTitle) == 0) {
        $errorTitle = "Login Error";
        $errorMessage = "You are not logged in";
        $errorRedirect = "/user/login.php";
    }
} else {
    $row = read_assoc($mysqli, 'user', $userId);
    $crypt = substr(hash('sha512', 'Some affable spice! ' . $row['username'] . $row['email']),0,16);
    $linkurl = 'www.affablegenes.com/confirm.php?u=' . strval($userId) . '&sec=' . $crypt;

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
    $mail->Subject = 'Affable Genes email confirmation';
    $mail->Body = '<h1>Email Confirmation</h1>' .
            '<p>This email was sent by ' .
            '<a href="http://www.affablegenes.com">affablegenes.com</a> ' .
            'so that you can confirm your email address. ' .
            'If you confirm your email address you will be able to reset your password should you forget it</p>' .
            '<p>Please click this ' .
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
    $mail->AltBody = "This email was sent by affablegenes.com so that you can confirm your email address.\r\n" .
            "If you confirm your email address you will be able to reset your password should you forget it.\r\n" .
            "Please copy the URL below into your browser:\r\n" .
            $linkurl . "\r\n";

    $errorTitle = "Confirmation e-mail";
    $errorRedirect = "/tree/tree.php";

    if (!$mail->send()) {
        $errorMessage = "An error has occurred sending the email: " . $mail->ErrorInfo;
    } else {
        $errorMessage = 'An email has been sent to the address you gave. '
                . 'Use this to confirm your email address by folllowing the instructions. '
                . 'Unless you do this we will not be able to service a '
                . 'request to reset your password should you forget it.';
    }
}
include $path . "includes/error.php";
include $path . 'includes/footer.php';
