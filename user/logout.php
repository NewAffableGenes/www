<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
$errorTitle = "Successful Log Out";
$errorMessage = "";
$errorRedirect = "/index.php";
include $path . "includes/error.php";
do_log($mysqli, "Log out");
if (isset($_SESSION['userId'])) {
    unset($_SESSION['userId']);
    unset($_SESSION['timeout']);
}
$userId = -1;
include $path . 'includes/footer.php';
