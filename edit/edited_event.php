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

$type = "event";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . 'includes/footer.php';
} else {
    $data = read_assoc($mysqli, $type, $return[$type]);
    
    if ($data['belongs_to_class'] == 'individual') {
        $EventTypes = [null, "BIRT", "BAPM", "CHR", "CONF",
            "DEAT", "BURI", "CREM", "PROB", "WILL",
            "RESI", "EDUC", "EMIG", "IMMI", "CENS",
            "ADOP", "BARM", "BASM", "BLES",
            "CHRA", "FCOM", "ORDN", "NATU",
            "GRAD", "RETI", "EVEN", "CAST",
            "DSCR", "IDNO", "NATI", "NCHI", "NMR",
            "OCCU", "PROP", "RELI", "SSN", "TITL"];
    } else { // family
        $EventTypes = [null, "ENGA", "MARR", "MARB", "MARC",
            "MARL", "MARS", "ANUL", "CENS", "DIV", "DIVF", "EVEN"];
    }

    $submit = filter_input(INPUT_POST, 'submit');
    $refType = $data['belongs_to_class'];
    
    if ($submit == 'cancel') {
        $redirect = "/edit/edit_" . $refType . ".php?" . $class[$refType]["rtn"] . "=" . $data['belongs_to_id'];
    } else {
        if ($submit == 'confirm') {
            $redirect = "/edit/edit_" . $refType . ".php?" . $class[$refType]["rtn"] . "=" . $data['belongs_to_id'];
        } else if ($submit == 'check_date') {
            $redirect = "/edit/edit_$type.php?" . $class[$type]["rtn"] . "=" . $return[$type];
        } else {
            $redirect = $submit;
        }
        if ($writeAllowed) {
            $data['type'] = $EventTypes[intval(notNullInputPost('type'))];
            $data['argument'] = notNullInputPost('argument');
            $data['event_descriptor'] = notNullInputPost('event_descriptor');
            $data['cause_of_event'] = notNullInputPost('cause_of_event');
            $data['age_at_event'] = notNullInputPost('age_at_event');
            $data['responsible_agency'] = notNullInputPost('responsible_agency');
            if ($data['belongs_to_class'] == 'family') {
                $data['husband_age'] = notNullInputPost('husband_age');
                $data['wife_age'] = notNullInputPost('wife_age');
            }
            $dateStr = notNullInputPost('date');
            $date = new CDateValue();
            if ($data['event_date_id'] == null) {
                if (strlen($dateStr) > 0) {
                    $data['event_date_id'] = createDefaultComplexDate($mysqli, $treeId);
                    $date->Interpret($dateStr);
                    $date->UpdateDatabase($mysqli, $data['event_date_id']);
                }
            } else {
                $date->Interpret($dateStr);
                $date->UpdateDatabase($mysqli, $data['event_date_id']);
            }
            update_assoc($mysqli, $type, $return[$type], $data);
            $placeData = read_assoc($mysqli, "place", $data['place_id']);
            $placeData['place_value'] = notNullInputPost('place_value');
            $placeData['place_hierarchy'] = notNullInputPost('place_hierarchy');
            update_assoc($mysqli, "place", $data['place_id'], $placeData);
            $addressData = read_assoc($mysqli, "address", $data['address_id']);
            $addressData['line1'] = notNullInputPost('line1');
            $addressData['line2'] = notNullInputPost('line2');
            $addressData['line3'] = notNullInputPost('line3');
            $addressData['city'] = notNullInputPost('city');
            $addressData['state'] = notNullInputPost('state');
            $addressData['postal_code'] = notNullInputPost('postal_code');
            $addressData['country'] = notNullInputPost('country');
            $addressData['phone'] = notNullInputPost('phone');
            update_assoc($mysqli, "address", $data['address_id'], $addressData);
        }
    }
    include $path . 'includes/redirect.php';
}

