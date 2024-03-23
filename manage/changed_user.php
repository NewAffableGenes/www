<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

$userdata = read_assoc($mysqli, 'user', $userId);
if ($userdata['usergroup'] !== 'super') {
    do_log($mysqli, "ALERT: User tried to enter manage.php!");
} else {
    do_log($mysqli, "ALERT: Swapping user");
// Log out first
    if (isset($_SESSION['userId'])) {
        unset($_SESSION['userId']);
        unset($_SESSION['timeout']);
    }
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/tree.php";
    if ($submit == 'cancel') {
        $redirect = "/manage/manage.php";
    } else {
        $userId = filter_input(INPUT_POST, 'selUser', FILTER_VALIDATE_INT);
        $row = read_assoc($mysqli, 'user', $userId);
        $session_id = $row['session_id'];
        session_destroy();
        session_id($session_id);
        session_start();
        session_destroy();
        $unique_id = sha1('xws6' . gethostbyaddr(filter_input(INPUT_SERVER, 'REMOTE_ADDR')) . time() . 'fik2') . '-' . time();
        session_id($unique_id);
        session_start();
        $row['session_id'] = $unique_id;
        update_assoc($mysqli, 'user', $userId, $row);
        $_SESSION["userId"] = $userId;
        $_SESSION['timeout'] = time();
        do_log($mysqli, "Log in - Superuser");
        $redirect = '/tree/tree.php';
    }
}
include $path . 'includes/redirect.php';

