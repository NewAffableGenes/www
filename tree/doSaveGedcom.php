<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/GEDCOMExporter.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

if ((strlen($errorTitle) == 0) && (($treeId < 0) || (!$downloadAllowed))) {
    $errorTitle = "Selection Error";
    $errorMessage = "You have not selected a tree that you are allowed to download";
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
    $mediaOption = notNullInputPost('media_output'); // n = None, f = full
    $UseExtras = (filter_input(INPUT_POST, 'extras') == 'on');
    // send the right headers
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='. date('Y-m-d') . ' AffableGenes.ged');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    $ge = new GEDCOMExporter($mediaOption, $UseExtras, $class);
    $ge->writeHeader($mysqli, $treeId);
    $ge->writeBody($mysqli, $treeId,$media_path);
    $ge->writeTailer();
    exit();
}
