<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";

$sec = filter_input(INPUT_GET, 'sec');
$u = filter_input(INPUT_GET, 'u');
$row = read_assoc($mysqli, 'user', $u);
$crypt = substr(hash('sha512', 'Some forgotten spice! ' . $row['username'] . $row['email']), 0, 16);

if ($sec === $crypt) {
    ?>

    <div class="w3-row w3-padding-small">
        <div class="w3-quarter w3-container">
        </div>
        <div class="w3-half w3-container w3-white">
            <h2>Password reset</h2>
            <form class="w3-container" action="/user/do_reset.php?u=<?php echo $u; ?>" method="POST">

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
                        <button class="w3-button w3-block w3-light-grey w3-border" type="submit">Set new password</button>
                    </div>
                    <div class="w3-quarter w3-container"></div>
                </div>
            </form>
            <ul>
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
} else {
    $errorTitle = "Reset Password";
    $errorRedirect = "/index.php";
    $errorMessage = "Failed";
    include $path . "includes/error.php";
}

include $path . 'includes/footer.php';


