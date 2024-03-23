<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/';
include $path . "includes/globals.php";

if ($userId >= 0) {
    $title = 'Welcome';
    $redirect = "/tree/tree.php";
    include $path . "includes/redirect.php";
} else {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    // phpinfo();
?>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white w3-center">
            <h2><b>Welcome to Affable Genes!</b></h2>
            <p>Click a button below to Register or Log In to start your journey</p>
        </div>
    </div>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-bar">
            <a href="/user/login.php" class="w3-bar-item w3-button w3-white w3-left" style="width:49%"><b>Log in now</b></a>
            <a href="/user/register.php" class="w3-bar-item w3-button w3-white w3-right" style="width:49%"><b>Register</b></a>
        </div>
    </div>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white">
            <div class="w3-third w3-center">
                <img src="<?php echo "img/AGLogo.png?" . time(); ?>" alt="Logo" class="w3-image">
                <p>Beautiful family trees to share!!<br>Even if I say so myself</p>
            </div>
            <div class="w3-twothird w3-center">
                <div class="w3-container w3-padding-small">
                    <div class="w3-container w3-white">
                        <h3>Getting started</h3>
                        <p>Help me test this new application and get a free 3 month subscription. While the code is being developed we suggest you take regular copies of your data by saving a GEDCOM file. That data format is recognised by most family tree software.</p>
                        <p>No guarantees on data retention! Please see the Terms and Conditions by clicking 'About' and follow the link</p>
                        <h3>Sharing</h3>
                        <p>Once you've got your tree you can share it with family and friend. You can choose whether they can just view your tree or, if you like, you can let them add to it too.</p>
                        <p>"It's brill. I'm getting really quick at adding people!!" - Jamie H</p>
                        <p>I am thrilled to have found Affable Genes. I have been searching for ages to find a site which will help me research my family tree - Brilliant! - Helen from Devon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
    include $path . "includes/footer.php";
}
