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

if ((strlen($errorTitle) == 0) && (!$writeAllowed)) {
    $errorTitle = "Tree Access Error";
    $errorMessage = "You do not have write access to this tree and cannot create any new entries";
    $errorRedirect = "/tree/tree.php";
}

// The event to delete
$event = $return['event'];
// The type of object to delete the event from
$type = $return['type'];
// The id of object to delete the event from
$id = $return[$type];

if ((strlen($errorTitle) == 0) && (($event == null) || ($type == null) || ($id == null))) {
    $errorTitle = "Selection Error: delete event";
    $errorMessage = "You have not selected the right elements from this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) == 0) {
    delete_entry($mysqli, "event", $event);
    $redirect = "/edit/edit_" . $type . ".php?" . $class[$type]['rtn'] . "=" . $id;
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    include $path . 'includes/redirect.php';
}
