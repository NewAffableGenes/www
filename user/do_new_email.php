<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";

$errorTitle = "Changing e-mail";
$errorRedirect = "/index.php";
$errorMessage = "";

if ($userId < 0) {
    $errorMessage = "You are not logged in";
} else {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if ($email === null) {
        $errorMessage = "No email address";
    } elseif ($email === false) {
        $errorMessage = "Invalid email address";
    } else {
        $uid = get_user_by_email($mysqli, $email);
        if ($uid != null) {
            $errorMessage = "This email is already registered";
        }
    }
}

if (strlen($errorMessage) == 0) {
    update_assoc($mysqli, 'user', $userId, ['email' => $email]);
    $errorMessage = "Success!";
    $errorRedirect = "/user/emailConf.php";
}

include $path . "includes/error.php";
include $path . 'includes/footer.php';
