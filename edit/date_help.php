<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    
    ?>


    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-light-grey">
            <p>Tip: Dates may be entered as free text or more normally in one of the standard formats below:</p>
            <ul>
                <li> dd mmm yyyy (e.g. 10 Aug 1965)</li>
                <li> Abt dd mmm yyyy (Abt is short for About)</li>
                <li> Aft dd mmm yyyy (Aft is short for After)</li>
                <li> Bef dd mmm yyyy (Bef is short for Before)</li>
                <li> Bet dd mmm yyyy and dd mmm yyyy (Bet is short for Between)</li>
                <li> Cal dd mmm yyyy (Cal is short for Calculated)</li>
                <li> Est dd mmm yyyy (Est is short for Estimated)</li>
                <li> From dd mmm yyyy to dd mmm yyyy</li>
                <li> From dd mmm yyyy</li>
                <li> To dd mmm yyyy</li>
            </ul>
            <p>To see if the format is recognised click the 'Check Date Format' button. If it is recognised the box to the right of the date entry box will show a tick otherwise it will show a cross</p>
            <p>The input is not case sensitive</p>
        </div>
    </div>

    <?php
}
include $path . 'includes/footer.php';
