?>

<div class="w3-row w3-padding-small">
    <div class="w3-quarter w3-container">
    </div>
    <div class="w3-half w3-container">
        <div class="w3-container w3-padding-small w3-white w3-center">
            <h2><?php echo $errorTitle; ?></h2>
            <p><?php echo $errorMessage; ?></p>
        </div>
        <div class="w3-container w3-padding-small w3-white">
            <a href="<?php echo $errorRedirect; ?>" class="w3-button w3-light-grey w3-border w3-block" >OK</a> 
        </div>
    </div>
    <div class="w3-quarter w3-container">
    </div>
</div>

<?php
