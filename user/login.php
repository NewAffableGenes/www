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
        <h2>Please log in:</h2>
        <form class="w3-container" action="/user/do_login.php" method="POST">

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
                    <label>Password</label>
                </div>
                <div class="w3-rest w3-container">
                    <input type="password" name="password" placeholder="Password" style="width: 100%" required>
                </div>
            </div>

            <div class="w3-row w3-padding">
                <div class="w3-quarter w3-container"></div>
                <div class="w3-half w3-container">
                    <button class="w3-button w3-block w3-light-grey w3-border" type="submit">Login</button>
                </div>
                <div class="w3-quarter w3-container"></div>
            </div>
        </form>
        <p><a href="/user/forgot.php">Forgotten your password?</a></p>
    </div>
    <div class="w3-quarter w3-container">
    </div>
</div>

<?php
include $path . 'includes/footer.php';
