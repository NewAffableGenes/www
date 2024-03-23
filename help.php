<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
if ($userId >= 0) {
    $row = read_assoc($mysqli, 'user', $userId);
    $crypt = substr(hash('sha512', 'Some forgotten spice! ' . $row['username'] . $row['email']), 0, 16);
}
?>

<div class="w3-container w3-padding-small" style="max-width: 500px; margin: auto">
    <div class="w3-container w3-white">
        <h2>Affable Genes</h2>
        <ul>
            <li><a href="/about.php">About</a></li>
            <li><a href="/privacy.php">Privacy</a></li>
            <li><a href="/tac.php">Terms and Conditions</a></li>
            <li><a href="/faq.php">Frequently Asked Questions</a></li>
            <li><a href="/contact.php">Contact</a></li>
            <?php if ($userId >= 0) { ?> 
                <li><a href="/user/change_email.php">Change email address</a></li>
                <li><a href="/reset.php?u=<?php echo strval($userId); ?>&sec=<?php echo $crypt; ?>">Change password</a></li>
                <?php }
                ?>
        </ul>
        
        <div class="w3-container w3-padding-small">
            <a href="index.php" class="w3-button w3-light-grey w3-border w3-block"><b>OK</b></a>
        </div>
    </div>
</div>
<?php
include $path . "includes/footer.php";
