<?php

$server = filter_input(INPUT_SERVER, 'SERVER_NAME');

if (($server == 'localhost') || ($server == 'www.affablegenes.com')){
    define("HOST", "localhost");     // The host you want to connect to.
    define("USER", "affableg");    // The database username.
    define("PASSWORD", "R1lstone!");    // The database password.
    define("DATABASE", "affableg");    // The database name.
} else if ($server == '35.177.231.21') {
    define("HOST", "localhost");     // The host you want to connect to.
    define("USER", "affableg");    // The database username.
    define("PASSWORD", "R1lstone!");    // The database password.
    define("DATABASE", "affableg");    // The database name.
} else {
    echo "Invalid Server = " . $server . "<br>";
    die();
}
$mysqli = new mysqli('P:' . HOST, USER, PASSWORD, DATABASE);

if ($mysqli->connect_errno) {
    echo "Connect failed: %s\n" . $mysqli->connect_error . "<br>";
    die();
}

if ($mysqli->ping()) {
    // echo "Our connection is ok!" . $mysqli->error . "<br>";
    // die();
} else {
    echo "DB error on Ping: " . $mysqli->error . "<br>";
    die();
}
