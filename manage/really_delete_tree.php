<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

$treeId = filter_input(INPUT_GET, 'tree', FILTER_VALIDATE_INT);
$userdata = read_assoc($mysqli, 'user', $userId);
if ($userdata['usergroup'] !== 'super') {
    do_log($mysqli, "ALERT: User tried to enter manage.php!");
} else {
    do_log($mysqli, "ALERT: User deleting tree");
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="Beautiful Genealogy Family Tree Editor and Viewer with PDF Output">
            <meta name="author" content="The Affable Genes Company">
            <title>Affable Genes</title>   
            <link rel="shortcut icon" href="/img/AGfavicon.ico?<?php echo time(); ?>">
            <link rel="stylesheet" href="/css/w3.css">
            <link rel="stylesheet" href="/css/affablegenes.css">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
        </head>
        <body>
            <div class="w3-container">
                <h2>Welcome <?php echo $userdata['username']; ?></h2>
                <p>Getting ready to delete tree <?php echo $treeId; ?> ...</p>
                <?php

                delete_rows($mysqli, $treeId, 'address');
                delete_rows($mysqli, $treeId, 'citation');
                delete_rows($mysqli, $treeId, 'complex_date');
                delete_rows($mysqli, $treeId, 'event');
                delete_rows($mysqli, $treeId, 'family');
                delete_rows($mysqli, $treeId, 'font');
                delete_rows($mysqli, $treeId, 'individual');
                delete_rows($mysqli, $treeId, 'media_link');
                delete_rows($mysqli, $treeId, 'note');
                delete_rows($mysqli, $treeId, 'note_link');
                delete_rows($mysqli, $treeId, 'place');
                delete_rows($mysqli, $treeId, 'source');
                delete_rows($mysqli, $treeId, 'submitter');
                delete_rows($mysqli, $treeId, 'submitter_link');
                delete_rows($mysqli, $treeId, 'media');

                // Finally the tree itself
                delete_entry($mysqli, 'tree', $treeId);
                ?>
            </div>
                <p><a href="/manage/manage.php">Return</a></p>
        </body>
    </html>
    <?php
}
