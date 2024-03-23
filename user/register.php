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
        <h2>Registering with Affable Genes</h2>
        <p><strong>Please be careful to enter your email accurately</strong></p>
        <form class="w3-container" action="/user/do_register.php" method="POST">

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
                    <label>Email address</label>
                </div>
                <div class="w3-rest w3-container">
                    <input type="email" name="email" placeholder="Email address" style="width: 100%" required>
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
                <div class="w3-quarter w3-container">
                    <label>Confirm Password</label>
                </div>
                <div class="w3-rest w3-container">
                    <input type="password" name="confirmpwd" placeholder="Confirm Password" style="width: 100%" required>
                </div>
            </div>
            
            <div class="w3-row w3-padding">
                <div class="w3-quarter w3-container"></div>
                <div class="w3-half w3-container">
                    <button class="w3-button w3-block w3-light-grey w3-border" type="submit">Register</button>
                </div>
                <div class="w3-quarter w3-container"></div>
            </div>
        </form>
        <ul>
            <li>Username must be at least 4 characters long with only English letters and numbers</li>
            <li>Emails must have a valid email format</li>
            <li>Passwords must be at least 6 characters long</li>
            <li>Passwords must contain
                <ul>
                    <li>At least one uppercase letter (A..Z)</li>
                    <li>At least one lowercase letter (a..z)</li>
                </ul>
        </ul>
    </div>
    <div class="w3-quarter w3-container">
    </div>
</div>

<?php
include $path . 'includes/footer.php';
