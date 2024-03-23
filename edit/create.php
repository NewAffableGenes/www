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

if (strlen($errorTitle) == 0) {
    switch ($return['type']) {
        case "individual" :
            $ind = 'i';
            $object = createDefaultIndividual($mysqli, $treeId, nextLabel($mysqli, $treeId, $return['type']));
            break;
        case "family" :
            $ind = 'f';
            $object = createDefaultFamily($mysqli, $treeId, nextLabel($mysqli, $treeId, $return['type']));
            break;
        case "note" :
            $ind = 'n';
            $object = createDefaultNote($mysqli, $treeId, nextLabel($mysqli, $treeId, $return['type']));
            break;
        case "media" :
            $ind = 'm';
            $object = createDefaultMedia($mysqli, $treeId, nextLabel($mysqli, $treeId, $return['type']));
            break;
        case "source" :
            $ind = 'sour';
            $object = createDefaultSource($mysqli, $treeId, nextLabel($mysqli, $treeId, $return['type']));
            break;
        case "submitter" :
            $ind = 'subm';
            $object = createDefaultSubmitter($mysqli, $treeId, nextLabel($mysqli, $treeId, $return['type']));
            break;
        default:
            $errorTitle = "Selection Error";
            $errorMessage = "Unknown type";
            $errorRedirect = "/tree/tree.php";
            break;
    }
    $redirect = '/edit/edit_' . $return['type'] . '.php?' . $ind . '=' . $object;
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    include $path . 'includes/redirect.php';
}
