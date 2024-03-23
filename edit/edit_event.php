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

$type = "event";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
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

    $data = read_assoc($mysqli, $type, $return[$type]);
    $ref = array();
    addDecriptionAndRedirectForObject($mysqli, $ref, $data, $class);
    // Now $ref[0] is the description and $ref[1] is the redirection string

    $date = new CDateValue();
    if ($data['event_date_id'] == null) {
        $dateStr = "";
        $dateStrOK = false;
    } else {
        $date->ReadDatabase($mysqli, $data['event_date_id']);
        $dateStr = $date->GEDCOMStyle();
        $dateStrOK = !$date->isInterpreted();
    }

    if ($data['belongs_to_class'] == 'individual') {
        $EventTypes = ["Not defined", "BIRT", "BAPM", "CHR", "CONF",
            "DEAT", "BURI", "CREM", "PROB", "WILL",
            "RESI", "EDUC", "EMIG", "IMMI", "CENS",
            "ADOP", "BARM", "BASM", "BLES",
            "CHRA", "FCOM", "ORDN", "NATU",
            "GRAD", "RETI", "EVEN", "CAST",
            "DSCR", "IDNO", "NATI", "NCHI", "NMR",
            "OCCU", "PROP", "RELI", "SSN", "TITL"];
    } else { // family
        $EventTypes = ["Not defined", "ENGA", "MARR", "MARB", "MARC",
            "MARL", "MARS", "ANUL", "CENS", "DIV", "DIVF", "EVEN"];
    }

    $currentEventType = $data['type'];
    $currentEventIndex = 0;
    $EventList = [];
    foreach ($EventTypes as $key => $value) {
        array_push($EventList, ExpandType($value));
        if ($currentEventType == $value) {
            $currentEventIndex = $key;
        }
    }

    $placeData = read_assoc($mysqli, "place", $data['place_id']);
    $addressData = read_assoc($mysqli, "address", $data['address_id']);
    ?>

    <form 
        <?php echo "action=/edit/edited_$type.php?" . $class[$type]["rtn"] . "=" . $return[$type] . " method='POST'" ?> >
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h1>Event</h1>
            </div>
        </div>

        <?php include $path . 'includes/referenced_from_1.php'; ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white">

                <div class="w3-row">
                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Event type:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><select style="border: 1px solid black; width: 100%" name="type">
                                        <?php
                                        foreach ($EventList as $key => $EvType) {
                                            if ($key == $currentEventIndex) {
                                                echo "<option value='$key' selected='selected'>$EvType</option>";
                                            } else {
                                                echo "<option value='$key'>$EvType</option>";
                                            }
                                        }
                                        ?>
                                    </select></p>
                            </div>
                        </div>
                    </div>

                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Date: <button type="button" class="w3-button-tiny w3-circle w3-teal w3-border"
                                style="border:none;outline:0;" onclick="myPopupFunction()">?</button></p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><div class="input-group">
                                    <input type="text" 
                                           id="date" name="date" title="Click ? for help"
                                           maxlength="30" placeholder="Date ..."
                                           style="border: 1px solid black; width: 100%;text-align:center"
                                           value="<?php echo $dateStr; ?>"/>
                                </div></p>
                            </div>
                        </div>
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

                <div class="w3-row">
                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Descriptor:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><input type="text" id="event_descriptor" name="event_descriptor"
                                          maxlength="90" placeholder="Descriptor ..."
                                          style="border: 1px solid black; width: 100%"
                                          value= "<?php echo $data['event_descriptor']; ?>" /></p>
                            </div>
                        </div>
                    </div>
                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Cause:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><input type="text" id="cause_of_event" name="cause_of_event"
                                          maxlength="90" placeholder="Cause ..."
                                          style="border: 1px solid black; width: 100%"
                                          value= "<?php echo $data['cause_of_event']; ?>" /></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w3-row">
                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Age at event:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><input type="text" id="age_at_event" name="age_at_event"
                                          maxlength="12" placeholder="Age ..."
                                          style="border: 1px solid black; width: 100%"
                                          value= "<?php echo $data['age_at_event']; ?>" /></p>
                            </div>
                        </div>
                    </div>
                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Agency:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><input type="text" id="responsible_agency" name="responsible_agency"
                                          maxlength="120" placeholder="Agency ..."
                                          style="border: 1px solid black; width: 100%"
                                          value= "<?php echo $data['responsible_agency']; ?>" /></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                if ($data['belongs_to_class'] == 'family') {
                    ?>

                    <div class="w3-row">
                        <div class="w3-half">
                            <div class="w3-row">
                                <div class="w3-quarter">
                                    <p>Husband's age:</p>
                                </div>
                                <div class="w3-threequarter w3-container">
                                    <p><input type="text" id="husband_age" name="husband_age"
                                              maxlength="12" placeholder="Age ..."
                                              style="border: 1px solid black; width: 100%"
                                              value= "<?php echo $data['husband_age']; ?>" /></p>
                                </div>
                            </div>
                        </div>
                        <div class="w3-half">
                            <div class="w3-row">
                                <div class="w3-quarter">
                                    <p>Wife's age:</p>
                                </div>
                                <div class="w3-threequarter w3-container">
                                    <p><input type="text" id="wife_age" name="wife_age"
                                              maxlength="12" placeholder="Age ..."
                                              style="border: 1px solid black; width: 100%"
                                              value= "<?php echo $data['wife_age']; ?>" /></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <div class="w3-row">
                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Place:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><input type="text" id="place_value" name="place_value"
                                          maxlength="120" placeholder="Place value" style="border: 1px solid black; width: 100%"
                                          value= "<?php echo $placeData['place_value']; ?>" /><br>
                                    <input type="text" class="form-control" id="place_hierarchy" name="place_hierarchy"
                                           maxlength="120" placeholder="Place hierarchy" style="border: 1px solid black; border-top: 0px; width: 100%"
                                           value= "<?php echo $placeData['place_hierarchy']; ?>" /></p>
                            </div>
                        </div>
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Data:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><input type="text" id="argument" name="argument"
                                          maxlength="90" placeholder="Data ..."
                                          style="border: 1px solid black; width: 100%"
                                          value= "<?php echo $data['argument']; ?>" /></p>
                            </div>
                        </div>

                    </div>
                    <div class="w3-half">
                        <div class="w3-row">
                            <div class="w3-quarter">
                                <p>Address:</p>
                            </div>
                            <div class="w3-threequarter w3-container">
                                <p><input type="text" id="line1" name="line1"
                                          maxlength="60" placeholder="Line 1" style="border: 1px solid black; width: 100%"
                                          value= "<?php echo $addressData['line1']; ?>" /><br>
                                    <input type="text" id="line2" name="line2"
                                           maxlength="60" placeholder="Line 2" style="border: 1px solid black; border-top: 0px;width: 100%"
                                           value= "<?php echo $addressData['line2']; ?>" /><br>
                                    <input type="text" id="line3" name="line3"
                                           maxlength="60" placeholder="Line 3" style="border: 1px solid black; border-top: 0px; width: 100%"
                                           value= "<?php echo $addressData['line3']; ?>" /><br>
                                    <input type="text" id="city" name="city"
                                           maxlength="60" placeholder="City" style="border: 1px solid black; border-top: 0px; width: 100%"
                                           value= "<?php echo $addressData['city']; ?>" /><br>
                                    <input type="text" id="state" name="state"
                                           maxlength="60" placeholder="State" style="border: 1px solid black; border-top: 0px; width: 100%"
                                           value= "<?php echo $addressData['state']; ?>" /><br>
                                    <input type="text" id="postal_code" name="postal_code"
                                           maxlength="10" placeholder="Postal code" style="border: 1px solid black; border-top: 0px; width: 100%"
                                           value= "<?php echo $addressData['postal_code']; ?>" /><br>
                                    <input type="text" id="country" name="country"
                                           maxlength="60" placeholder="Country" style="border: 1px solid black; border-top: 0px; width: 100%"
                                           value= "<?php echo $addressData['country']; ?>" /><br>
                                    <input type="text" id="phone" name="phone"
                                           maxlength="25" placeholder="Phone" style="border: 1px solid black; border-top: 0px; width: 100%"
                                           value= "<?php echo $addressData['phone']; ?>" /></p>
                            </div>
                        </div>
                    </div>
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
    <?php
}
include $path . 'includes/footer.php';
