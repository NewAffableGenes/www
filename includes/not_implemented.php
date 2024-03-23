<?php
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";
?>

<div class="w3-row w3-padding-small" style="max-width: 1000px; margin: auto">
    <div class="w3-quarter w3-container">
    </div>
    <div class="w3-half w3-container">
        <div class="w3-container w3-padding-small w3-white">
            <p>This function has not yet been implemented</p>
        </div>
        <div class="w3-container w3-padding-small w3-white">
            <a onclick="goBack()" class="w3-button w3-light-grey w3-border w3-block" >OK</a> 
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
