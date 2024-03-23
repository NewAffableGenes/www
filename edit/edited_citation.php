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

$type = "citation";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $EventTypes = [
        null, "ADOP", "ANUL", "BAPM", "BARM", "BASM", "BIRT", "BLES", "BURI",
        "CAST", "CENS", "CHR", "CHRA", "CONF", "CREM", "DEAT", "DSCR", "DIV", "DIVF",
        "EDUC", "ENGA", "GRAD", "EMIG", "FCOM", "IDNO", "IMMI",
        "NATI", "NATU", "NCHI", "NMR", "MARR", "MARB", "MARC", "MARL", "MARS",
        "OCCU", "ORDN", "PROB", "PROP", "RELI", "RESI", "RETI", "SSN", "TITL", "WILL"];
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/tree.php";
    if ($submit == 'cancel') {
        $redirect = "/edit/edit_$type.php?" . $class[$type]["rtn"] . "=" . $return[$type];
    } else {
        if ($writeAllowed) {
            $data = read_assoc($mysqli, $type, $return[$type]);
        }
        if ($submit == 'confirm') {
            $redirect = "/edit/edit_$type.php?" . $class[$type]["rtn"] . "=" . $return[$type];
        } else if ($submit == 'create_source') {
            if ($writeAllowed) {
                $newSource = createDefaultSource($mysqli, $treeId, nextLabel($mysqli, $treeId, $type));
                $data['source_id'] = $newSource;
                $redirect = "/edit/edit_source.php?" . $class["source"]["rtn"] . "=" . $newSource;
            } else {
                $redirect = "/edit/edit_$type.php?" . $class[$type]["rtn"] . "=" . $return[$type];
            }
        } else if ($submit == 'check_date') {
            $redirect = "/edit/edit_$type.php?" . $class[$type]["rtn"] . "=" . $return[$type];
        } else {
            $redirect = $submit;
        }
        if ($writeAllowed) {
            $data['text_from_source'] = notNullInputPost('text_from_source');
            $data['where_within_source'] = notNullInputPost('where_within_source');
            $data['role_in_event'] = notNullInputPost('role_in_event');
            $data['event_type'] = $EventTypes[intval(notNullInputPost('event_type'))];
            $data['certainty_assessment'] = intval(notNullInputPost('radioCA'));
            $dateStr = notNullInputPost('date');
            $date = new CDateValue();
            if ($data['entry_recording_date_id'] == null) {
                if (strlen($dateStr) > 0) {
                    $data['entry_recording_date_id'] = createDefaultComplexDate($mysqli, $treeId);
                    $date->Interpret($dateStr);
                    $date->UpdateDatabase($mysqli, $data['entry_recording_date_id']);
                }
            } else {
                $date->Interpret($dateStr);
                $date->UpdateDatabase($mysqli, $data['entry_recording_date_id']);
            }
            update_assoc($mysqli, $type, $return[$type], $data);
        }
    }
    include $path . 'includes/redirect.php';
}

