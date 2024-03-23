<?php
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

if ((strlen($errorTitle) == 0) && ($return['family'] == null)) {
    $errorTitle = "Family Selection Error";
    $errorMessage = "You have not selected a family in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    if (!$writeAllowed) {
        ?>
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-light-grey w3-center">
                You do not have write access to this tree - changes will be ignored
            </div>
        </div>
        <?php
    }
    $famData = read_assoc($mysqli, "family", $return['family']);
    $father = $famData['husband'];
    $mother = $famData['wife'];
    ?>
    <form action="/edit/edited_family.php?f=<?php echo $return['family']; ?>" method="POST">
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h1>Family</h1>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white">
                <div class="w3-row">
                    <div class="w3-col w3-container m5 l5 w3-light-grey w3-padding-small w3-border">Father:<?php
                        if ($father != null) {
                            $fatherData = read_assoc($mysqli, "individual", $father);
                            ?>
                            <div class="w3-row w3-padding-small">
                                <div class="w3-col w3-container w3-right" style="width:75px; padding: 0px">
                                    <button class="w3-button w3-block w3-border" type="submit" name="submit" title="Unlink"
                                               value="/edit/fam_com.php?com=unlinkFather&f=<?php echo $return['family']; ?>"
                                               ><span style='color: blue;'>Unlink</span></button>
                                </div>
                                <div class="w3-rest w3-container" style="padding: 0px">
                                    <button class="w3-button w3-block w3-left-align w3-border" type="submit" name="submit" 
                                               value="/edit/edit_individual.php?i=<?php echo $father; ?>"
                                               ><?php echo $fatherData['name1'] . " " . $fatherData['name2']; ?></button>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="w3-row w3-padding-small">
                                <div class="w3-col w3-container w3-right" style="width:75px; padding: 0px">
                                    <button class="w3-button w3-block w3-border" type="submit" name="submit" title="Unlink"
                                               value="/edit/fam_com.php?com=createFather&f=<?php echo $return['family']; ?>"
                                               ><span style='color: blue;'>Create</span></button>
                                </div>
                                <div class="w3-col w3-container w3-right" style="width:75px; padding: 0px">
                                    <button class="w3-button w3-block w3-border" type="submit" name="submit" title="Unlink"
                                               value="/edit/selectFather.php?f=<?php echo $return['family']; ?>"
                                               ><span style='color: blue;'>Select</span></button>
                                </div>
                                <div class="w3-rest w3-container" style="padding: 0px">
                                    <button class="w3-button w3-block w3-left-align w3-border" type="submit" name="submit" disabled
                                               >Unknown</button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="w3-col w3-container m2 l2 w3-center">  
                        <h2>=</h2>
                    </div>
                    <div class="w3-col w3-container m5 l5 w3-light-grey w3-padding-small w3-border">Mother:<?php
                        if ($mother != null) {
                            $motherData = read_assoc($mysqli, "individual", $mother);
                            ?>
                            <div class="w3-row w3-padding-small">
                                <div class="w3-col w3-container w3-right" style="width:75px; padding: 0px">
                                    <button class="w3-button w3-block w3-border" type="submit" name="submit" title="Unlink"
                                               value="/edit/fam_com.php?com=unlinkMother&f=<?php echo $return['family']; ?>"
                                               ><span style='color: blue;'>Unlink</span></button>
                                </div>
                                <div class="w3-rest w3-container" style="padding: 0px">
                                    <button class="w3-button w3-block w3-left-align w3-border" type="submit" name="submit" 
                                               value="/edit/edit_individual.php?i=<?php echo $mother; ?>"
                                               ><?php echo $motherData['name1'] . " " . $motherData['name2']; ?></button>
                                </div>
                            </div>                            
                        <?php } else { ?>
                            <div class="w3-row w3-padding-small">
                                <div class="w3-col w3-container w3-right" style="width:75px; padding: 0px">
                                    <button class="w3-button w3-block w3-border" type="submit" name="submit" title="Unlink"
                                               value="/edit/fam_com.php?com=createMother&f=<?php echo $return['family']; ?>"
                                               ><span style='color: blue;'>Create</span></button>
                                </div>
                                <div class="w3-col w3-container w3-right" style="width:75px; padding: 0px">
                                    <button class="w3-button w3-block w3-border" type="submit" name="submit" title="Unlink"
                                               value="/edit/selectMother.php?f=<?php echo $return['family']; ?>"
                                               ><span style='color: blue;'>Select</span></button>
                                </div>
                                <div class="w3-rest w3-container" style="padding: 0px">
                                    <button class="w3-button w3-block w3-left-align w3-border" type="submit" name="submit" disabled
                                               >Unknown</button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="w3-row">          
                    <div class="w3-rest" style="min-width: 75px">
                        <p><button class="w3-button w3-block w3-border" type="submit" name="submit" 
                                   value="/edit/fam_com.php?com=swap_parents&f=<?php echo $return['family']; ?>"
                                   >Swap parents (husband for wife and vice versa)</button></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $children = get_children($mysqli, $return['family'], $treeId);
        $childList = [];
        $nC = 0;
        $lastShowMe = 'z';
        $ShowMe = 'x';
        foreach ($children as &$i) {
            if ($i['place_in_family_sibling_list'] != $nC) {
                $i['place_in_family_sibling_list'] = $nC;
                update_assoc($mysqli, "individual", $i['id'], $i);
            }
            $line = ["", "---", "---", "", "", "", ""];
            $line[0] = $i['name1']; // Name
            $birth = get_birth($mysqli, $i['id'], $treeId);
            if ($birth != null) {
                $line[1] = $birth->ShortStyle();
            }
            $death = get_death($mysqli, $i['id'], $treeId);
            if ($death != null) {
                $line[2] = $death->ShortStyle();
            }
            $ShowMe = $i['show_me'];
            $line[3] = $ShowMe;
            if (($ShowMe == 'b') && ($lastShowMe == 'z')) {
                $i['show_me'] = 't';
                $line[3] = "t";
                update_assoc($mysqli, "individual", $i['id'], $i);
            }
            $line[4] = $nC++;
            $line[5] = "";
            if ($nC == sizeOf($children)) {
                $line[5] = "l";
            }
            $line[6] = $i['id'];
            array_push($childList, $line);
            if ($ShowMe != 'n') {
                $lastShowMe = $ShowMe;
            }
        }
        ?>
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-row w3-white w3-padding-small">
                <div class="w3-col" style="width: 100px">
                    <p>Children:</p>
                </div>

                <div class="w3-col w3-padding w3-right" style="width: 200px">
                    <div  style="overflow-x: auto;" >
                        <table class="w3-table" style="width: 100%">
                            <tr>Key:</tr>
                            <tr><div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:25px; padding: 0px"><b>&#x1F884;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Move Up or Left</div>
                            </div></tr>
                            <tr><div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:25px; padding: 0px"><b>&#x1F886;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Move Down or Right</div>
                            </div></tr>
                            <tr><div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:25px; padding: 0px"><b>&#x2924;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Start new stack</div>
                            </div></tr>
                            <tr><div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:25px; padding: 0px"><b>&#x21df;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Show below in stack</div>
                            </div></tr>
                            <tr><div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:25px; padding: 0px"><b>&#x2297;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Do not show</div>
                            </div></tr>
                            <tr><div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:25px; padding: 0px"><b>&#x1F5D1;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Unlink</div>
                            </div></tr>
                        </table>
                    </div>
                </div>

                <div class="w3-rest w3-border">
                    <table class="w3-table" style="width: 100%">
                        <?php foreach ($childList as $key => $row) { ?>
                            <tr>
                            <div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:30px; padding: 0px">
                                    <button class="w3-button w3-block" type="submit" name="submit" 
                                            value="/edit/fam_com.php?com=unlinkFromFamily&i=<?php echo $row[6]; ?>&f=<?php echo $return['family']; ?>" 
                                            title="Delete link"><span style="color: blue;"><b>&#x1F5D1;</b></span></button> 
                                </div>
                                <div class="w3-col w3-container w3-right" style="width:30px; padding: 0px">
                                    <button class="w3-button w3-block" type="submit" name="submit" 
                                            value="/edit/fam_com.php?com=setNoShow&i=<?php echo $row[6]; ?>&f=<?php echo $return['family']; ?>" 
                                            <?php
                                            if ($row[3] != 'n') {
                                                echo ' title="Do not show"><span style="color: blue;"';
                                            } else {
                                                echo ' disabled><span style="color: gray;"';
                                            }
                                            ?>
                                            ><b>&#x2297;</b></span></button> 
                                </div>
                                <div class="w3-col w3-container w3-right" style="width:30px; padding: 0px">
                                    <button class="w3-button w3-block" type="submit" name="submit" 
                                            value="/edit/fam_com.php?com=setBelowInStack&i=<?php echo $row[6]; ?>&f=<?php echo $return['family']; ?>" 
                                            <?php
                                            if (($row[3] != 'b') && ($key != 0)) {
                                                echo ' title="Show below stack"><span style="color: blue;"';
                                            } else {
                                                echo ' disabled><span style="color: gray;"';
                                            }
                                            ?>
                                            ><b>&#x21df;</b></span></button> 
                                </div>
                                <div class="w3-col w3-container w3-right" style="width:30px; padding: 0px">
                                    <button class="w3-button w3-block" type="submit" name="submit" 
                                            value="/edit/fam_com.php?com=setTopOfStack&i=<?php echo $row[6]; ?>&f=<?php echo $return['family']; ?>" 
                                            <?php
                                            if ($row[3] != 't') {
                                                echo ' title="Show at the top of a stack"><span style="color: blue;"';
                                            } else {
                                                echo ' disabled><span style="color: gray;"';
                                            }
                                            ?>
                                            ><b>&#x2924;</b></span></button> 
                                </div>
                                <div class="w3-col w3-container w3-right" style="width:30px; padding: 0px">
                                    <button class="w3-button w3-block" type="submit" name="submit" 
                                            value="/edit/fam_com.php?com=moveDnInFamily&c=<?php echo $row[4]; ?>&f=<?php echo $return['family']; ?>" 
                                            <?php
                                            if ($row[5] == 'l') {
                                                echo ' disabled><span style="color: gray;"';
                                            } else {
                                                echo ' title="Move down or right"><span style="color: blue;"';
                                            }
                                            ?>
                                            ><b>&#x1F886;</b></span></button> 
                                </div>
                                <div class="w3-col w3-container w3-right" style="width:30px; padding: 0px">
                                    <button class="w3-button w3-block" type="submit" name="submit" 
                                            value="/edit/fam_com.php?com=moveUpInFamily&c=<?php echo $row[4]; ?>&f=<?php echo $return['family']; ?>" 
                                            <?php
                                            if ($row[4] == 0) {
                                                echo ' disabled><span style="color: gray;"';
                                            } else {
                                                echo ' title="Move up or left"><span style="color: blue;"';
                                            }
                                            ?>
                                            ><b>&#x1F884;</b></span></button> 
                                </div>
                                <div class="w3-rest w3-container" style="padding: 0px"> <!-- Main button to select family -->
                                    <button class="w3-button w3-block w3-left-align" type="submit" name="submit"  title="Edit"
                                            value="/edit/edit_individual.php?i=<?php echo $row[6]; ?>"
                                            ><?php echo $row[0] . " (b. " . $row[1] . ", d. " . $row[2] . ")"; ?></button>
                                </div>
                            </div>
                            </tr>
                        <?php } ?>
                        <tr>
                        <div class="w3-row w3-border">
                            <div class="w3-rest w3-container" style="padding: 0px"> 
                                <button class="w3-button w3-block w3-left-align" type="submit" name="submit"
                                        value="/edit/fam_com.php?com=addChild&f=<?php echo $return['family']; ?>"
                                        >Create a new Child</button>
                            </div>
                        </div>
                        </tr>
                        <tr>
                        <div class="w3-row w3-border">
                            <div class="w3-rest w3-container" style="padding: 0px"> 
                                <button class="w3-button w3-block w3-left-align" type="submit" name="submit"
                                        value="/edit/selectChild.php?f=<?php echo $return['family']; ?>"
                                        >Link an existing person as a Child</button>
                            </div>
                        </div>
                        </tr>                                
                    </table>
                </div>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-light-grey">
                <p>Tip:</p>
                <p>If you move a child so that they are no longer at the top of a stack with no one below them then any spouse they have cannot be shown. If they have a spouse selected to be shown then that will be disabled. This doesn't remove the spouse it just hides them because they cannot be shown.</p>
            </div>
        </div>
        <?php
        $FALStrings = completeFALList($mysqli, "family", $return['family'], $treeId, $class);
        include $path . "includes/facts_and_links.php";
        ?>
    </form>
    <?php
}
include $path . 'includes/footer.php';
