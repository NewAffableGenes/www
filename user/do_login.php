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
$error = "";
$confirmpwd = "?";
$n_fail = 0;
$password = "Not set";

$username = filter_input(INPUT_POST, 'username');
if ($username == null) {
    $error .= "No username<br>";
    $username = "null";
} elseif ($username == false) {
    $error .= "Invalid username or password<br>";
    $username = "false";
} else {
    $userId = get_user_by_username($mysqli, $username);
    if ($userId != null) {
        $row = read_assoc($mysqli, 'user', $userId);
        if ($row != null) {
            $confirmpwd = $row['password'];
            $n_fail = $row['n_fail'];
            $last_attempt = strtotime($row['last_attempt']);
            $session_id = $row['session_id'];
            if ($n_fail >= 3) {
                $current_time = time();
                $diff = $current_time - $last_attempt;
                if ($diff < 60 * 60) {
                    $error .= "You have failed to log in 3 or more times.<br>"
                            . "Please wait for 1 hour from now.<br>"
                            . "This is irritating but it is to protect your data.<br>";
                }
            }
        } else {
            $error .= "SQL Error<br>";
        }
    } else {
        $error .= "Invalid username or password<br>";
    }
}

if (strlen($error) == 0) {
    $password = filter_input(INPUT_POST, 'password');
    if ($password == null) {
        $error .= "Invalid username or password<br>";
        $password = "null";
    } elseif ($password == false) {
        $error .= "Invalid username or password<br>";
        $password = "false";
    } else {
        $password = hash('sha512', $password);
        if ($password != $confirmpwd) {
            $error .= "Invalid username or password<br>";
        }
    }
}

if (strlen($error) > 0) {
    include $path . "includes/header.php";
    $errorTitle = "Login Error";
    $errorMessage = $error . "<br><br>Remember: If you fail to log in 3 times in a row, you will have to wait 1 hour until your next attempt.";
    $errorRedirect = "/user/login.php";
    include $path . "includes/error.php";
    do_log($mysqli, "Failed log in: $username");
    if ($userId >= 0) {
        $current_time = date("Y-m-d H:i:s");
        $n_fail++;
        update_assoc($mysqli, 'user', $userId, ['n_fail' => $n_fail, 'last_attempt' => $current_time]);
        // be extra sure we've logged out
        if (isset($_SESSION['userId'])) {
            unset($_SESSION['userId']);
            unset($_SESSION['timeout']);
        }
    }
    include $path . 'includes/footer.php';
} else {
    session_destroy();
    session_id($session_id);
    session_start();
    session_destroy();
    $unique_id = sha1('xws6' . gethostbyaddr(filter_input(INPUT_SERVER, 'REMOTE_ADDR')) . time() . 'fik2') . '-' . time();
    session_id($unique_id);
    session_start();
    update_assoc($mysqli, 'user', $userId, ['session_id' => $unique_id]);
    $_SESSION["userId"] = $userId;
    $_SESSION['timeout'] = time();
    do_log($mysqli, "Log in");
    
    // Delete any chunks of files in the database - used during file upload
    delete_chunks($mysqli, $userId);
    
    $redirect = '/tree/tree.php';
    include $path . 'includes/redirect.php';
}
