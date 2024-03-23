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

$type = "note";
$short = "n";
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

    // Make a list of where this is referenced from    
    $type = "note";
    $object = read_assoc($mysqli, $type, $return[$type]);
    if ($type == "source") {
        $objectlinks = read_all_assoc($mysqli, 'citation', $treeId);
    } else {
        $objectlinks = read_all_assoc($mysqli, $type . '_link', $treeId);
    }
    $treeData = read_assoc($mysqli, 'tree', $treeId);
    $author = $treeData['author'];
    $line = [];
    if (($type == "submitter") && ($object['id'] == $author)) {
        array_push($line, "Options (Author)");
        array_push($line, "/edit/edit_options.php");
    }
    foreach ($objectlinks as $nl) {
        if ($nl[$type . '_id'] == $object['id']) {
            addDecriptionAndRedirectForObject($mysqli, $line, $nl, $class);
        }
    }
    $nLink = (sizeof($line) / 2);

    // Prepare a default return redirect: Return to tree if there is no reference or to the first
    if ($nLink == 0) {
        $def_redirect = "/tree/tree.php";
    } else {
        $def_redirect = $line[1];
    }
    
    if ($submit == 'cancel') {
        $redirect = $def_redirect;
    } else {
        if ($submit == 'confirm') {
            $redirect = $def_redirect;
        } else {
            $redirect = $submit;
        }
        if ($writeAllowed) {
            $data = read_assoc($mysqli, $type, $return[$type]);
            $data['note'] = notNullInputPost('note');
            update_assoc($mysqli, $type, $return[$type], $data);
        }
    }
    include $path . 'includes/redirect.php';
}
