<?php
/* TO DO
  author
 */

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/header.php";
include $path . "includes/navbar.php";

if ((strlen($errorTitle) == 0) && ($userId < 0)) {
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
}

if ((strlen($errorTitle) == 0) && ($treeId < 0)) {
    $errorTitle = "Tree Selection Error";
    $errorMessage = "You have not selected a tree";
    $errorRedirect = "/tree/tree.php";
}

if ((strlen($errorTitle) == 0) && (!$writeAllowed)) {
    $errorTitle = "Tree Selection Error";
    $errorMessage = "You do not have permission to edit this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    $treeData = read_assoc($mysqli, 'tree', $treeId);
    ?>

    <script>
        function rgbToHex(r, g, b) {
            var rgb = b | (g << 8) | (r << 16);
            return "#" + (0x1000000 | rgb).toString(16).substring(1);
        }
        function con_change() {
            var r = document.getElementById("con_r").value;
            var g = document.getElementById("con_g").value;
            var b = document.getElementById("con_b").value;
            document.getElementById("con_tip").innerHTML = rgbToHex(r, g, b);
            document.getElementById("con_sample").style = "background-color: " + rgbToHex(r, g, b) + ";";
        }
        function out_change() {
            var r = document.getElementById("out_r").value;
            var g = document.getElementById("out_g").value;
            var b = document.getElementById("out_b").value;
            document.getElementById("out_tip").innerHTML = rgbToHex(r, g, b);
            document.getElementById("out_sample").style = "background-color: " + rgbToHex(r, g, b) + ";";
        }

        window.onload = function () {
            con_change();
            out_change();
            tfc_change();
            flfc_change();
            olfc_change();
            ofc_change();
        };
    </script>

    <form action="/edit/edited_options.php" method="POST">
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h2>Tree Options</h2>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">

            <!-- Title and Aspect Ratio ------------------------------------------------------------------------------------->

            <div class="w3-row-padding w3-white w3-border">
                <div class="w3-half">
                    <div class="w3-row">
                        <div class="w3-quarter">
                            <h4>Title:</h4>
                        </div>
                        <div class="w3-threequarter">
                            <p><input class="w3-input" type="text" name="titletext"  value= "<?php echo $treeData['title']; ?>"
                                      placeholder="Title" autofocus></p>
                        </div>
                    </div>
                </div>
                <div class="w3-half">
                    <div class="w3-row">
                        <div class="w3-quarter">
                            <h4>Aspect ratio:</h4>
                        </div>
                        <div class="w3-threequarter">
                            <p><select class="w3-select" name="aspectRatio">
                                    <?php
                                    $aspects = aspect_ratios();
                                    $error = 10000000;
                                    $ar = floatval($treeData['aspect_ratio']);
                                    $ari = -1;
                                    for ($i = 0; $i < count($aspects); $i++) {
                                        if (abs($aspects[$i][0] - $ar) < $error) {
                                            $ari = $i;
                                            $error = abs($aspects[$i][0] - $ar);
                                        }
                                    }
                                    for ($i = 0; $i < count($aspects); $i++) {
                                        if ($i == $ari) {
                                            echo '<option value="' . $i . '" selected>' . $aspects[$i][1] . '</option>';
                                        } else {
                                            echo '<option value="' . $i . '">' . $aspects[$i][1] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Root person ------------------------------------------------------------------------------------->

            <div class="w3-row-padding w3-white w3-border">
                <div class="w3-half">
                    <div class="w3-row">
                        <div class="w3-quarter">
                            <h4>Root Person:</h4>
                        </div>
                        <div class="w3-threequarter">
                            <p><select  class="w3-select" name="rootIndi">
                                    <?php
                                    $rt = intval($treeData['root']);
                                    $desc = get_all_descriptions('individual', $mysqli, $treeId);
                                    $array = [];
                                    foreach ($desc as $id => $str) {
                                        array_push($array, [$str, $id]);
                                    }
                                    usort($array, function($a, $b) {
                                        return $a[0] > $b[0];
                                    });
                                    foreach ($array as $value) {
                                        $nid = intval($value[1]);
                                        if ($nid == $rt) {
                                            echo '<option value="' . $nid . '" selected>' . $value[0] . '</option>';
                                        } else {
                                            echo '<option value="' . $nid . '">' . $value[0] . '</option>';
                                        }
                                    }
                                    ?>
                                </select></p>
                        </div>
                    </div>
                </div>
                <div class="w3-half">
                    <p>When the tree is drawn the software will start with one person and work outward through all the relationships to draw as much as it can. This person is the 'Root' person.</p>
                </div>
            </div>

            <!-- Connecting Line  ------------------------------------------------------------------------------------->

            <div class="w3-row-padding w3-white w3-border">
                <div class="w3-quarter">
                    <h4>Connecting Line:</h4>
                </div>
                <div class="w3-quarter">
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Thickness:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="lineThickness">
                                <?php
                                $lt = intval($treeData['line_thickness']);
                                for ($i = 1; $i <= 10; $i++) {
                                    if ($i == $lt) {
                                        echo '<option style="text-align: center" value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="w3-half">
                    <div class="w3-row">
                        <div class="w3-quarter">
                            <p>Colour:</p>
                        </div>
                        <div class="w3-threequarter w3-padding-8">
                            <div id="con_sample" class="w3-padding-8">
                                <p class="w3-center" id="con_tip"><-></p>
                            </div>
                        </div>
                    </div>
                    <div class="w3-row w3-border">
                        <div class="w3-third w3-center">
                            R <input type="range" min="0" max="255" name="con_r" id="con_r" onchange="con_change()" 
                                     oninput="con_change()" value="<?php echo $treeData['connecting_R']; ?>">
                        </div>
                        <div class="w3-third w3-center">
                            G <input type="range" min="0" max="255" name="con_g" id="con_g" onchange="con_change()"
                                     oninput="con_change()" value="<?php echo $treeData['connecting_G']; ?>">
                        </div>
                        <div class="w3-third w3-center">
                            B <input type="range" min="0" max="255" name="con_b" id="con_b" onchange="con_change()" 
                                     oninput="con_change()" value="<?php echo $treeData['connecting_B']; ?>">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Box Outline ------------------------------------------------------------------------------------->

            <div class="w3-row-padding w3-white w3-border">
                <div class="w3-quarter">
                    <h4>Box Outline:</h4>
                </div>
                <div class="w3-quarter">
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Thickness:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="outlineThickness">
                                <?php
                                $lt = intval($treeData['outline_thickness']);
                                if ($treeData['box_outline'] == false) {
                                    $lt = 0;
                                }
                                for ($i = 0; $i <= 10; $i++) {
                                    if ($i == $lt) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="w3-half">
                    <div class="w3-row">
                        <div class="w3-quarter">
                            <p>Colour:</p>
                        </div>
                        <div class="w3-threequarter w3-padding-8">
                            <div id="out_sample" class="w3-padding-8">
                                <p class="w3-center" id="out_tip"><-></p>
                            </div>
                        </div>
                    </div>
                    <div class="w3-row w3-border">
                        <div class="w3-third w3-center">
                            R <input type="range" min="0" max="255" name="out_r" id="out_r" onchange="out_change()" 
                                     oninput="out_change()" value="<?php echo $treeData['outline_R']; ?>">
                        </div>
                        <div class="w3-third w3-center">
                            G <input type="range" min="0" max="255" name="out_g" id="out_g" onchange="out_change()"
                                     oninput="out_change()" value="<?php echo $treeData['outline_G']; ?>">
                        </div>
                        <div class="w3-third w3-center">
                            B <input type="range" min="0" max="255" name="out_b" id="out_b" onchange="out_change()" 
                                     oninput="out_change()" value="<?php echo $treeData['outline_B']; ?>">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Dimensions ------------------------------------------------------------------------------------->

            <div class="w3-row-padding w3-white w3-border">
                <div class="w3-quarter">
                    <h4>Dimensions:</h4>
                </div>
                <div class="w3-quarter">
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Min Box Width:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="minBoxW">
                                <?php
                                $mbw = intval($treeData['min_indi_W']);
                                for ($i = 0; $i <= 200; $i += 20) {
                                    if ($i == $mbw) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Min Box Height:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="minBoxH">
                                <?php
                                $mbh = intval($treeData['min_indi_H']);
                                for ($i = 0; $i <= 200; $i += 20) {
                                    if ($i == $mbh) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="w3-quarter">
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Max Thumbnail Width:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="thumbW">
                                <?php
                                $tw = intval($treeData['thumbnail_W']);
                                for ($i = 20; $i <= 200; $i += 20) {
                                    if ($i == $tw) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Max Thumbnail Height:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="thumbH">
                                <?php
                                $th = intval($treeData['thumbnail_H']);
                                for ($i = 20; $i <= 200; $i += 20) {
                                    if ($i == $th) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="w3-quarter">
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Line Height:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="lineHeight">
                                <?php
                                $lh = intval($treeData['line_height']);
                                for ($i = 10; $i <= 100; $i += 10) {
                                    if ($i == $lh) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Sibling Gap:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="siblingGap">
                                <?php
                                $sg = intval($treeData['sibling_gap']);
                                for ($i = 2; $i <= 20; $i += 2) {
                                    if ($i == $sg) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="w3-row">
                        <div class="w3-half">
                            <p>Marriage Gap:</p>                            
                        </div>
                        <div class="w3-half">
                            <select class="w3-select" name="marriageGap">
                                <?php
                                $mg = intval($treeData['marriage_gap']);
                                for ($i = 2; $i <= 20; $i += 2) {
                                    if ($i == $mg) {
                                        echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                    } else {
                                        echo '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All the Fonts ------------------------------------------------------------------------------------->

            <?php
            $font = read_assoc($mysqli, 'font', $treeData['title_font']);
            $pre = 'tf';
            $fontname = 'Title Font';
            include $path . "edit/edit_font.php";

            $font = read_assoc($mysqli, 'font', $treeData['first_line_font']);
            $pre = 'flf';
            $fontname = 'First Line Font';
            include $path . "edit/edit_font.php";

            $font = read_assoc($mysqli, 'font', $treeData['other_line_font']);
            $pre = 'olf';
            $fontname = 'Other Line Font';
            include $path . "edit/edit_font.php";

            $font = read_assoc($mysqli, 'font', $treeData['originator_font']);
            $pre = 'of';
            $fontname = 'Author Font';
            include $path . "edit/edit_font.php";
            ?>


            <!-- Backgrounds ------------------------------------------------------------------------------------->

            <div class="w3-row-padding w3-white w3-border">
                <div class="w3-quarter">
                    <h4>Backgrounds</h4>
                </div>
                <div class="w3-threequarter">
                    <?php
                    for ($r = 0; $r < 2; $r++) {
                        ?>
                        <div class="w3-row">
                            <?php
                            for ($i = $r * 4; $i < ($r + 1) * 4; $i++) {
                                ?>
                                <div class="w3-quarter">
                                    <label  class="w3-display-container w3-text-black w3-border w3-button" >
                                        <input type="radio" name="background" value="<?php echo $i; ?>" <?php
                                        if ($i == intval($treeData['watermark_media_id'])) {
                                            echo ' checked="checked"';
                                        };
                                        ?>>
                                        <img src="/<?php echo $backgrounds[$i][1]; ?>" alt="Background" style="width:100%">
                                        <div class="w3-display-middle w3-large"><p><?php echo $backgrounds[$i][0]; ?></p></div>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <button class="w3-bar-item w3-button w3-white w3-left" style="width:49%" type="submit" name="submit" 
                        value="confirm">Submit Changes</button>
                <button class="w3-bar-item w3-button w3-white w3-right" style="width:49%" type="submit" name="submit" 
                        value="cancel">Cancel Changes</button>
            </div>
        </div>
    </div>
    </form>
    <?php
}
include $path . 'includes/footer.php';
