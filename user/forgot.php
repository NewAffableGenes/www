<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
?>

<div class="w3-row w3-padding-small">
    <div class="w3-quarter w3-container">
    </div>
    <div class="w3-half w3-container w3-white">
        <h2>You have forgotten your password?</h2>
        <p>Please provide the details requested below and we will send you an email to reset your password if you have confirmed your email address.</p>
        <form class="w3-container" action="/user/do_forgot.php" method="POST">

            <div class="w3-row w3-padding">
                <div class="w3-quarter w3-container">
                    <label>Username</label>
                </div>
                <div class="w3-rest w3-container">
                    <input type="text" name="username" placeholder="Username" style="width: 100%" required autofocus>
                </div>
            </div>

            <div class="w3-row w3-padding">
                <div class="w3-quarter w3-container">
                    <label>email</label>
                </div>
                <div class="w3-rest w3-container">
                    <input type="email" name="email" placeholder="email address" style="width: 100%" required>
                </div>
            </div>

            <div class="w3-row w3-padding">
                <div class="w3-quarter w3-container"></div>
                <div class="w3-half w3-container">
                    <button class="w3-button w3-block w3-light-grey w3-border" type="submit">Request password reset</button>
                </div>
                <div class="w3-quarter w3-container"></div>
            </div>
        </form>
    </div>
    <div class="w3-quarter w3-container">
    </div>
</div>

<?php
include $path . 'includes/footer.php';
