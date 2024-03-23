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
    include $path . 'includes/footer.php';
} else {
    $submit = filter_input(INPUT_POST, 'submit');
    $redirect = "/tree/tree.php";
    if ($submit == 'cancel') {
        $redirect = "/tree/tree.php";
    } else {
        if ($submit == 'confirm') {
            $redirect = "/tree/tree.php";
        } else {
            $redirect = $submit;
        }
        $data = read_assoc($mysqli, 'tree', $treeId);
        $data['watermark_media_id'] = filter_input(INPUT_POST, 'background', FILTER_VALIDATE_INT);
        $data['root'] = filter_input(INPUT_POST, 'rootIndi', FILTER_VALIDATE_INT);
        $data['title'] = notNullInputPost('titletext');
        $aspects = aspect_ratios();
        $data['aspect_ratio'] = $aspects[filter_input(INPUT_POST, 'aspectRatio', FILTER_VALIDATE_INT)][0];
        $data['line_thickness'] = filter_input(INPUT_POST, 'lineThickness', FILTER_VALIDATE_INT);
        $data['connecting_R'] = filter_input(INPUT_POST, 'con_r', FILTER_VALIDATE_INT);
        $data['connecting_G'] = filter_input(INPUT_POST, 'con_g', FILTER_VALIDATE_INT);
        $data['connecting_B'] = filter_input(INPUT_POST, 'con_b', FILTER_VALIDATE_INT);
        $data['outline_thickness'] = filter_input(INPUT_POST, 'outlineThickness', FILTER_VALIDATE_INT);
        if ($data['outline_thickness'] == 0) {
            $data['outline_thickness'] = 1;
            $data['box_outline'] = false;
        } else {
            $data['box_outline'] = true;
        }
        $data['outline_R'] = filter_input(INPUT_POST, 'out_r', FILTER_VALIDATE_INT);
        $data['outline_G'] = filter_input(INPUT_POST, 'out_g', FILTER_VALIDATE_INT);
        $data['outline_B'] = filter_input(INPUT_POST, 'out_b', FILTER_VALIDATE_INT);
        $data['min_indi_H'] = filter_input(INPUT_POST, 'minBoxH', FILTER_VALIDATE_INT);
        $data['min_indi_W'] = filter_input(INPUT_POST, 'minBoxW', FILTER_VALIDATE_INT);
        $data['thumbnail_H'] = filter_input(INPUT_POST, 'thumbH', FILTER_VALIDATE_INT);
        $data['thumbnail_W'] = filter_input(INPUT_POST, 'thumbW', FILTER_VALIDATE_INT);
        $data['line_height'] = filter_input(INPUT_POST, 'lineHeight', FILTER_VALIDATE_INT);
        $data['sibling_gap'] = filter_input(INPUT_POST, 'siblingGap', FILTER_VALIDATE_INT);
        $data['marriage_gap'] = filter_input(INPUT_POST, 'marriageGap', FILTER_VALIDATE_INT);
        update_assoc($mysqli, 'tree', $treeId, $data);

        $fontarray = [
            [$data['title_font'], 'tf'],
            [$data['first_line_font'], 'flf'],
            [$data['other_line_font'], 'olf'],
            [$data['originator_font'], 'of']
        ];

        for ($i = 0; $i < count($fontarray); $i++) {
            $fi = $fontarray[$i][0];
            $pre = $fontarray[$i][1];
            $font = read_assoc($mysqli, 'font', $fi);
            $font['font_R'] = intval(filter_input(INPUT_POST, $pre . 'c_r'));
            $font['font_G'] = intval(filter_input(INPUT_POST, $pre . 'c_g'));
            $font['font_B'] = intval(filter_input(INPUT_POST, $pre . 'c_b'));
            $font['background_R'] = intval(filter_input(INPUT_POST, $pre . 'b_r'));
            $font['background_G'] = intval(filter_input(INPUT_POST, $pre . 'b_g'));
            $font['background_B'] = intval(filter_input(INPUT_POST, $pre . 'b_b'));
            $font['opaque_background'] = intval(filter_input(INPUT_POST, $pre . 'opaque') == 'on');
            $font['style'] = filter_input(INPUT_POST, $pre . 'family', FILTER_SANITIZE_STRING);
            $font['bold'] = intval(filter_input(INPUT_POST, $pre . 'bold') == 'on');
            $font['underline'] = intval(filter_input(INPUT_POST, $pre . 'underline') == 'on');
            $font['oblique'] = intval(filter_input(INPUT_POST, $pre . 'italic') == 'on');
            $font['size'] = intval(filter_input(INPUT_POST, $pre . 'size'));
            update_assoc($mysqli, 'font', $fi, $font);
        }
    }
    include $path . 'includes/redirect.php';
}

