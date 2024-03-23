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

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/tree.php";
    if ($submit == 'cancel')  {
        $redirect = "/edit/edit_individual.php?i=" . $return['individual'];
    } else {
        if ($submit == 'confirm') {
            $redirect = "/edit/edit_individual.php?i=" . $return['individual'];
        } else {
            $redirect = $submit;
        }
        if ($writeAllowed) {
            $data = read_assoc($mysqli, 'individual', $return['individual']);
            $data['name1'] = notNullInputPost('name1');
            $data['name2'] = notNullInputPost('name2');
            $data['name3'] = notNullInputPost('name3');
            $data['sex'] = notNullInputPost('radioGroupSex');
            $data['living'] = notNullInputPost('radioGroupStatus');
            $data['show_me'] = notNullInputPost('radioGroupShow');
            $str = notNullInputPost('boxText');
            $data['box_text'] = str_replace(chr(13), '', $str);
            update_assoc($mysqli, 'individual', $return['individual'], $data);
        }
    }
    include $path . 'includes/redirect.php';
}

