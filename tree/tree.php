<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";

if ((strlen($errorTitle) > 0) || ($userId < 0)) {
    if (strlen($errorTitle) == 0) {
        $errorTitle = "Login Error";
        $errorMessage = "You are not logged in";
        $errorRedirect = "/user/login.php";
    }
    include $path . "includes/error.php";
} else {
    if (!$emailConfirmed) {
        ?>
        <div class="w3-container w3-light-grey" style="width: 100%">
            <p>You have not confirmed your email. Unless you do we will not be able to service a request to reset your password if your forget it. <a href="/user/emailConf.php">Click here to resend confirmation</a></p>
        </div>
        <?php
    }
    if ($treeId < 0) {
        ?>
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                <h2>No tree selected</h2>
                <div class="w3-row">
                    <div class="w3-half">
                        <a href="#">
                            <img class="img-thumbnail" src="/img/lg-View_No_Tree.jpg" alt="No tree selected" style="max-width:100%" align="middle" />
                        </a>
                    </div>
                    <div class="w3-half"> 
                        <div class="w3-container">
                            <?php if ($subscription == 1) { ?>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/newTree.php">Start a new tree</a></p>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/uploadGedcom.php">Import GEDCOM file</a></p>
                            <?php } ?>
                            <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/selectTree.php">Select a shared tree</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                <h2>The selected family tree belongs to: <?php echo $treeOwner; ?></h2>
                <div class="w3-row">
                    <div class="w3-half">
                        <p><a href="/tree/viewTree.php">
                                <img class="img-thumbnail" src="/img/lg-View.jpg" alt="Click here to view" style="max-width:100%" align="middle" />
                            </a></p>
                    </div>
                    <div class="w3-half"> 
                        <div class="w3-container">
                            <?php if ($writeAllowed) { ?>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/edit/edit_options.php">Settings for this tree</a></p>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/auto_format.php">Auto-Format - There is no undo</a></p>
                            <?php } if ($exportPdfAllowed) { ?>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/output/exportPDF.php">Export PDF</a></p>
                            <?php } if ($downloadAllowed) { ?>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/saveGedcom.php">Save GEDCOM file</a></p>
                            <?php } ?>
                            <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/reportErrors.php">Look for errors in this tree</a></p>
                            <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/selectTree.php">Select a shared tree</a></p>
                            <?php
                            if ($treeId == $userTreeId) {
                                if ($subscription == 1) {
                                    ?>
                                    <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/shareTree.php">Share or un-share your tree</a></p>
                                <?php } ?>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/deleteTree.php">Delete your tree</a></p>
                            <?php } else { ?>
                                <p><a class="w3-button w3-light-grey w3-border w3-block w3-padding-small" href="/tree/selectOwnTree.php">Select your tree</a></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>    
            </div>
        </div>
    <?php } ?>

    <div class="w3-container w3-padding-small">
        <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
            <?php if ($subscription == 0) { ?>
                <h3>You are not subscribed.</h3>
                <p>You can view other peoples trees if they offer you access<br>
                    You will need your own subscription to create your own tree</p>
                <p><a href="/user/subscribe.php">Click here to subscribe</a></p>
            <?php } ?>
            <?php
            if ($subscription == 1) {
                echo "<h3>You are subscribed until " . date("d M Y", $subscribedUntil) . "</h3>";
                ?>
                <p>You can view other peoples trees if they offer you access<br>
                    You can create and edit your own tree</p>
                <p><a href="/user/subscribe.php">Click here to extend your subscription</a></p>
            <?php } ?>
            <?php if ($subscription == 2) { ?>
                <h3>Your subscription has lapsed.</h3>
                <?php
                if ($userTreeId >= 0) {
                    echo "<p>You may download a copy of your tree as a GEDCOM file until "
                    . date("d M Y", $graceUntil) . "</p>";
                }
                ?>
                <p>You can still view other peoples trees if they offer you access<br>
                    You can renew your subscription and pick up where you left off</p>       
                <p><a href="/user/subscribe.php">Click here to renew your subscription</a></p> 
            <?php } ?>
            <?php if ($subscription == 3) { ?>
                <h3>Your subscription lapsed some time ago</h3>
                <p>You can still view other peoples trees if they offer you access<br>
                    <?php
                    if ($userTreeId >= 0) {
                        echo "You can renew your subscription and pick up where you left off</p>";
                    } else {
                        echo "You will need your own subscription to create your own tree</p>";
                    }
                    ?>
                <p><a href="/user/subscribe.php">Click here to renew your subscription</a></p>
            <?php } ?> 
            <p></p>
            <p>Tip: When you subscribe you can create your own new tree, share your tree, export a beautiful PDF for printing, import a GEDCOM file and save your tree to a GEDCOM file. Meanwhile your data will be stored securely in the cloud.</p>
        </div>
    </div>

    <?php
}
include $path . 'includes/footer.php';

