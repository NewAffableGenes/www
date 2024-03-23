<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle    = "Login Error";
    $errorMessage  = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

if ((strlen($errorTitle) == 0) && (($treeId < 0) || (!$downloadAllowed))) {
    $errorTitle = "Selection Error";
    $errorMessage = "You have not selected a tree that you are allowed to download";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    ?>
    <form 
        <?php echo "class='form-saveGedcom' action=/tree/doSaveGedcom.php method='POST'" ?> >
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                <h2>Tree Saving</h2>
            </div>
            <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                <div class="row">
                    <input type="checkbox" id="write_allowed" name="extras"  checked="checked"
                           style="border: 1px solid black;"> Include AffableGenes formatting data</input>
                </div>
                <div class="row">
                    <p><label class="radio-inline">
                            <input type="radio" name="media_output" value="n" checked="checked"> Do not write embedded media</label> </p>
                    <p><label class="radio-inline">
                            <input type="radio" name="media_output"  value="f"> Embed media - The file will be large!</label></p>
                    <p>Note: The embedded media will only be recoverable by a this software. Remember, you can download the media when you view it if you have permission</p>
                </div>
            </div>
        </div>
        
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <input type="submit" name="submit" value="Export Tree" class="w3-bar-item w3-button w3-white w3-left" style="width:49%">
                <a href="/tree/tree.php" class="w3-bar-item w3-button w3-white w3-right" style="width:49%"><b>Exit</b></a>
            </div>
        </div>
    </form>
    <?php
}
include $path . 'includes/footer.php';
