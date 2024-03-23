<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
// All the PDF ralated stuff
// include $path . "output/CpAll.php";
include $path . "output/LoadAllToDisplay.php";
include $path . "output/BoxList.php";
include $path . "output/ConnectingLineList.php";
include $path . "output/OutputMap.php";
include $path . "output/OutputMapSlice.php";
include $path . "output/SiblingSpouseObject.php";
include $path . "output/createOutput.php";

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

if ((strlen($errorTitle) == 0) && (!$exportPdfAllowed)) {
    $errorTitle = "Export Error";
    $errorMessage = "You do not have rights to export a PDF of this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) == 0) {
// Make sure Display Root is valid!
    $treeData = read_assoc($mysqli, "tree", $treeId);
    $DisplayRootId = $treeData['root'];
    if ($DisplayRootId == null) {
        $errorTitle = "Export Error";
        $errorMessage = "You have not selected a \"Root\" person from whom to build the tree<br>"
                . "Click below to be taken to options where this may be set";
        $errorRedirect = "/edit/edit_options.php";
    }
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/header.php";
    include $path . "includes/navbar.php";
    include $path . "includes/error.php";
    include $path . 'includes/footer.php';
} else {
    $DisplayRoot = read_assoc($mysqli, "individual", $DisplayRootId);
    if ($DisplayRoot['show_me'] == 'n') {
        $DisplayRoot['show_me'] = 't';
        update_assoc($mysqli, "individual", $DisplayRootId, $DisplayRoot);
    }

    $ep = new createOutput($mysqli, $treeId, true, $media_path, false, $path . $backgrounds[$treeData['watermark_media_id']][1]);
    $ep->fpdf->Output('D', date('Y-m-d') . ' Affable Genes Family Tree.pdf');
    
}
