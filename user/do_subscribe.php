<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
// In time this will be a pay page where people can pay for the service
// In the mean time give away a week up to a maximum of 4 weeks ahead
// We have variables $subscribedUntil & $currentTime
if ($userId < 0) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
    include $path . "includes/error.php";
} else {
    $maxSubscription = $currentTime + 60 * 60 * 24 * 366 * 2;
    if ($subscribedUntil < $currentTime) {
        $subscribedUntil = $currentTime;
    }
    $subscribedUntil += 60 * 60 * 24 * 366;
    if ($subscribedUntil > $maxSubscription) {
        $subscribedUntil = $maxSubscription;
    }
    $strSubscribedUntil = date("y-m-d" . " 23:59:59", $subscribedUntil); // End of the day to reduce confusion
    update_assoc($mysqli, 'user', $userId, ['subscribed_until' => $strSubscribedUntil]);
    $errorTitle = "Thank you!";
    $errorMessage = "You are subscribed until " . date("d M Y", $subscribedUntil) .
            "<br><br>You can view other peoples trees if they offer you access<br>You can create and edit your own tree";
    $errorRedirect = "/tree/tree.php";
    include $path . "includes/error.php";
}
include $path . 'includes/footer.php';
