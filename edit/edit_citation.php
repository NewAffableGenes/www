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

if ((strlen($errorTitle) == 0) && ($return['citation'] == null)) {
    $errorTitle = "Citation Selection Error";
    $errorMessage = "You have not selected a citation in this tree";
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
    $type = "citation";
    $data = read_assoc($mysqli, $type, $return[$type]);
    $ref = array();
    addDecriptionAndRedirectForObject($mysqli, $ref, $data, $class);
    // Now $ref[0] is the description and $ref[1] is the redirection string
    $sou = array();
    if ($data['source_id'] == null) {
        $sou = ["None - Click here to create",
            "create_source"];
    } else {
        $sourcedata = read_assoc($mysqli, "source", $data['source_id']);
        $sou = ["Source: " . sourceBriefDescriptor($sourcedata),
            "/edit/edit_source.php?sour=" . $data['source_id']];
    }

    $date = new CDateValue();
    if ($data['entry_recording_date_id'] == null) {
        $dateStr = "";
        $dateStrOK = false;
    } else {
        $date->ReadDatabase($mysqli, $data['entry_recording_date_id']);
        $dateStr = $date->GEDCOMStyle();
        $dateStrOK = !$date->isInterpreted();
    }

    $certainty_assessment = $data['certainty_assessment'];
    if ($certainty_assessment == null) {
        $certainty_assessment = 0;
    }

    $EventTypes = [
        "Not defined", "ADOP", "ANUL", "BAPM", "BARM", "BASM", "BIRT", "BLES", "BURI",
        "CAST", "CENS", "CHR", "CHRA", "CONF", "CREM", "DEAT", "DSCR", "DIV", "DIVF",
        "EDUC", "ENGA", "GRAD", "EMIG", "FCOM", "IDNO", "IMMI",
        "NATI", "NATU", "NCHI", "NMR", "MARR", "MARB", "MARC", "MARL", "MARS",
        "OCCU", "ORDN", "PROB", "PROP", "RELI", "RESI", "RETI", "SSN", "TITL", "WILL"];

    $currentEventType = $data['event_type'];
    $currentEventIndex = 0;
    $EventList = [];
    foreach ($EventTypes as $key => $value) {
        array_push($EventList, ExpandType($value));
        if ($currentEventType == $value) {
            $currentEventIndex = $key;
        }
    };
    ?>
    <form 
        <?php echo "action=/edit/edited_$type.php?" . $class["citation"]["rtn"] . "=" . $return[$type] . " method='POST'" ?> >
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h1>Citation</h1>
            </div>
        </div>

        <?php include $path . 'includes/referenced_from_1.php'; ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white">

                <div class="w3-row">
                    <div class="w3-quarter">
                        <p>Source:</p>
                    </div>
                    <div class="w3-threequarter w3-container">
                        <p><button class="w3-button w3-block" 
                                   style="border: 1px solid black; width: 100%; overflow-y: hidden;"
                                   type="submit" name="submit" 
                                   value="<?php echo $sou[1]; ?>">
                                       <?php echo $sou[0]; ?>
                            </button></p>
                    </div>
                </div>

                <div class="w3-row">
                    <div class="w3-quarter">
                        <p>Where within source:</p>
                    </div>
                    <div class="w3-threequarter w3-container">
                        <p><input type="text" id="where_within_source" name="where_within_source"
                                  maxlength="248" placeholder="Where within source ..."
                                  style="border: 1px solid black; width: 100%"
                                  value="<?php echo $data['where_within_source']; ?>" /></p>
                    </div>
                </div>

                <div class="w3-row">
                    <div class="w3-quarter"><p>Event type:</p></div>
                    <div class="w3-quarter w3-container">
                        <p><select name="event_type" style="width: 100%">
                                <?php
                                foreach ($EventList as $key => $EvType) {
                                    if ($key == $currentEventIndex) {
                                        echo "<option value='$key' selected='selected'>$EvType</option>";
                                    } else {
                                        echo "<option value='$key'>$EvType</option>";
                                    }
                                };
                                ?>
                            </select></p>
                    </div>
                    <div class="w3-quarter"><p>Role in event:</p></div>
                    <div class="w3-quarter w3-container">
                        <p><input type="text" id="where_within_source" name="role_in_event" style="width: 100%"
                                  maxlength="15" placeholder="Role ..."
                                  style="border: 1px solid black;"
                                  value= "<?php echo $data['role_in_event']; ?>" /></p>
                    </div>
                </div>

                <div class="w3-row">
                    <div class="w3-quarter">
                        <p>Entry recording date: <button type="button" class="w3-button-tiny w3-circle w3-teal w3-border"
                            style="border:none;outline:0;" onclick="myPopupFunction()">?</button></p>
                    </div>
                    <div class="w3-threequarter w3-container">
                        <p><div class="input-group">
                            <input type="text" id="date" name="date"
                                maxlength="30" placeholder="Date ..."
                                style="border: 1px solid black; width: 100%"
                                value="<?php echo $dateStr; ?>"/>
                            </div></p>
                        </div>
                </div>
                
<div id="myPopup" style="display:none">
    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-display-container w3-light-grey">
            <div class="w3-padding-none w3-display-topright"><button type="button" class="w3-button w3-border" onclick="myPopupFunction()">X</button></div>
            <h4><u>Date formats</u></h4>
            <p>Dates may be entered as free text or more normally in one of the standard formats below:</p>
            <ul>
                <li> dd mmm yyyy (e.g. 10 Aug 1965)</li>
                <li> Abt dd mmm yyyy (Abt is short for About)</li>
                <li> Aft dd mmm yyyy (Aft is short for After)</li>
                <li> Bef dd mmm yyyy (Bef is short for Before)</li>
                <li> Bet dd mmm yyyy and dd mmm yyyy (Bet is short for Between)</li>
                <li> Cal dd mmm yyyy (Cal is short for Calculated)</li>
                <li> Est dd mmm yyyy (Est is short for Estimated)</li>
                <li> From dd mmm yyyy to dd mmm yyyy</li>
                <li> From dd mmm yyyy</li>
                <li> To dd mmm yyyy</li>
            </ul>
            <p>The input is not case sensitive</p>
        </div>
    </div>
</div>

            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-padding-small">
                <div class="w3-row w3-white">
                    <div class="w3-col" style="width: 100px;">
                        <p> Text from source:</p>
                    </div>
                    <div class="w3-rest">
                        <textarea ROWS=3 
                                  id="text_from_source" name="text_from_source" 
                                  style="width: 100%; resize: none; height: 100px; overflow-y: scroll; overflow-x: auto; text-align: left; white-space: normal;"
                                  ><?php echo $data['text_from_source']; ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-padding-small">
                <div class="w3-col" style="width: 100px""><p>Certainty assessment:</p></div>
                <div class="w3-rest">
                    <p><label class="radio-inline">
                            <input type="radio" name="radioCA" value="0" 
                            <?php
                            if ($certainty_assessment == 0) {
                                echo 'checked="checked"';
                            }
                            ?>> 0 = Unreliable evidence or estimated data
                        </label><br>
                        <label class="radio-inline">
                            <input type="radio" name="radioCA" value="1" 
                            <?php
                            if ($certainty_assessment == 1) {
                                echo 'checked="checked"';
                            }
                            ?>> 1 = Questionable reliability of evidence
                        </label><br>
                        <label class="radio-inline">
                            <input type="radio" name="radioCA" value="2" 
                            <?php
                            if ($certainty_assessment == 2) {
                                echo 'checked="checked"';
                            }
                            ?>> 2 = Secondary evidence, data officially recorded sometime after event
                        </label><br>
                        <label class="radio-inline">
                            <input type="radio" name="radioCA" value="3" 
                            <?php
                            if ($certainty_assessment == 3) {
                                echo 'checked="checked"';
                            }
                            ?>> 3 = Direct and primary evidence used, or by dominance of the evidenced
                        </label></p>
                </div>
            </div>
        </div>

        <?php
        $FALStrings = completeFALList($mysqli, $type, $return[$type], $treeId, $class);
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
    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-light-grey">
            <p>Tip: Dates may be entered as free text or more normally in one of the standard formats below:</p>
            <ul>
                <li> dd mmm yyyy (e.g. 10 Aug 1965)</li>
                <li> Abt dd mmm yyyy (Abt is short for About)</li>
                <li> Aft dd mmm yyyy (Aft is short for After)</li>
                <li> Bef dd mmm yyyy (Bef is short for Before)</li>
                <li> Bet dd mmm yyyy and dd mmm yyyy (Bet is short for Between)</li>
                <li> Cal dd mmm yyyy (Cal is short for Calculated)</li>
                <li> Est dd mmm yyyy (Est is short for Estimated)</li>
                <li> From dd mmm yyyy to dd mmm yyyy</li>
                <li> From dd mmm yyyy</li>
                <li> To dd mmm yyyy</li>
            </ul>
            <p>To see if the format is recognised click the 'Check Date Format' button. If it is recognised the box to the right of the date entry box will show a tick otherwise it will show a cross</p>
            <p>The input is not case sensitive</p>
        </div>
    </div>

    <?php
}
include $path . 'includes/footer.php';
