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

$need = "family";
if ((strlen($errorTitle) == 0) && ($return[$need] == null)) {
    $errorTitle = "Selection Error: $need";
    $errorMessage = "You have not selected a $need from this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) == 0) {
    switch ($return['command']) {
        case "addChild" :
            $object = createDefaultIndividual($mysqli, $treeId, nextLabel($mysqli, $treeId, "individual"));
            $data = read_assoc($mysqli, "individual", $object);
            $data['child_in_family'] = $return['family'];
            update_assoc($mysqli, "individual", $object, $data);
            $redirect = '/edit/edit_individual.php?i=' . $object;
            break;
        case "swap_parents" :
            $thisFamily = read_assoc($mysqli, "family", $return['family']);
            $wife = $thisFamily['wife'];
            $husband = $thisFamily['husband'];
            $thisFamily['wife'] = $husband;
            $thisFamily['husband'] = $wife;
            update_assoc($mysqli, "family", $return['family'], $thisFamily);
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "createMother" :
            $thisFamily = read_assoc($mysqli, "family", $return['family']);
            $object = createDefaultIndividual($mysqli, $treeId, nextLabel($mysqli, $treeId, "individual"));
            $data = read_assoc($mysqli, "individual", $object);
            $date['sex'] = 'f';
            update_assoc($mysqli, "individual", $object, $data);
            $thisFamily['wife'] = $object;
            update_assoc($mysqli, "family", $return['family'], $thisFamily);
            $redirect = '/edit/edit_individual.php?i=' . $object;
            break;
        case "createFather" :
            $thisFamily = read_assoc($mysqli, "family", $return['family']);
            $object = createDefaultIndividual($mysqli, $treeId, nextLabel($mysqli, $treeId, "individual"));
            $data = read_assoc($mysqli, "individual", $object);
            $date['sex'] = 'm';
            update_assoc($mysqli, "individual", $object, $data);
            $thisFamily['husband'] = $object;
            update_assoc($mysqli, "family", $return['family'], $thisFamily);
            $redirect = '/edit/edit_individual.php?i=' . $object;
            break;
        case "unlinkFather" :
            $thisFamily = read_assoc($mysqli, "family", $return['family']);
            $father = $thisFamily['husband'];
            $mother = $thisFamily['wife'];
            if ($father != null) {
                $father = read_assoc($mysqli, "individual", $father);
                if ($mother != null) {
                    $mother = read_assoc($mysqli, "individual", $mother);
                    if ($father['lspouse'] == $mother['id']) {
                        $father['lspouse'] = null;
                        $mother['rspouse'] = null;
                        update_assoc($mysqli, "individual", $father['id'], $father);
                        update_assoc($mysqli, "individual", $mother['id'], $mother);
                    }
                    if ($father['rspouse'] == $mother['id']) {
                        $father['rspouse'] = null;
                        $mother['lspouse'] = null;
                        update_assoc($mysqli, "individual", $father['id'], $father);
                        update_assoc($mysqli, "individual", $mother['id'], $mother);
                    }
                }
                $thisFamily['husband'] = null;
                update_assoc($mysqli, "family", $thisFamily['id'], $thisFamily);
            }
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "unlinkMother" :
            $thisFamily = read_assoc($mysqli, "family", $return['family']);
            $father = $thisFamily['husband'];
            $mother = $thisFamily['wife'];
            if ($mother != null) {
                $mother = read_assoc($mysqli, "individual", $mother);
                if ($father != null) {
                    $father = read_assoc($mysqli, "individual", $father);
                    if ($mother['lspouse'] == $father['id']) {
                        $mother['lspouse'] = null;
                        $father['rspouse'] = null;
                        update_assoc($mysqli, "individual", $father['id'], $father);
                        update_assoc($mysqli, "individual", $mother['id'], $mother);
                    }
                    if ($mother['rspouse'] == $father['id']) {
                        $mother['rspouse'] = null;
                        $father['lspouse'] = null;
                        update_assoc($mysqli, "individual", $father['id'], $father);
                        update_assoc($mysqli, "individual", $mother['id'], $mother);
                    }
                }
                $thisFamily['wife'] = null;
                update_assoc($mysqli, "family", $thisFamily['id'], $thisFamily);
            }
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "moveUpInFamily" :
            $children = get_children($mysqli, $return['family'], $treeId);
            if ($return['counter'] != null) {
                if (($return['counter'] > 0) && ($return['counter'] < sizeof($children))) {
                    $c1 = $children[$return['counter']];
                    $c2 = $children[$return['counter'] - 1];
                    $p1 = $c1['place_in_family_sibling_list'];
                    $p2 = $c2['place_in_family_sibling_list'];
                    $c1['place_in_family_sibling_list'] = $p2;
                    $c2['place_in_family_sibling_list'] = $p1;
                    update_assoc($mysqli, "individual", $c1['id'], $c1);
                    update_assoc($mysqli, "individual", $c2['id'], $c2);
                }
            }
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "moveDnInFamily" :
            $children = get_children($mysqli, $return['family'], $treeId);
            if ($return['counter'] != null) {
                if (($return['counter'] >= 0) && ($return['counter'] < sizeof($children) - 1)) {
                    $c1 = $children[$return['counter']];
                    $c2 = $children[$return['counter'] + 1];
                    $p1 = $c1['place_in_family_sibling_list'];
                    $p2 = $c2['place_in_family_sibling_list'];
                    $c1['place_in_family_sibling_list'] = $p2;
                    $c2['place_in_family_sibling_list'] = $p1;
                    update_assoc($mysqli, "individual", $c1['id'], $c1);
                    update_assoc($mysqli, "individual", $c2['id'], $c2);
                }
            }
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "setTopOfStack" :
            $thisIndi = $return['individual'];
            $thisFam = $return['family'];
            $data = read_assoc($mysqli, "individual", $thisIndi);
            $data['show_me'] = 't';
            update_assoc($mysqli, "individual", $thisIndi, $data);
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "setNoShow" :
            $thisIndi = $return['individual'];
            $thisFam = $return['family'];
            $data = read_assoc($mysqli, "individual", $thisIndi);
            $data['show_me'] = 'n';
            update_assoc($mysqli, "individual", $thisIndi, $data);
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "setBelowInStack" :
            $thisIndi = $return['individual'];
            $thisFam = $return['family'];
            $data = read_assoc($mysqli, "individual", $thisIndi);
            $data['show_me'] = 'b';
            update_assoc($mysqli, "individual", $thisIndi, $data);
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
            break;
        case "unlinkFromFamily" :
            $thisIndi = $return['individual'];
            $thisFam = $return['family'];
            $data = read_assoc($mysqli, "individual", $thisIndi);
            $data['child_in_family'] = null;
            update_assoc($mysqli, "individual", $thisIndi, $data);
            $redirect = '/edit/edit_family.php?f=' . $return['family'];
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
