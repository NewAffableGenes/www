<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

if ((strlen($errorTitle) == 0) && ($treeId < 0)) {
    $errorTitle = "Tree Selection Error";
    $errorMessage = "You have not selected a tree";
    $errorRedirect = "/tree/tree.php";
}

if ((strlen($errorTitle) == 0) && ($return['family'] == null)) {
    $errorTitle = "Family Selection Error";
    $errorMessage = "You have not selected an family in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/tree.php";
    if ($submit == 'cancel') {
        $redirect = "/edit/edit_family.php?f=" . $return['family'];
    } else {
        if ($submit == 'confirm') {
            $redirect = "/edit/edit_family.php?f=" . $return['family'];
        } else {
            $redirect = $submit;
        }
    }
    include $path . 'includes/redirect.php';
}
