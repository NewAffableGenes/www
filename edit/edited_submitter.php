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

$type = "submitter";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $data = read_assoc($mysqli, $type, $return[$type]);
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/browseLink.php?type=submitter";
    if ($writeAllowed) {
        $data['name'] = notNullInputPost('name');
        $data['registered_RFN'] = notNullInputPost('registered_RFN');
        update_assoc($mysqli, $type, $return[$type], $data);
        $addressData = read_assoc($mysqli, "address", $data['address_id']);
        $addressData['line1'] = notNullInputPost('line1');
        $addressData['line2'] = notNullInputPost('line2');
        $addressData['line3'] = notNullInputPost('line3');
        $addressData['city'] = notNullInputPost('city');
        $addressData['state'] = notNullInputPost('state');
        $addressData['postal_code'] = notNullInputPost('postal_code');
        $addressData['country'] = notNullInputPost('country');
        $addressData['phone'] = notNullInputPost('phone');
        update_assoc($mysqli, "address", $data['address_id'], $addressData);
    }
    include $path . 'includes/redirect.php';
}

