<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
// Log out before changing password
if (isset($_SESSION['userId'])) {
    unset($_SESSION['userId']);
    unset($_SESSION['timeout']);
}
include $path . "includes/globals.php";

// Sanitize and validate the data passed in
$errorMessage = "";
$u = filter_input(INPUT_GET, 'u');
$userId = -1;
if ($u === null) {
    $errorMessage = "Error";
} else if ($u === false) {
    $errorMessage = "Error";
} else {
    $row = read_assoc($mysqli, 'user', $u);
    if (count($row) === 0) {
        $errorMessage = "Error";
    } else {
        $userId = $row['id'];
    }
}

$password = filter_input(INPUT_POST, 'password');
if ($password == null) {
    $errorMessage .= "No password<br>";
} elseif ($password == false) {
    $errorMessage .= "Invalid password<br>";
} elseif (strlen($password) < 6) {
    $errorMessage .= "Password too short. 6 characters minumum <br>";
} elseif (strtoupper($password) == $password) {
    $errorMessage .= "Password has no lower case characters <br>";
} elseif (strtolower($password) == $password) {
    $errorMessage .= "Password has no upper case characters <br>";
} else {
    $confirmpwd = filter_input(INPUT_POST, 'confirmpwd');
    if ($confirmpwd == null) {
        $errorMessage .= "No password confirmation<br>";
    } elseif ($confirmpwd == false) {
        $errorMessage .= "Invalid password confirmation<br>";
    } elseif ($password != $confirmpwd) {
        $errorMessage .= "Password does not match confirmation <br>";
    }
}

include $path . "includes/header.php";
$errorTitle = "Password Change";
    $errorRedirect = "/index.php";
if (strlen($errorMessage) === 0)  {
    $row['password'] = hash('sha512', $password);
    update_assoc($mysqli, 'user', $userId, $row);
        $errorMessage .= "Success";
}

include $path . "includes/error.php";
include $path . 'includes/footer.php';
