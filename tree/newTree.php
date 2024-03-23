<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
$problem = false;
if ($userId < 0) {
    $problem = true;
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
} else if ($userTreeId >= 0) {
    $problem = true;
    $errorTitle = "New Tree Error";
    $errorMessage = "You cannot create a tree because you already have a tree<br>"
            . "If you want to create a new, emplty tree you must delete your current tree. "
            . "However, you may want to download a copy before you delete it!";
    $errorRedirect = "/tree/tree.php";
} else if ($subscription != 1) {
    $problem = true;
    $errorTitle = "New Tree Error";
    $errorMessage = "You cannot create a tree because you do not have a current subscription";
    $errorRedirect = "/tree/tree.php";
}
if ($problem) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    do_log($mysqli, "New Tree requested");
    // Create the tree and assign it to the user
    $treeId = createDefaultTree($mysqli, $userId, $media_path);
    update_assoc($mysqli, 'user', $userId, ['tree_id' => $treeId]);
    $object = createDefaultIndividual($mysqli, $treeId, nextLabel($mysqli, $treeId, 'individual'));
    update_assoc($mysqli, 'tree', $treeId, ['root' => $object]);
    $redirect = '/edit/edit_individual.php?i=' . $object;
    include $path . 'includes/redirect.php';
}
