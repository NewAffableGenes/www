<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
if ($userId < 0) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
    include $path . "includes/error.php";
} else {
    ?>
    <div class="w3-container w3-padding-small"  style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white">
            <h2><strong>This software is under test</strong></h2>
            <p>You use it at your own risk<br>
                Please make sure you download and save a copy of your GEDCOM file regularly<br>
                During development your tree may be lost<br>
                Because of this your subscription is currently free</p>
        </div>
    </div>
    <div class = "w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class = "w3-container w3-white">
            <?php
            if ($subscription == 0) {
                ?>
                <h3>You are not subscribed.</h3>
                <p>You can view other peoples trees if they offer you access</p>
                <p>You will need a subscription to create your own tree</p>
                <?php
            }
            if ($subscription == 1) {
                ?>
                <h3>You are subscribed until <?php echo date("d M Y", $subscribedUntil); ?></h3>
                <p>You can view other peoples trees if they offer you access</p>
                <p>You can create and edit your own tree</p>
                <?php
                if ($subscribedUntil > $currentTime + 60 * 60 * 24 * 92) {
                    ?> <p>You do not need to extend your subscription</p> <?php
                } else {
                    ?> <p>Click below to extend your subscription by 1 year</p> <?php
                }
            } else if ($subscription == 2) {
                ?>
                <h3>Your subscription has lapsed.</h3>
                <p>You may download a copy of your tree as a GEDCOM file until <?php echo date("d M Y", $graceUntil); ?></p>
                <p>You can still view other peoples trees if they offer you access</p>
                <p>You can renew your subscription and pick up where you left off</p>
                <p>Click below to renew your subscription</p>      
                <?php
            } else if ($subscription == 3) {
                ?>
                <h3>Your subscription lapsed a long time ago</h3>
                <p>You can still view other peoples trees if they offer you access</p>
                <?php if ($userTreeId >= 0) { ?>
                    <p>You can renew your subscription and pick up where you left off</p>
                <?php } else { ?>
                    <p>You will a subscription to create your own tree</p>
                <?php } ?>
                <p>Click below to renew your subscription</p>
            <?php } ?>
        </div>
    </div> 
    <?php
    if ($subscribedUntil > $currentTime + 60 * 60 * 24 * 92) {
        ?>
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <a href="/tree/tree.php" class="w3-bar-item w3-button w3-white w3-right" style="width:100%"><b>Exit</b></a>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <a href="/user/do_subscribe.php" class="w3-bar-item w3-button w3-white w3-left" style="width:49%" ><b>Subscribe</b></a>
                <a href="/tree/tree.php" class="w3-bar-item w3-button w3-white w3-right" style="width:49%"><b>Exit</b></a>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white">
            <p>Tip: When you subscribe you can create your own new tree, share your tree, export a beautiful PDF for printing, import a GEDCOM file and save your tree to a GEDCOM file. Meanwhile your data will be stored securely in the cloud.</p>
        </div>
    </div>

    <?php
}
include $path . 'includes/footer.php';


