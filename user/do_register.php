<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
// Log out before register
if (isset($_SESSION['userId'])) {
    unset($_SESSION['userId']);
    unset($_SESSION['timeout']);
}
include $path . "includes/globals.php";

$userId = -1;

// Sanitize and validate the data passed in
$errorMessage = "";

$username = filter_input(INPUT_POST, 'username');
if ($username == null) {
    $errorMessage .= "No username<br>";
} elseif ($username == false) {
    $errorMessage .= "Invalid username<br>";
} elseif (!preg_match('/^\w{4,}$/', $username)) {
    $errorMessage .= "Invalid username<br>";
} else {
    $uid = get_user_by_username($mysqli, $username);
    if ($uid != null) {
        $errorMessage .= "This username is already registered<br>";
    }
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($email == null) {
    $errorMessage .= "No email address<br>";
} elseif ($email == false) {
    $errorMessage .= "Invalid email address<br>";
} else {
    $uid = get_user_by_email($mysqli, $email);
    if ($uid != null) {
        $errorMessage .= "This email is already registered<br>";
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

if (strlen($errorMessage) > 0) {
    include $path . "includes/header.php";
    $errorTitle = "Registration Error";
    $errorRedirect = "/user/register.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $password = hash('sha512', $password);
    $create_time = date("Y-m-d H:i:s");
    $userId = create_assoc($mysqli, 'user', [
        'username' => $username,
        'email' => $email,
        'email_confirmed' => true, // false, TODO - Sort confirmation email
        'password' => $password,
        'create_time' => $create_time,
        'n_fail' => 0,
        'last_attempt' => $create_time,
        'usergroup' => 'user'
    ]);    
    if ($userId != null) {
        $unique_id = sha1('xws6' . gethostbyaddr(filter_input(INPUT_SERVER, 'REMOTE_ADDR')) . time() . 'fik2') . '-' . time();
        session_destroy();
        session_id($unique_id);
        session_start();
        update_assoc($mysqli, 'user', $userId, ['session_id' => $unique_id]);
        $_SESSION["userId"] = $userId;
        $_SESSION['timeout'] = time();
        do_log($mysqli, "Registered $username");
        do_log($mysqli, "Log in");
        // $redirect = '/user/emailConf.php'; // TODO - Sort confirmation email
        $redirect = '/tree/tree.php';
        include $path . 'includes/redirect.php';
    } else {
        include $path . "includes/header.php";
        $errorTitle = "Registration Error";
        $errorMessage = "Error: " . $mysqli->error;
        $errorRedirect = "/user/register.php";
        include $path . "includes/error.php";
        include $path . 'includes/footer.php';
    }
}
