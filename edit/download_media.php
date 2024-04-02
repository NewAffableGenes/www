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

$type = "media";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (!$downloadAllowed) {
    $errorTitle = "Access Error";
    $errorMessage = "You do not have rights to download media - Ask the owner";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    // $submit = filter_input(INPUT_POST, 'submit');
    // send the right headers
    $media = read_assoc($mysqli, $type, $return[$type]);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $media['label'] . '.' . $media['format']); //  basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    ob_clean();
    flush();
    // echo base64_decode($media['content']); 
    readfile($media_path . $media['content']);
    exit;
}
