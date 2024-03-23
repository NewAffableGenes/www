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
    $errorTitle = "Tree Access Error";
    $errorMessage = "You do not have write access to this tree and cannot create any new entries";
    $errorRedirect = "/tree/tree.php";
}

$need = "individual";
if ((strlen($errorTitle) == 0) && ($return[$need] == null)) {
    $errorTitle = "Selection Error: $need";
    $errorMessage = "You have not selected an $need from this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) == 0) {
    switch ($return['command']) {
        case "removelmedia" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $indiData['l_media_id'] = null;
            update_assoc($mysqli, "individual", $return['individual'], $indiData);
            $redirect = '/edit/edit_individual.php?i=' . $return['individual'];
            break;
        case "removermedia" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $indiData['r_media_id'] = null;
            update_assoc($mysqli, "individual", $return['individual'], $indiData);
            $redirect = '/edit/edit_individual.php?i=' . $return['individual'];
            break;
        case "addlmedia" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $media = createDefaultMedia($mysqli, $treeId, nextLabel($mysqli, $treeId, 'media'));
            $medialink = createDefaultMediaLink($mysqli, $treeId, "individual", $return['individual'], $media);
            $indiData['l_media_id'] = $medialink;
            update_assoc($mysqli, "individual", $return['individual'], $indiData);
            $redirect = '/edit/edit_media.php?m=' . $media;
            break;
        case "addrmedia" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $media = createDefaultMedia($mysqli, $treeId, nextLabel($mysqli, $treeId, 'media'));
            $medialink = createDefaultMediaLink($mysqli, $treeId, "individual", $return['individual'], $media);
            $indiData['r_media_id'] = $medialink;
            update_assoc($mysqli, "individual", $return['individual'], $indiData);
            $redirect = '/edit/edit_media.php?m=' . $media;
            break;
        case "editlmedia" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $media = $indiData['l_media_id'];
            $redirect = '/edit/edit_media.php?m=' . $media;
            break;
        case "editrmedia" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $media = $indiData['r_media_id'];
            $redirect = '/edit/edit_media.php?m=' . $media;
            break;
        case "addSpouseFamily" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $fam = createDefaultFamily($mysqli, $treeId, nextLabel($mysqli, $treeId, "family"));
            $famData = read_assoc($mysqli, "family", $fam);
            if ($indiData['sex'] == 'm') {
                $famData['husband'] = $return['individual'];
            } else {
                $famData['wife'] = $return['individual'];
            }
            update_assoc($mysqli, "family", $fam, $famData);
            $redirect = '/edit/edit_family.php?f=' . $fam;
            break;
        case "autoBoxText" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $indiData['box_text'] = autoBoxText($mysqli, $treeId, $indiData);
            update_assoc($mysqli, "individual", $return['individual'], $indiData);
            $redirect = '/edit/edit_individual.php?i=' . $return['individual'];
            break;
        case "editParentsFamily" :
            $indiData = read_assoc($mysqli, "individual", $return['individual']);
            $fam = $indiData['child_in_family'];
            if ($fam == null) {
                $fam = createDefaultFamily($mysqli, $treeId, nextLabel($mysqli, $treeId, "family"));
                $indiData['child_in_family'] = $fam;
                update_assoc($mysqli, "individual", $return['individual'], $indiData);
            }
            $redirect = '/edit/edit_family.php?f=' . $fam;
            break;
        case "makeLSpouse" :
            if ($return['spouse'] != null) {
                $thisIndi = read_assoc($mysqli, "individual", $return['individual']);
                // Check whether thisIndi has a spouse on the left already
                // If they have break the link from both sides
                if ($thisIndi['lspouse'] != null) {
                    update_assoc($mysqli, 'individual', $thisIndi['lspouse'], ['rspouse' => null]);
                    update_assoc($mysqli, 'individual', $return['individual'], ['lspouse' => null]);
                    $thisIndi['lspouse'] = null;
                }
                // Check whether the spouse to be made LSpouse is already on the right
                // If they have break the link from both sides
                if ($thisIndi['rspouse'] == $return['spouse']) {
                    update_assoc($mysqli, 'individual', $thisIndi['rspouse'], ['lspouse' => null]);
                    update_assoc($mysqli, 'individual', $return['individual'], ['rspouse' => null]);
                    $thisIndi['rspouse'] = null;
                }
                // Now read the data for the spouse who will be drawn to the left
                $thisSpouse = read_assoc($mysqli, "individual", $return['spouse']);
                // Check whether thisSpouse has a spouse on the right already
                // If they have break the link from both sides
                if ($thisSpouse['rspouse'] != null) {
                    update_assoc($mysqli, 'individual', $thisSpouse['rspouse'], ['lspouse' => null]);
                    update_assoc($mysqli, 'individual', $return['spouse'], ['rspouse' => null]);
                    $thisSpouse['rspouse'] = null;
                }
                // So we have thisIndi and thisSpouse and they have space to do the deed
                update_assoc($mysqli, 'individual', $return['spouse'], ['rspouse' => $return['individual']]);
                update_assoc($mysqli, 'individual', $return['individual'], ['lspouse' => $return['spouse']]);
            }
            $redirect = '/edit/edit_individual.php?i=' . $return['individual'];
            break;
        case "makeRSpouse" :
            if ($return['spouse'] != null) {
                $thisIndi = read_assoc($mysqli, "individual", $return['individual']);
                // Check whether thisIndi has a spouse on the right already
                // If they have break the link from both sides
                if ($thisIndi['rspouse'] != null) {
                    update_assoc($mysqli, 'individual', $thisIndi['rspouse'], ['lspouse' => null]);
                    update_assoc($mysqli, 'individual', $return['individual'], ['rspouse' => null]);
                    $thisIndi['rspouse'] = null;
                }
                // Check whether the spouse to be made RSpouse is already on the left
                // If they have break the link from both sides
                if ($thisIndi['lspouse'] == $return['spouse']) {
                    update_assoc($mysqli, 'individual', $thisIndi['lspouse'], ['rspouse' => null]);
                    update_assoc($mysqli, 'individual', $return['individual'], ['lspouse' => null]);
                    $thisIndi['lspouse'] = null;
                }
                // Now read the data for the spouse who will be drawn to the right
                $thisSpouse = read_assoc($mysqli, "individual", $return['spouse']);
                // Check whether thisSpouse has a spouse on the left already
                // If they have break the link from both sides
                if ($thisSpouse['lspouse'] != null) {
                    update_assoc($mysqli, 'individual', $thisSpouse['lspouse'], ['rspouse' => null]);
                    update_assoc($mysqli, 'individual', $return['spouse'], ['lspouse' => null]);
                    $thisSpouse['lspouse'] = null;
                }
                // So we have thisIndi and thisSpouse and they have space to do the deed
                update_assoc($mysqli, 'individual', $return['spouse'], ['lspouse' => $return['individual']]);
                update_assoc($mysqli, 'individual', $return['individual'], ['rspouse' => $return['spouse']]);
            }
            $redirect = '/edit/edit_individual.php?i=' . $return['individual'];
            break;
        default:
            $errorTitle = "Invalid Command: $need";
            $errorMessage = "Are you trying to break this?";
            $errorRedirect = '/edit/edit_individual.php?i=' . $return['individual'];
            break;
    }
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    include $path . 'includes/redirect.php';
}
