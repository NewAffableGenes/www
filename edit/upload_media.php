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

$type = "media";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
    $errorRedirect = "/tree/tree.php";
}

if ((strlen($errorTitle) == 0) && (!$writeAllowed)) {
    $errorTitle = "Write Error: $type";
    $errorMessage = "You do not have write access to this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $fileLimit = 20000000;
    $redirect = "/edit/edit_$type.php?m=" . $return[$type];
    if (isset($_POST['btn-upload'])) {
        $file_name = $_FILES['file']['name'];
        // echo $file_name;
        $file_size = $_FILES['file']['size'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_type = $_FILES['file']['type'];
        if ($file_size > $fileLimit) {
            do_log($mysqli, "Tree upload - Too large ($fileLimit)");
            include $path . "includes/header.php";
            include $path . "includes/navbar.php";
            $errorTitle = "Uplaod Error: $type";
            $errorMessage = "File size exceeds maximum which is 20MB";
            include $path . "includes/error.php";
            include $path . 'includes/footer.php';
        } else {
            $object = read_assoc($mysqli, $type, $return[$type]);
            // GEDCOM Types [ bmp | gif | jpeg | ole | pcx | tiff | wav ] 
            // But I will just use extension so pdf etc can also be used
            $fname = $file_name;
            $ftypep = strrpos($fname, ".");
            if ($ftypep === false) {
                $ftype = "";
            } else {
                $ftype = substr($fname, $ftypep + 1);
                $fname = substr($fname, 0, $ftypep);
            }
            $ftype = strtolower($ftype);
            $typeposn = strpos("bmp.gif.jpeg.jpg.wav.pdf", $ftype);
            if ($typeposn === false) {
                include $path . "includes/header.php";
                include $path . "includes/navbar.php";
                $errorTitle = "Uplaod Error: $type";
                $errorMessage = "File type is not supported. Try; bmp, gif, jpeg, jpg, wav or pdf";
                include $path . "includes/error.php";
                include $path . 'includes/footer.php';
            } else {
                $object['format'] = $ftype;
                $object['mime_type'] = $file_type;
                if (($object['title'] === null) || ($object['title'] === "")) {
                    $object['title'] = $fname;
                }
                if ($object['content'] !== null) {
                    unlink($media_path . $object['content']); // delete any existing file
                }
                $object['content'] = sprintf('%08d', $object['id']) . '.' . $object['format'];
                move_uploaded_file($file_tmp, $media_path . $object['content']);
                update_assoc($mysqli, $type, $return[$type], $object);
                include $path . 'includes/redirect.php';
            }
        }
    } else {
        echo "<body>Button btn-upload not posted - File probably too large<br>";
        echo "</body>";
    }
}
