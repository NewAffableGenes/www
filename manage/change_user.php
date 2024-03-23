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

$userdata = read_assoc($mysqli, 'user', $userId);
if ($userdata['usergroup'] !== 'super') {
    do_log($mysqli, "ALERT: User tried to enter manage.php!");
} else {
    do_log($mysqli, "ALERT: Swapping user");
    ?>

    <form action="/manage/changed_user.php" method="POST">
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h2>Change User</h2>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-row-padding w3-white w3-border">
                <div class="w3-half">
                    <div class="w3-row">
                        <div class="w3-quarter">
                            <h4>User:</h4>
                        </div>
                        <div class="w3-threequarter">
                            <p><select  class="w3-select" name="selUser">
                                    <?php
                                    $array = read_all_assoc($mysqli, 'user');
                                    foreach ($array as $value) {
                                        $nid = intval($value['id']);
                                        echo '<option value="' . $value['id'] . '">' . $value['username'] . '</option>';
                                    }
                                    ?>
                                </select></p>
                        </div>
                    </div>
                </div>
                <div class="w3-half">
                    <p>Be Careful!</p>
                </div>
            </div>

        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <button class="w3-bar-item w3-button w3-white w3-left" style="width:49%" type="submit" name="submit" 
                        value="confirm">Go!</button>
                <button class="w3-bar-item w3-button w3-white w3-right" style="width:49%" type="submit" name="submit" 
                        value="cancel">Cancel</button>
            </div>
        </div>
    </div>
    </form>
    <?php
}
include $path . 'includes/footer.php';
