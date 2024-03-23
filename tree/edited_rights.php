<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

// Check if this is a duplicate
if (strlen($errorTitle) == 0) {
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/shareTree.php";
    if ($submit == 'Make Offer') {
        $receiver_username = notNullInputPost('username');
        $receiver_id = get_user_by_username($mysqli, $receiver_username);        
        if ($receiver_id == null) {
            $errorTitle = "Rights Error";
            $errorMessage = "There is not a user with this username";
            $errorRedirect = "/tree/shareTree.php";
        } else {
            $rights = read_rights($mysqli, $userId, $receiver_id);
            if ($rights != null) {
                $errorTitle = "Rights Error";
                $errorMessage = "You have already given this person rights to your tree. "
                        . "If you want to change them you must delete their current rights "
                        . "and offer the the modified rights";
                $errorRedirect = "/tree/shareTree.php";
            } else {
                if ($userId == $receiver_id) {
                    $errorTitle = "Rights Error";
                    $errorMessage = "You cannot offer rights to yourself - You as the owner have all the rghts you need!";
                    $errorRedirect = "/tree/shareTree.php";
                } else {
                    $data = [
                        "rights_giver" => $userId,
                        "rights_receiver" => $receiver_id,
                        "write_allowed" => (filter_input(INPUT_POST, 'write_allowed') == 'on'),
                        "export_pdf_allowed" => (filter_input(INPUT_POST, 'export_pdf_allowed') == 'on'),
                        "download_allowed" => (filter_input(INPUT_POST, 'download_allowed') == 'on'),
                        "rights_accepted" => false];
                    create_assoc($mysqli, "rights", $data);
                }
            }
        }
    } else {
        $errorTitle = "Rights Error";
        $errorMessage = "Submit not pressed! ($submit)";
        $errorRedirect = "/tree/shareTree.php";
    }
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    include $path . 'includes/redirect.php';
}

