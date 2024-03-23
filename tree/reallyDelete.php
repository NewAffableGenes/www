<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
$problem = false;
if ($userId < 0) {
    $problem = true;
    $errorTitle = "Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
} else if ($userTreeId < 0) {
    $problem = true;
    $errorTitle = "Error";
    $errorMessage = "You cannot delete your tree because you do not have one";
    $errorRedirect = "/tree/tree.php";
}
if ($problem) {
    include $path . "includes/error.php";
} else {
    $submitBtn = filter_input(INPUT_POST, 'submit');
    $confirmBox = filter_input(INPUT_POST, 'confirmBox');
    if (($submitBtn === "confirm") && ($confirmBox === "DELETE")) {
        do_log($mysqli, "Tree delete confirmed");
        $whenDropped = date("Y-m-d H:i:s");
        update_assoc($mysqli, 'tree', $userTreeId, ['when_dropped_by_user' => $whenDropped]);
        update_assoc($mysqli, 'user', $userId, ['tree_id' => null]);
        ?>
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                <h2>Tree deletion complete</h2>
                <p>Your tree has been deleted</p>
                <a type="button" class="w3-button w3-block" href="/tree/tree.php">OK</a> 
            </div>
        </div>
        <?php
    } else {
        do_log($mysqli, "Tree delete cancelled");
        ?>
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                <h2>Tree deletion cancelled</h2>
                <p>Your tree has not been deleted</p>
                <a type="button" class="w3-button w3-block" href="/tree/tree.php">OK</a> 
            </div>
        </div>
        <?php
    }
}
include $path . 'includes/footer.php';

