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
    ?>
    <form action="/tree/reallyDelete.php" method="POST">
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white" style="max-width: 750px; margin: auto">
                <h2>Preparing to delete tree</h2>
                <p><strong>Your have chosen to delete your tree.</strong></p>
                <p><strong>Your tree and all of your data will be gone if you do this!</strong></p>
                <p>Remember you can download a copy of your tree as a GEDCOM. You can use this as a backup which you can restore if you wish. To do this press 'Cancel' and save a copy of your tree before deleting.</p>
                <p>If you really want to delete your tree now type DELETE in capitals in the confirmation box below and press 'DELETE'. Otherwise press 'Cancel'</p>
                <p></p>
                <div class="row">
                    <div class="col-md-4">
                        <p>Confirmation box:  <input type="text" id="confirmBox" name="confirmBox" autofocus></p>
                    </div>
                    <div class="col-md-4">
                        <p><button class="w3-button w3-block w3-light-grey" type="submit" name="submit" 
                                   value="confirm">DELETE !</button></p>
                    </div>
                    <div class="col-md-4">
                        <p><button class="w3-button w3-block w3-light-grey" type="submit" name="submit" 
                                   value="cancel">Cancel</button></p>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
}
include $path . 'includes/footer.php';
?>

