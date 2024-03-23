<div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
    <div class="w3-container w3-white">
        <div class="w3-row w3-padding-small">
            <div class="w3-quarter">
                <p>Referenced from:</p>
            </div>
            <div class="w3-threequarter">
                <?php
                if ($nLink == 0) {
                echo '<p>Nowhere</p>';
                } else {
                for ($i = 0; $i < $nLink; $i++) {
                ?>
                <p><button class="w3-bar-item w3-button w3-light-grey w3-left w3-block" type="submit" name="submit" 
                            value="<?php echo $line[$i * 2 + 1]; ?>"><?php echo $line[$i * 2]; ?></button></p>
                <?php
                }
                }
                ?>
            </div>
        </div>
    </div>
</div>
