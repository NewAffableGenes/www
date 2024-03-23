<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
?>

<div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
    <div class="w3-container w3-white">
        <h3>How Affable Genes came to be...</h3>
        <p>When I was researching my own family tree I wanted to print the tree on a poster. I tried a lot of commercial solutions but none seemed good enough. So I wrote my own software to draw the tree and added a family tree editor. When I had that done, I thought it would be fun to be able to share the tree with my family. So I moved the application to the web and added some security.</p>
        <p>Since then I have added features and graphical interfaces to make it fun and easy to use:</p>
        <ul>
            <li>Importing and exporting GEDCOM files</li>
            <li>Checking for errors in the data</li>
            <li>Adding media linked to people, family and events</li>
            <li>Adding graphics to the tree (pictures shown beside the box text)</li>
        </ul>
        <p>Now, I have decided to offer it more widely. If you would like to try the software please do. If you see anything that can be improved or if there are any features you would like to have added please send an email.</p>
        <p>Please send any comment or suggestion for features you would like to see added to: <a href="mailto:affablegenes@gmail.com">affablegenes@gmail.com</a></p>
        <p>Tech: For those who are interested, I wrote early versions of this program in VC++ and Java EE but because I wanted to collaborate with my family online I wrote this version as a classic LAMP (Linux Apache MySQL PHP) application using FPDF v1.81 to create the PDF output all hosted on Amazon Web Services</p>
        <p></p>
        <p>&copy; 2014 - <?php echo date("Y"); ?> AffableGenes.com. Your usage of this site is subject to its published <a href="/tac.php">terms and conditions</a>. Your data will be handled in accordance with our <a href="/privacy.php">privacy policy</a></p>      
    </div>
</div>

<div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
    <a onclick="goBack()" class="w3-button w3-light-grey w3-border w3-block"><b>OK</b></a>
</div>

<script>
    function goBack() {
        window.history.back();
    }
</script>

<?php
// phpinfo();
include $path . "includes/footer.php";

