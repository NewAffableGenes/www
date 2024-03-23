<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

if (strlen($errorTitle) == 0) {
    $redirect = "/tree/selectTree.php";
    $rights = read_assoc($mysqli, 'rights', $return['counter']);
    if ($rights['rights_receiver'] != $userId) {
        $errorTitle = "Rights Error";
        $errorMessage = "You have not been offered rights to see this tree";
        $errorRedirect = "/tree/selectTree.php";
    } else {
        $rights['rights_accepted'] = true;
        update_assoc($mysqli, "rights", $return['counter'], $rights);
    }
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    include $path . 'includes/redirect.php';
}

