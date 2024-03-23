<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";

$sec = filter_input(INPUT_GET, 'sec');
$u = filter_input(INPUT_GET, 'u');
$row = read_assoc($mysqli, 'user', $u);
$crypt = substr(hash('sha512', 'Some affable spice! ' . $row['username'] . $row['email']), 0, 16);

$errorTitle = "Confirming email";
$errorRedirect = "/index.php";

if ($sec === $crypt) {
    $row['email_confirmed'] = true;
    update_assoc($mysqli, 'user', $u, $row);
    $errorMessage = "Success. Thank you!";
} else {
    $errorMessage = "Failed";
}

include $path . "includes/error.php";
include $path . 'includes/footer.php';


