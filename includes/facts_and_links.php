<div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
    <div class="w3-container w3-white w3-padding-small">
        <div class="w3-row w3-white">
            <div class="w3-col" style="width: 100px">
                <p>Facts & Links:</p>
            </div>
            <div class="w3-rest w3-border">
                <div style="overflow-x: auto;">
                    <table class="w3-table" style="width: 100%">
                        <?php
                        foreach ($FALStrings as $row) {
                            if ($row[2] != '') {
                        ?>
                                <tr>
                                    <div class="w3-row w3-border">
                                        <div class="w3-col w3-container w3-right" style="width:50px; padding: 0px">
                                            <button class="w3-button w3-block" type="submit" name="submit" value="<?php echo $row[2]; ?>"><span style='color: blue;'><b>&#x1F5D1;</b></span></button>
                                        </div>
                                        <div class="w3-rest w3-container" style="padding: 0px">
                                            <button class="w3-button w3-block w3-left-align" type="submit" name="submit" value="<?php echo $row[1]; ?>"><?php
                                                                                                                                                        echo $row[0]; // . ' (' . $row[1] .')'; 
                                                                                                                                                        ?></button>
                                        </div>
                                    </div>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <div class="w3-row w3-border">
                                        <button class="w3-button w3-block w3-left-align" type="submit" name="submit" value="<?php echo $row[1]; ?>"><?php
                                                                                                                                                    echo $row[0]; // . ' (' . $row[1] .')'; 
                                                                                                                                                    ?></button>
                                    </div>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>