<?php

include $path . "includes/db_connect.php";
include $path . "includes/class.php";
include $path . "fpdf/fpdf.php";
include $path . "includes/functions.php";
include $path . "includes/complex_date.php";
define('TTF_FONTPATH', $path . 'font');
define('FPDF_FONTPATH', $path . 'fpdf/font');

$backgrounds = [
    ['White', 'background/White.jpg'],
    ['Gray', 'background/Gray.jpg'],
    ['Light Blue', 'background/LtBlue.jpg'],
    ['Paper', 'background/Paper.jpg'],
    ['Parchment', 'background/Parchment.jpg'],
    ['Papyrus', 'background/Papyrus.jpg'],
    ['Sand', 'background/Sand.jpg'],
    ['Distressed', 'background/Distressed.jpg']
];

// Safe defaults
$userId = -1;               // id of currently logged in user
$treeId = -1;               // id of selected tree
$treeOwner = "Unknown";     // Username of owener of the viewed tree
$viewedUserId = -1;         // id of user whose tree this user is viewing
$subscription = 0;          // This users subscription status: 0 = never, 1 = current, 2 = in grace (90d), 3 = expired
$userTreeId = -1;           // id of the tree that belongs to the user
$emailConfirmed = false;    // Records whether the user has confirmed their email address
$writeAllowed = false;      // This users rights to the tree being viewed (treeId)
$exportPdfAllowed = false;  // This users rights to the tree being viewed (treeId)
$downloadAllowed = false;   // This users rights to the tree being viewed (treeId)
// Error variables
$errorTitle = "";       // If we redirect to error this will be the title
$errorMessage = "";     // If we redirect to error this will be the message
$errorRedirect = "";    // If we redirect to error this will be the file to go to when we have clicked OK
// Get the current time
$currentTime = time();
$graceUntil = 0;
$subscribedUntil = 0;

// Check for timeout
if (isset($_SESSION["timeout"])) {
    if ($_SESSION['timeout'] + 60 * 60 < time()) { // 1 Hour timeout
        unset($_SESSION['userId']);
        unset($_SESSION['timeout']);
        $errorTitle = "Logged out";
        $errorMessage = "You have been logged out because of inactivity. "
                . "This is a security feature to protect your data. Please log in again.";
        $errorRedirect = "/user/login.php";
    } else {
        $_SESSION['timeout'] = time();
    }
}

// Now check all the globals I need if I'm logged in
if (isset($_SESSION["userId"])) {
    $userId = $_SESSION["userId"];
    // Get the user's subscription status and whether they have a tree
    $row = read_assoc($mysqli, 'user', $userId);
    if ($row != null) {
        // Check this user's tree_id
        $emailConfirmed = $row['email_confirmed'];
        $userTreeId = $row['tree_id'];
        if ($userTreeId == null) {
            $userTreeId = -1;
        }
        // Check subscription
        $subscribedUntil = $row['subscribed_until'];
        if ($subscribedUntil == null) {
            $subscription = 0; // ie Never subscribed
        } else {
            $subscribedUntil = strtotime($subscribedUntil); // Convert to UNIX time
            $graceUntil = $subscribedUntil + 60 * 60 * 24 * 90;
            if ($currentTime < $subscribedUntil) {
                $subscription = 1; // ie Current subscribed
            } elseif ($currentTime < $graceUntil) {
                $subscription = 2; // ie In grace period
            } else {
                $subscription = 3; // ie Expired subscription and past grace period
            }
        }
        // Who's tree are we viewing
        if (isset($_SESSION["viewedUserId"])) {
            $viewedUserId = $_SESSION["viewedUserId"];
        } else {
            $viewedUserId = $userId;
            $_SESSION["viewedUserId"] = $viewedUserId;
        }
        // Set what rights we have
        if ($viewedUserId == $userId) { // If it is the user's own tree
            $treeId = $userTreeId;
            // - subscription // 0 = never, 1 = current, 2 = in grace (90d), 3 = expired
            $treeOwner = "You";
            if ($subscription == 1) {
                $writeAllowed = true;
                $exportPdfAllowed = true;
                $downloadAllowed = true;
            } else if ($subscription == 2) {
                $downloadAllowed = true;
            } else {
                $treeId = -1;
            }
        } else {   // If it isn't the user's own tree
            // Create a boolean to record whether we can see it 
            $viewedUserOK = false;
            // Get a list of all the trees the user has rights to
            $rights = read_rights($mysqli, $viewedUserId, $userId);
            if ($rights != null) {  // Is there a rights record?
                if ($rights['rights_accepted'] == true) { // Have the rights been accpted?
                    $row = read_assoc($mysqli, 'user', $viewedUserId);
                    if ($row != null) { // Can I see the viewed UserId in the database
                        if ($row['subscribed_until'] != null) { // Is subscribed_until set? 
                            $veiwedUserSubscribedUntil = strtotime($row['subscribed_until']);
                            if ($currentTime < $veiwedUserSubscribedUntil) { // Is the viewed user still subscribed
                                if ($row['tree_id'] != null) { // Does the viewed user have a tree?
                                    $viewedUserOK = true;
                                    $treeId = $row['tree_id'];
                                    $treeOwner = $row['username'];
                                    $writeAllowed = $rights['write_allowed'];
                                    $exportPdfAllowed = $rights['export_pdf_allowed'];
                                    $downloadAllowed = $rights['download_allowed'];
                                }
                            }
                        }
                    }
                }
            }
            if (!$viewedUserOK) {
                $errorTitle = "Access Error";
                $errorMessage = "You cannot access this tree. Please check:<br>"
                        . "Has the owner to allowed you access and not cancelled it?<br>"
                        . "Have you accepted the right to view their tree?<br>"
                        . "Is the owner is still subscribed?<br>"
                        . "Has the owner deleted their tree?<br><br>"
                        . "Your tree will now be selected for viewing";
                $errorRedirect = "/tree/tree.php";
                $viewedUserId = $userId;
                $_SESSION["viewedUserId"] = $viewedUserId;
            }
        }
    } else {
        $errorTitle = "SQL Error";
        $errorMessage = "Please try again or let contact the author";
        $errorRedirect = "/index.php";
    }

    // Create an array $return with all the passed parameters in it
    // if a parameter isn't defined then it is null
    if (strlen($errorTitle) == 0) {
        $return = array();
        foreach ($class as $type => $attr) {
            array_push_assoc($return, $type, getAndCheckId($mysqli, $attr['rtn'], $type, $treeId));
        }
        array_push_assoc($return, "spouse", getAndCheckId($mysqli, 's', 'individual', $treeId));
        array_push_assoc($return, "counter", getAndCheckId($mysqli, 'c', '', $treeId));
        $tType = filter_input(INPUT_GET, 'type');
        if ($tType === false) {
            $tType = null;
        }
        array_push_assoc($return, "type", $tType);
        $tCom = filter_input(INPUT_GET, 'com');
        if ($tCom === false) {
            $tCom = null;
        }
        array_push_assoc($return, "command", $tCom);
    }
}

// $writeAllowed = false;
/*
  if (strlen($errorTitle) > 0) {
  echo "$errorTitle<br>";
  echo "$errorMessage<br>";
  echo "$errorRedirect<br>";
  }
 */

