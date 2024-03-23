
<script>
    function <?php echo $pre; ?>c_change() {
        var r = document.getElementById("<?php echo $pre; ?>c_r").value;
        var g = document.getElementById("<?php echo $pre; ?>c_g").value;
        var b = document.getElementById("<?php echo $pre; ?>c_b").value;
        document.getElementById("<?php echo $pre; ?>c_tip").innerHTML = rgbToHex(r, g, b);
        document.getElementById("<?php echo $pre; ?>c_sample").style = "background-color: " + rgbToHex(r, g, b) + ";";
        var rb = document.getElementById("<?php echo $pre; ?>b_r").value;
        var gb = document.getElementById("<?php echo $pre; ?>b_g").value;
        var bb = document.getElementById("<?php echo $pre; ?>b_b").value;
        var op = document.getElementById("<?php echo $pre; ?>opaque").checked;
        if (op) {
            document.getElementById("<?php echo $pre; ?>b_tip").innerHTML = rgbToHex(rb, gb, bb);
            document.getElementById("<?php echo $pre; ?>b_sample").style = "background-color: " + rgbToHex(rb, gb, bb) + ";";
            document.getElementById("<?php echo $pre; ?>b_r").disabled = false;
            document.getElementById("<?php echo $pre; ?>b_g").disabled = false;
            document.getElementById("<?php echo $pre; ?>b_b").disabled = false;
        } else {
            document.getElementById("<?php echo $pre; ?>b_tip").innerHTML = "Invisible";
            document.getElementById("<?php echo $pre; ?>b_sample").style = "background-color: grey;";
            document.getElementById("<?php echo $pre; ?>b_r").disabled = true;
            document.getElementById("<?php echo $pre; ?>b_g").disabled = true;
            document.getElementById("<?php echo $pre; ?>b_b").disabled = true;
        }
        var ff = document.getElementById("<?php echo $pre; ?>family").value;
        var fs = document.getElementById("<?php echo $pre; ?>size").value;
        var fb = document.getElementById("<?php echo $pre; ?>bold").checked;
        var fi = document.getElementById("<?php echo $pre; ?>italic").checked;
        var fu = document.getElementById("<?php echo $pre; ?>underline").checked;

        var style = "white-space: nowrap; color: " + rgbToHex(r, g, b) + ";";
        if (op) {
            style += "background-color: " + rgbToHex(rb, gb, bb) + ";";
        }
        style += "font-family: '" + ff + "';";
        style += "font-size: " + fs + "px;";
        if (fb) {
            style += "font-weight: bold;";
        }
        if (fi) {
            style += "font-style: italic;";
        }
        if (fu) {
            style += "text-decoration: underline;";
        }

        document.getElementById("<?php echo $pre; ?>c_sample_text").style = style;
    }
</script>

<div class="w3-border">
    <div class="w3-row-padding w3-white">
        <div class="w3-quarter">
            <h4><?php echo $fontname; ?></h4>
        </div>
        <div class="w3-threequarter w3-padding w3-center">
            <div class="w3-display-container w3-border" style="overflow: hidden;">
                <img src='/img/Background.png' alt="Image here" style="width:100%;">
                <div class="w3-display-middle">
                    <p id="<?php echo $pre; ?>c_sample_text" class="w3-padding-small" 
                       style="font-family: 'Courier'; font-size: 24px; font-weight: bold; font-style: italic; 
                       text-decoration: underline; color: red; background-color: blue; white-space: nowrap;"
                       >Sample Text</p>
                </div>
            </div>
        </div>
    </div>

    <div class="w3-row-padding w3-white">
        <div class="w3-quarter">
            <h4> </h4>
        </div>
        <div class="w3-quarter">
            <div class="w3-row">
                <div class="w3-half">
                    <p>Family:</p>                            
                </div>
                <div class="w3-half">
                    <p><select class="w3-select" name="<?php echo $pre; ?>family" id="<?php echo $pre; ?>family" onchange="<?php echo $pre; ?>c_change()">
                            <?php
                            $families = font_families();
                            for ($i = 0; $i < count($families); $i++) {
                                echo '<option value="' . $families[$i][0] . '"';
                                if ($families[$i][0] == strtolower($font['style'])) {
                                    echo ' selected';
                                }
                                echo '>' . $families[$i][1] . '</option>';
                            }
                            ?>
                        </select>
                    </p>
                </div>
            </div>

        </div>

        <div class="w3-half">
            <div class="w3-row">
                <div class="w3-quarter">
                    <p>Colour:</p>
                </div>
                <div class="w3-threequarter w3-padding-8">
                    <div id="<?php echo $pre; ?>c_sample" class="w3-padding-8">
                        <p class="w3-center" id="<?php echo $pre; ?>c_tip"><-></p>
                    </div>
                </div>
            </div>
            <div class="w3-row w3-border">
                <div class="w3-third w3-center">
                    R <input type="range" min="0" max="255" name="<?php echo $pre; ?>c_r" id="<?php echo $pre; ?>c_r" onchange="<?php echo $pre; ?>c_change()" 
                             oninput="<?php echo $pre; ?>c_change()" value="<?php echo $font['font_R']; ?>">
                </div>
                <div class="w3-third w3-center">
                    G <input type="range" min="0" max="255" name="<?php echo $pre; ?>c_g" id="<?php echo $pre; ?>c_g" onchange="<?php echo $pre; ?>c_change()"
                             oninput="<?php echo $pre; ?>c_change()" value="<?php echo $font['font_G']; ?>">
                </div>
                <div class="w3-third w3-center">
                    B <input type="range" min="0" max="255" name="<?php echo $pre; ?>c_b" id="<?php echo $pre; ?>c_b" onchange="<?php echo $pre; ?>c_change()" 
                             oninput="<?php echo $pre; ?>c_change()" value="<?php echo $font['font_B']; ?>">
                </div>
            </div>
        </div>

    </div>

    <div class="w3-row-padding w3-white">
        <div class="w3-quarter">
            <h4> </h4>
        </div>
        <div class="w3-quarter">
            <div class="w3-row">
                <div class="w3-half">
                    <p>Opaque background:</p>                            
                </div>
                <div class="w3-half w3-center">
                    <p><input class="w3-check" name="<?php echo $pre; ?>opaque" id="<?php echo $pre; ?>opaque" 
                              type="checkbox"  onchange="<?php echo $pre; ?>c_change()"<?php
                              if (boolval($font['opaque_background'])) {
                                  echo " checked";
                              }
                              ?>></p>
                </div>
            </div>
        </div>

        <div class="w3-half">
            <div class="w3-row">
                <div class="w3-quarter">
                    <p>Background:</p>
                </div>
                <div class="w3-threequarter w3-padding-8">
                    <div id="<?php echo $pre; ?>b_sample" class="w3-padding-8">
                        <p class="w3-center" id="<?php echo $pre; ?>b_tip"><-></p>
                    </div>
                </div>
            </div>
            <div class="w3-row w3-border">
                <div class="w3-third w3-center">
                    R <input type="range" min="0" max="255" name="<?php echo $pre; ?>b_r" id="<?php echo $pre; ?>b_r" onchange="<?php echo $pre; ?>c_change()" 
                             oninput="<?php echo $pre; ?>c_change()" value="<?php echo $font['background_R']; ?>">
                </div>
                <div class="w3-third w3-center">
                    G <input type="range" min="0" max="255" name="<?php echo $pre; ?>b_g" id="<?php echo $pre; ?>b_g" onchange="<?php echo $pre; ?>c_change()"
                             oninput="<?php echo $pre; ?>c_change()" value="<?php echo $font['background_G']; ?>">
                </div>
                <div class="w3-third w3-center">
                    B <input type="range" min="0" max="255" name="<?php echo $pre; ?>b_b" id="<?php echo $pre; ?>b_b" onchange="<?php echo $pre; ?>c_change()" 
                             oninput="<?php echo $pre; ?>c_change()" value="<?php echo $font['background_B']; ?>">
                </div>
            </div>
        </div>

    </div>

    <div class="w3-row-padding w3-white">
        <div class="w3-quarter">
            <h4> </h4>
        </div>
        <div class="w3-quarter">
            <div class="w3-row">
                <div class="w3-half">
                    <p>Font size:</p>                            
                </div>
                <div class="w3-half w3-center">
                    <p><select class="w3-select" name="<?php echo $pre; ?>size" id="<?php echo $pre; ?>size" onchange="<?php echo $pre; ?>c_change()">
                            <?php
                            $fontsizes = font_sizes();
                            $error = 10000000;
                            $fs = intval($font['size']);
                            $fsi = -1;
                            for ($i = 0; $i < count($fontsizes); $i++) {
                                if (abs($fontsizes[$i][0] - $fs) < $error) {
                                    $fsi = $i;
                                    $error = abs($fontsizes[$i][0] - $fs);
                                }
                            }
                            for ($i = 0; $i < count($fontsizes); $i++) {
                                if ($i == $fsi) {
                                    echo '<option value="' . $fontsizes[$i][0] . '" selected>' . $fontsizes[$i][1] . '</option>';
                                } else {
                                    echo '<option value="' . $fontsizes[$i][0] . '">' . $fontsizes[$i][1] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </p>
                </div>
            </div>
        </div>

        <div class="w3-half">
            <div class="w3-row">
                <div class="w3-third w3-center">
                    <p>Bold: <input class="w3-check" name="<?php echo $pre; ?>bold" id="<?php echo $pre; ?>bold" 
                                    type="checkbox"  onchange="<?php echo $pre; ?>c_change()"<?php
                                    if (boolval($font['bold'])) {
                                        echo " checked";
                                    }
                                    ?>></p>
                </div>
                <div class="w3-third w3-center">
                    <p>Italic: <input class="w3-check" name="<?php echo $pre; ?>italic" id="<?php echo $pre; ?>italic" 
                                      type="checkbox"  onchange="<?php echo $pre; ?>c_change()"<?php
                                      if (boolval($font['oblique'])) {
                                          echo " checked";
                                      }
                                      ?>></p>
                </div>
                <div class="w3-third w3-center">
                    <p>Underline: <input class="w3-check" name="<?php echo $pre; ?>underline" id="<?php echo $pre; ?>underline" 
                                         type="checkbox"  onchange="<?php echo $pre; ?>c_change()"<?php
                                         if (boolval($font['underline'])) {
                                             echo " checked";
                                         }
                                         ?>></p>
                </div>
            </div>
        </div>
    </div>
</div>
