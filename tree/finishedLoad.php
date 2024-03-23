<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

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
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . "includes/footer.php";
} else {
    // Call check tree
    check_tree($mysqli, $treeId);

    // If Root isn't set then Select one and call auto-layout
    $treeData = read_assoc($mysqli, 'tree', $treeId);
    if ($treeData['root'] == null) {
        include $path . "includes/header_no_navbar.php";
        ?>
        <form action="/tree/rootSelected.php" method="POST">
            <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
                <div class="w3-container w3-white w3-center">
                    <h2>Select 'Root'</h2>
                </div>
            </div>

            <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">

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

            </div>

            <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
                <div class="w3-bar">
                    <button class="w3-bar-item w3-button w3-white w3-left" style="width:100%" type="submit" name="submit" 
                            value="confirm">Done</button>
                </div>
            </div>
        </div>
        </form>

        <?php
        include $path . "includes/footer_no_navbar.php";
        // Redirect to show_as_many_as_possible($mysqli, $treeId);
    } else {
        // Otherwise if Root is set go straight to /tree/tree.php
        $title = 'Finished Loading';
        $redirect = '/tree/tree.php';
        include $path . "includes/redirect.php";
    }
}
