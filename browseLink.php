<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/';
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

if (strlen($errorTitle) == 0) {
    switch ($return['type']) {
        case "note":
            $typeName = "Note";
            $singular = "note";
            $plural = "notes";
            $ind = "n";
            break;
        case "media":
            $typeName = "Media";
            $singular = "media";
            $plural = "media";
            $ind = "m";
            break;
        case "source":
            $typeName = "Source";
            $singular = "source";
            $plural = "sources";
            $ind = "sour";
            break;
        case "submitter":
            $typeName = "Submitter";
            $singular = "submitter";
            $plural = "submitters";
            $ind = "subm";
            break;
        default:
            $errorTitle = "Selection Error";
            $errorMessage = "Unknown type";
            $errorRedirect = "/tree/tree.php";
            break;
    }
}

if (strlen($errorTitle) == 0) {
    $treeData = read_assoc($mysqli, 'tree', $treeId);
    $author = $treeData['author'];
    if ($return['type'] == "source") {
        $objectlinks = read_all_assoc($mysqli, 'citation', $treeId);
    } else {
        $objectlinks = read_all_assoc($mysqli, $return['type'] . '_link', $treeId);
    }

    $array = get_all_descriptions($return['type'], $mysqli, $treeId);
    $browse = [];
    foreach ($array as $id => $value) {
        $line = [];
        array_push($line, $value);
        array_push($line, '/edit/edit_' . $return['type'] . '.php?' . $ind . '=' . $id);
        if (($return['type'] == "submitter") && ($id == $author)) {
            array_push($line, "Options (Author)");
            array_push($line, "/edit/edit_options.php");
        }
        foreach ($objectlinks as $nl) {
            if ($nl[$return['type'] . '_id'] == $id) {
                addDecriptionAndRedirectForObject($mysqli, $line, $nl, $class);
            }
        }
        array_push($browse, $line);
    }
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    usort($browse, function ($a, $b) {
        return strtoupper($a[0]) > strtoupper($b[0]);
    });
    $nLink = [];
    foreach ($browse as $line) {
        array_push($nLink, (sizeof($line) / 2) - 1);
    }
?>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <a href="/edit/create.php?type=<?php echo $return['type']; ?>" class="w3-button w3-light-grey w3-border w3-block">Click here to add a <?php echo $singular; ?></a>
    </div>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white">
            <h3>These are the <?php echo $plural; ?> in this tree:</h3>
            <div style="overflow-x: auto;">
                <div class="w3-container w3-padding" style="min-width: 500px">
                    <table class="w3-table-all">
                        <tr class="w3-grey">
                            <th><b><strong><?php echo $typeName; ?></strong></b></th>
                            <th><b><strong>Referenced from</strong></b></th>
                        </tr>
                        <?php
                        for ($i = 0; $i < sizeof($browse); $i++) {
                            if ($nLink[$i] == 0) {
                        ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $browse[$i][1]; ?>" class="w3-block" style="text-decoration: none;"><?php echo $browse[$i][0]; ?></a>
                                    </td>
                                    <td>
                                        <a href="#" class="w3-block" style="text-decoration: none;">Nowhere</a>
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td rowspan="<?php echo $nLink[$i]; ?>">
                                        <a href="<?php echo $browse[$i][1]; ?>" class="w3-block" style="text-decoration: none;"><?php echo $browse[$i][0]; ?></a>
                                    </td>
                                    <td>
                                        <a href="<?php echo $browse[$i][3]; ?>" class="w3-block" style="text-decoration: none;"><?php echo $browse[$i][2]; ?></a>
                                    </td>
                                </tr>
                                <?php
                                if ($nLink[$i] > 1) {
                                    for ($j = 2; $j <= $nLink[$i]; $j++) {
                                ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo $browse[$i][$j * 2 + 1]; ?>" class="w3-block" style="text-decoration: none;"><?php echo $browse[$i][$j * 2]; ?></a>
                                            </td>
                                        </tr>
                        <?php
                                    }
                                }
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php
}
include $path . 'includes/footer.php';
