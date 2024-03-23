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

if ((strlen($errorTitle) == 0) && ($return['individual'] == null)) {
    $errorTitle = "Individual Selection Error";
    $errorMessage = "You have not selected an individual in this tree";
    $errorRedirect = "/tree/tree.php";
}

if ((strlen($errorTitle) == 0) && (!$writeAllowed)) {
    $errorTitle = "Permission Error";
    $errorMessage = "You do not have permission to edit this tree";
    $errorRedirect = "/tree/viewTree.php?i=" . $return['individual'];
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/viewTree.php?i=" . $return['individual'];
    $data = read_assoc($mysqli, 'individual', $return['individual']);
    $data['show_me'] = 't';
    update_assoc($mysqli, 'individual', $return['individual'], $data);
    include $path . 'includes/redirect.php';
}
