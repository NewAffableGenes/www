<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

$userdata = read_assoc($mysqli, 'user', $userId);
if ($userdata['usergroup'] !== 'super') {
    do_log($mysqli, 'ALERT: User $userId tried to enter manage.php!');
} else {
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
                <h3>Trees that have been abandoned:</h3>
                <?php
                $objects = read_all_assoc($mysqli, 'tree');
                foreach ($objects as $treeData) {
                    if ($treeData['when_dropped_by_user'] !== null) {
                        ?>
                        <p><a href = "/manage/really_delete_tree.php?tree=<?php echo $treeData['id']; ?>"
                              ><?php
                                  echo 'Tree dropped by ' . $treeData['created_by'] . ' at ' . $treeData['when_dropped_by_user'] . '. Title: ' . $treeData['title'];
                                  ?></a></p>
                        <?php
                    }
                }
                ?>
            </div>
                <p><a href="/manage/manage.php">Return</a></p>
        </body>
    </html>
    <?php
}
