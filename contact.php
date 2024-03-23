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

    <div class="w3-half w3-container">
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white w3-center">
                <h2>Contact</h2>
                <p>Please send any comments or suggestion to:
                    <a href="mailto:affablegenes@gmail.com">affablegenes@gmail.com</a></p>
            </div>
        </div>
        <div class="w3-container w3-padding-small">
            <a onclick="goBack()" class="w3-button w3-light-grey w3-border w3-block"><b>OK</b></a>
        </div>
    </div>

    <div class="w3-quarter w3-container">
    </div>

</div>

<script>
    function goBack() {
        window.history.back();
    }
</script>

<?php
include $path . "includes/footer.php";
