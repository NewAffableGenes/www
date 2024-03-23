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

if ((strlen($errorTitle) == 0) && ($return['individual'] == null)) {
    $errorTitle = "Individual Selection Error";
    $errorMessage = "You have not selected an individual in this tree";
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
    $indiData = read_assoc($mysqli, "individual", $return['individual']);
    ?>
    <form action="/edit/edited_individual.php?i=<?php echo $return['individual']; ?>" method="POST">
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h2>Person</h2>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white">
                <div class="w3-row">
                    <div class="w3-third w3-padding">
                        <p>Title and given name:<input type="text" style="width: 100%;" title="Title and given names" 
                                                       id="name1" name="name1"  value= "<?php echo $indiData['name1']; ?>"
                                                       placeholder="Title and given names" autofocus></p>
                        <p>
                    </div>
                    <div class="w3-third w3-padding">
                        <p>Family name (at birth):<input type="text" style="width: 100%;" title="Family name" 
                                              id="name2" name="name2" value= "<?php echo $indiData['name2']; ?>"
                                              placeholder="Family name" autofocus></p>
                    </div>
                    <div class="w3-third w3-padding">
                        <p>Nickname or suffix:<input type="text" style="width: 100%;" title="Nickname or suffix" 
                                                     id="name3" name="name3"  value= "<?php echo $indiData['name3']; ?>"
                                                     placeholder="Nickname or suffix" autofocus></p>
                    </div>
                </div>

                <div class="w3-row">
                    <div class="w3-third w3-padding">
                        Sex: 
                        <input type="radio" name="radioGroupSex" value="m" 
                        <?php
                        if ($indiData['sex'] == 'm') {
                            echo 'checked="checked"';
                        }
                        ?>> Male
                        <input type="radio" name="radioGroupSex" value="f" 
                        <?php
                        if ($indiData['sex'] == 'f') {
                            echo 'checked="checked"';
                        }
                        ?>> Female
                        <input type="radio" name="radioGroupSex"  value="u"
                        <?php
                        if ($indiData['sex'] == 'u') {
                            echo 'checked="checked"';
                        }
                        ?>> ?
                    </div>

                    <div class="w3-third w3-padding">
                        Show:
                        <input type="radio" name="radioGroupShow" value="t"
                        <?php
                        if ($indiData['show_me'] == 't') {
                            echo 'checked="checked"';
                        }
                        ?>> Top
                        <input type="radio" name="radioGroupShow" value="b" 
                        <?php
                        if ($indiData['show_me'] == 'b') {
                            echo 'checked="checked"';
                        }
                        ?>> Stack
                        <input type="radio" name="radioGroupShow" value="n"
                        <?php
                        if ($indiData['show_me'] == 'n') {
                            echo 'checked="checked"';
                        }
                        ?>> No
                    </div>

                    <div class="w3-third w3-padding">
                        Living: 
                        <input type="radio" name="radioGroupStatus" value="a"
                        <?php
                        if ($indiData['living'] == 'a') {
                            echo 'checked="checked"';
                        }
                        ?>> Alive
                        <input type="radio" name="radioGroupStatus" value="d" 
                        <?php
                        if ($indiData['living'] == 'd') {
                            echo 'checked="checked"';
                        }
                        ?>> Passed
                        <input type="radio" name="radioGroupStatus" value="u"
                        <?php
                        if ($indiData['living'] == 'u') {
                            echo 'checked="checked"';
                        }
                        ?>> ?
                    </div>
                </div>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-row w3-gray w3-border">
                <div class="w3-quarter w3-padding-small">
                    <div class="w3-white w3-padding-small">
                        <?php
                        if ($indiData['l_media_id'] === null) {
                            ?>
                            <p>No media to Left</p>
                            <img src="/img/NoImage.jpg" alt="No preview" style="max-width:100%; max-height: 155px;"/>
                            <button class="w3-button" type="submit" name="submit" 
                                    style="border: 1px solid black; width: 100%; overflow-y: hidden;"
                                    value="/edit/indi_com.php?com=addlmedia&i=<?php echo $return['individual']; ?>">Create / Add</button>
                                    <?php
                                } else {
                                    $object = read_assoc($mysqli, "media", $indiData['l_media_id']);
                                    echo '<p>Left Media:</p>';
                                    if ($object['content'] === null) {// Missing - Allow to upload
                                        echo '<center><img src="/img/Missing.jpg?1" alt="Missing Media" style="max-width:100%; max-height: 120px;" align="middle"></center>';
                                    } else {
                                        // Show the media if we can 
                                        switch ($object['format']) {
                                            case "wav":
                                                echo '<embed src="data:' . $object['mime_type'] . '; base64, ' .
                                                base64_encode(file_get_contents($media_path . $object['content'])) .
                                                '" alt="No preview" width="max-width:100%; max-height: 155px;" align="centre" />';
                                                break;
                                            case "pdf":
                                                echo '<iframe src="data:application/pdf; base64,' .
                                                base64_encode(file_get_contents($media_path . $object['content'])) .
                                                '" alt="No preview" width="max-width:100%; max-height: 155px;" height="300" align="centre"></iframe>';
                                                break;
                                            default:
                                                echo '<center><img src="data:' . $object['mime_type'] . '; base64, ' .
                                                base64_encode(file_get_contents($media_path . $object['content'])) .
                                                '" alt="No preview" style="max-width:100%; max-height: 155px;" align="middle" /></center>';
                                                break;
                                        }
                                    }
                                    ?>
                            <div class="w3-bar">
                                <button class="w3-bar-item w3-button w3-white w3-left" style="border: 1px solid black; width:49%" 
                                        type="submit" name="submit" 
                                        value="/edit/indi_com.php?com=editlmedia&i=<?php echo $return['individual']; ?>">Edit</button>
                                <button class="w3-bar-item w3-button w3-white w3-right" style="border: 1px solid black; width:49%" 
                                        type="submit" name="submit" 
                                        value="/edit/indi_com.php?com=removelmedia&i=<?php echo $return['individual']; ?>">Remove</button>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="w3-half w3-padding-small">
                    <div class="w3-white w3-padding-small">
                        <p>Text box to display:</p>
                        <textarea ROWS=3 class="form-control" 
                                  id="boxText" name="boxText" 
                                  style="width: 100%; border: 1px solid black; resize: none; height: 150px; overflow-y: 
                                  scroll; overflow-x: scroll; text-align: center; white-space: nowrap;"><?php
                                  echo $indiData['box_text'];
                                  ?></textarea>
                        <button class="w3-button" 
                                style="border: 1px solid black; width: 100%; overflow-y: hidden;"
                                type="submit" name="submit" 
                                value="/edit/indi_com.php?com=autoBoxText&i=<?php echo $return['individual']; ?>">Auto populate text box
                        </button>

                    </div>
                </div>
                <div class="w3-quarter w3-padding-small">
                    <div class="w3-white w3-padding-small">
                        <?php
                        if ($indiData['r_media_id'] === null) {
                            ?>
                            <p>No media to Right</p>
                            <img src="/img/NoImage.jpg" alt="No preview" style="max-width:100%; max-height: 155px;"/>
                            <button class="w3-button" type="submit" name="submit" 
                                    style="border: 1px solid black; width: 100%; overflow-y: hidden;"
                                    value="/edit/indi_com.php?com=addrmedia&i=<?php echo $return['individual']; ?>">Create / Add</button>
                                    <?php
                                } else {
                                    $object = read_assoc($mysqli, "media", $indiData['r_media_id']);
                                    echo '<p>Right Media:</p>';
                                    if ($object['content'] === null) {// Missing - Allow to upload
                                        echo '<center><img src="/img/Missing.jpg?1" alt="Missing Media" style="max-width:100%; max-height: 120px;" align="middle"></center>';
                                    } else {
                                        // Show the media if we can 
                                        // echo $object['format'] . ' ' . $media_path . sprintf('%08d', $treeId) . '<br>';
                                        switch ($object['format']) {
                                            case "wav":
                                                echo '<embed src="data:' . $object['mime_type'] . '; base64, ' .
                                                base64_encode(file_get_contents($media_path . $object['content'])) .
                                                '" alt="No preview" width="max-width:100%; height: 155px;" align="centre" />';
                                                break;
                                            case "pdf":
                                                echo '<iframe src="data:application/pdf; base64,' .
                                                base64_encode(file_get_contents($media_path . $object['content'])) .
                                                '" alt="No preview" width="max-width:100%; height: 155px;" height="300" align="centre"></iframe>';
                                                break;
                                            default:
                                                echo '<center><img src="data:' . $object['mime_type'] . '; base64, ' .
                                                base64_encode(file_get_contents($media_path . $object['content'])) .
                                                '" alt="No preview" style="max-width:100%; height: 155px;" align="middle" /></center>';
                                                break;
                                        }
                                    }
                                    ?>
                            <div class="w3-bar">
                                <button class="w3-bar-item w3-button w3-white w3-left" style="border: 1px solid black; width:49%" 
                                        type="submit" name="submit" 
                                        value="/edit/indi_com.php?com=editrmedia&i=<?php echo $return['individual']; ?>">Edit</button>
                                <button class="w3-bar-item w3-button w3-white w3-right" style="border: 1px solid black; width:49%" 
                                        type="submit" name="submit" 
                                        value="/edit/indi_com.php?com=removermedia&i=<?php echo $return['individual']; ?>">Remove</button>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-row w3-white w3-padding-small">
                <div class="w3-col" style="width: 100px">
                    <p>Parents:</p>
                </div>
                <div class="w3-rest">
                    <p><button class="w3-button" 
                               style="border: 1px solid black; width: 100%; overflow-y: hidden;"
                               type="submit" name="submit" 
                               value="/edit/indi_com.php?com=editParentsFamily&i=<?php echo $return['individual']; ?>">
                                   <?php
                                   $thisFamily = $indiData['child_in_family'];
                                   if ($thisFamily == null) {
                                       echo 'Click here to create parents family';
                                   } else {
                                       $famData = read_assoc($mysqli, "family", $thisFamily);
                                       echo familyDescription($mysqli, $famData);
                                   }
                                   ?>
                        </button></p>
                </div> 
            </div>
        </div>

        <?php
        $famS = get_spouses($mysqli, $return['individual'], $treeId, $indiData['lspouse'], $indiData['rspouse']);
        ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-row w3-white w3-padding-small">
                <div class="w3-col" style="width: 100px">
                    <P>Partners:</p>
                </div>
                
                <div class="w3-col w3-padding w3-right" style="width: 200px">
                    <div  style="overflow-x: auto;" >
                        <table style="width: 100%">
                            <tr>Key:</tr>
                            <tr>
                            <div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:50px; padding: 0px"><b>&#x1F880;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Draw to the left</div>
                            </div>
                            </tr>
                            <tr>
                            <div class="w3-row w3-border">
                                <div class="w3-col w3-container w3-right" style="width:50px; padding: 0px"><b>&#x1F882;</b></div>
                                <div class="w3-rest w3-container" style="padding: 0px">Draw to the right</div>
                            </div>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="w3-rest w3-border">
                    <div  style="overflow-x: auto;" >
                        <table class="w3-table" style="width: 100%">
                            <?php
                            foreach ($famS as $row) {
                                ?>
                                <tr>
                                <div class="w3-row w3-border">
                                    <div class="w3-col w3-container w3-right" style="width:50px; padding: 0px"> <!-- Right Arrow -->
                                        <button class="w3-button w3-block" type="submit" name="submit" 
                                                value="/edit/indi_com.php?com=makeRSpouse&i=<?php echo $return['individual']; ?>&s=<?php echo $row[3]; ?>" 
                                                <?php
                                                if ($row[2] == 'r') {
                                                    echo ' title="Already drawn right" disabled><span style="color: black;"';
                                                } else if ($row[2] == '') {
                                                    echo ' title="Other spouse isn\'t there" disabled><span style="color: lightgray;"';
                                                } else {
                                                    echo ' title="Draw to the right"><span style="color: blue;"';
                                                }
                                                ?>
                                                ><b>&#x1F882;</b></span></button> 
                                    </div>
                                    <div class="w3-col w3-container w3-right" style="width:50px; padding: 0px"> <!-- Left Arrow -->
                                        <button class="w3-button w3-block" type="submit" name="submit" 
                                                value="/edit/indi_com.php?com=makeLSpouse&i=<?php echo $return['individual']; ?>&s=<?php echo $row[3]; ?>" 
                                                <?php
                                                if ($row[2] == 'l') {
                                                    echo ' title="Already drawn left" disabled><span style="color: black;"';
                                                } else if ($row[2] == '') {
                                                    echo ' title="Other spouse isn\'t there" disabled><span style="color: lightgray;"';
                                                } else {
                                                    echo ' title="Draw to the left"><span style="color: blue;"';
                                                }
                                                ?>
                                                ><b>&#x1F880;</b></span></button> 
                                    </div>
                                    <div class="w3-rest w3-container" style="padding: 0px"> <!-- Main button to select family -->
                                        <button class="w3-button w3-block w3-left-align" type="submit" name="submit"  title="Edit"
                                                value="/edit/edit_family.php?f=<?php echo $row[1]; ?>"><?php echo $row[0]; ?></button>
                                    </div>
                                </div>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                            <div class="w3-row w3-border">
                                <button class="w3-button w3-block w3-left-align" type="submit" name="submit" 
                                        value="/edit/indi_com.php?com=addSpouseFamily&i=<?php echo $return['individual']; ?>"
                                        >New family...</button>
                            </div>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-light-grey">
                <p>Tip:</p>
                <p>You may select a spouse and family to be shown left or right of this individual using the left and right chevrons. But remember the spouse will only be shown if this person and the spouse are both shown at the top of a stack and there is no one shown below them.<br>
                    You can only select a spouse and family to be shown left or right of this person if the other spouse in that family exists. If that other spouse doesn't exist try adding them.</p>
            </div>
        </div>
        <?php
        $FALStrings = completeFALList($mysqli, "individual", $return['individual'], $treeId, $class);
        include $path . "includes/facts_and_links.php";
        ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <button class="w3-bar-item w3-button w3-white w3-left" style="width:49%" type="submit" name="submit" 
                        value="confirm">Submit Changes</button>
                <button class="w3-bar-item w3-button w3-white w3-right" style="width:49%" type="submit" name="submit" 
                        value="cancel">Cancel Changes</button>
            </div>
        </div>
    </form>
    <?php
}
include $path . 'includes/footer.php';
