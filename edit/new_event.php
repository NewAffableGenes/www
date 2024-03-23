<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";

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
    $errorTitle = "Tree Error";
    $errorMessage = "You do not have rights to modify this tree";
    $errorRedirect = "/tree/tree.php";
}

$type = $return['type'];
$id = $return[$type];
$createType = "event";
if ((strlen($errorTitle) == 0) && ($id == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    $object = createDefaultEvent($mysqli, $treeId, $type, $id);
    $redirect = "/edit/edit_$createType.php?" . $class[$createType]["rtn"] . "=" . $object;
    include $path . 'includes/redirect.php';
}
include $path . 'includes/footer.php';
