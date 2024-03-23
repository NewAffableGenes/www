<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

if ((strlen($errorTitle) == 0) && ($treeId < 0)) {
    $errorTitle = "Tree Selection Error";
    $errorMessage = "You have not selected a tree";
    $errorRedirect = "/tree/tree.php";
}

$type = "media";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
    $errorRedirect = "/tree/tree.php";
}

if ((strlen($errorTitle) == 0) && (!$writeAllowed)) {
    $errorTitle = "Write Error: $type";
    $errorMessage = "You do not have write access to this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    ?>

    <div class="w3-row w3-padding-small">
        <div class="w3-quarter w3-container">
        </div>
        <div class="w3-half w3-container w3-white">
            <div class="w3-container w3-padding-small">
                <h1>Media file upload</h1>
            </div>
            <form action="/edit/upload_media.php?m=<?php echo $return[$type]; ?>" method="POST" enctype="multipart/form-data">
                <p>Please select file:</p>
                <input type="file" id="file" name="file">
                <p></p>
                <button class="w3-button w3-block w3-border" 
                        type="submit" name="btn-upload">Upload and Process File</button>
            </form>
            <p><b>This may take some time if the file is large</b></p>
        </div>
        <div class="w3-quarter w3-container">
        </div>
    </div>

    <?php
}
include $path . 'includes/footer.php';
