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
    <div class="w3-row w3-padding-small">
        <div class="w3-quarter w3-container">
        </div>
        <div class="w3-half w3-container w3-white">
            <h2>Changing email address</h2>
            <p><strong>Please be careful to enter your new email accurately</strong></p>
            <form class="w3-container" action="/user/do_new_email.php" method="POST">                
                <div class="w3-row w3-padding">
                    <div class="w3-quarter w3-container">
                        <label>Email address</label>
                    </div>
                    <div class="w3-rest w3-container">
                        <input type="email" name="email" placeholder="Email address" style="width: 100%" required>
                    </div>
                </div>

                <div class="w3-row w3-padding">
                    <div class="w3-quarter w3-container"></div>
                    <div class="w3-half w3-container">
                        <button class="w3-button w3-block w3-light-grey w3-border" type="submit">Change e-mail</button>
                    </div>
                    <div class="w3-quarter w3-container"></div>
                </div>
            </form>
        </div>
        <div class="w3-quarter w3-container">
        </div>
    </div>
    <?php
}
include $path . 'includes/footer.php';
