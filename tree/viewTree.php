<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/';
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

if (strlen($errorTitle) == 0) {
    // Make sure Display Root is valid!
    $treeData = read_assoc($mysqli, "tree", $treeId);
    $DisplayRootId = $treeData['root'];
    if ($DisplayRootId == null) {
        $errorTitle = "Export Error";
        $errorMessage = "You have not selected a 'Root' person from whom to build the tree<br>"
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
    $ep = new createOutput(
        $mysqli,
        $treeId,
        false,
        $media_path,
        false,
        $path . $backgrounds[intval($treeData['watermark_media_id'])][1]
    );
?>

    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1; maximum-scale=1; user-scalable=no;">
        <meta name="description" content="Beautiful Genealogy Family Tree Editor and Viewer with PDF Output">
        <meta name="author" content="The Affable Genes Company">
        <title>Affable Genes</title>
        <link rel="shortcut icon" href="/img/AGfavicon.ico?<?php echo time(); ?>">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
        <style type="text/css">
            body {
                background-color: #cccccc;
                font-family: "Lato", sans-serif;
                overflow: hidden;
            }

            canvas {
                border: 1px solid black;
            }

            html,
            body {
                width: 100%;
                height: 100%;
                margin: 0;
            }
        </style>
        <script type="text/javascript">
            <?php
            $ep->writeScriptOutput();
            if ($return['individual'] == null) {
                echo "var zoom = 0.0;\n";
                echo "var offset_x = uw / 2.0;\n";
                echo "var offset_y = uh / 2.0;\n";
            } else {
                $bi = $ep->Boxes->getBox('individual', $return['individual']);
                if ($bi != null) {
                    $bd = $ep->Boxes->list[$bi];
                    echo "var zoom = 1.0;\n";
                    echo "var offset_x = " . ($bd[2] + $bd[4] / 2.0) . ";\n";
                    echo "var offset_y = " . ($bd[3] + $bd[5] / 2.0) . ";\n";
                } else {
                    echo "var zoom = 0.0;\n";
                    echo "var offset_x = uw / 2.0;\n";
                    echo "var offset_y = uh / 2.0;\n";
                }
            }
            ?>
        </script>
        <!-- TODO remove time - only there to ensure new download each refresh. For Debug -->
        <script src="/js/treeView.js<?php echo "?" . time(); ?>"></script>
    </head>

    <body onload="ol()">
        <canvas id="canvas" width="300" height="300" style="top:0px; left:0px; width: 10px; height: 10px;"></canvas>
    </body>

    </html>
<?php
}
