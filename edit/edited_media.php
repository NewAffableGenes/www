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
$short = "m";
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
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/tree.php";
    if ($submit == 'cancel') {
        // $redirect = "/edit/edit_$type.php?$short=" . $return[$type];
        $redirect = "/browseLink.php?type=media";
    } else {
        if ($submit == 'upload') {
            $redirect = "/edit/choose_media.php?$short=" . $return[$type];
        } else if ($submit == 'delete') {
            $redirect = "/edit/really_delete_media.php?$short=" . $return[$type];
        } else if ($submit == 'download') {
            $media = read_assoc($mysqli, $type, $return[$type]);
            $fname = $media['title'] . /* '_' . $media['label'] . */ '.' . $media['format'];
            $fname = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $fname);
            $fname = mb_ereg_replace("([\.]{2,})", '', $fname);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fname); //  basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            ob_clean();
            flush();
            // readfile($media_path . $media['content']);
            echo base64_decode($media['content']); 
            exit;
        } else if ($submit == 'confirm') {
            // $redirect = "/edit/edit_$type.php?$short=" . $return[$type];
            $redirect = "/browseLink.php?type=media";
        } else {
            $redirect = $submit;
        }
        if ($writeAllowed) {
            $data = read_assoc($mysqli, $type, $return[$type]);
            $data['title'] = notNullInputPost('title');
            update_assoc($mysqli, $type, $return[$type], $data);
        }
    }
    include $path . 'includes/redirect.php';
}
