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

if (strlen($errorTitle) == 0) {
    $browse = [];
    switch ($return['type']) {
        case "individual" :
            $singular = "person";
            $plural = "people";
            $array = get_all_descriptions('individual', $mysqli, $treeId);
            foreach ($array as $id => $value) {
                array_push($browse, [$value, "/edit/edit_individual.php?i=" . $id]);
            }
            break;
        case "family" :
            $singular = "family";
            $plural = "families";
            $array = get_all_descriptions('family', $mysqli, $treeId);
            foreach ($array as $id => $value) {
                array_push($browse, [$value, "/edit/edit_family.php?f=" . $id]);
            }
            break;
        default:
            $errorTitle = "Selection Error";
            $errorMessage = "Unknown type";
            $errorRedirect = "/tree/tree.php";
            break;
    }
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    usort($browse, function($a, $b) {
        return $a[0] > $b[0];
    });
    ?>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <a href="/edit/create.php?type=<?php echo $return['type']; ?>"
           class="w3-button w3-light-grey w3-border w3-block">Click here to add a <?php echo $singular; ?></a>
    </div>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white">
            <h3>These are the <?php echo $plural; ?> in this tree:</h3>

            <div style="overflow-x: auto;">
                <div class="w3-container w3-padding"  style="min-width: 500px">
                    <table class="w3-table-all">
                        <tr class="w3-grey">
                            <th><strong>Name</strong></th>
                        </tr>
                        <?php
                        foreach ($browse as $value) {
                            ?>
                            <tr>
                                <td>
                                    <a class="w3-block" style="text-decoration: none;" 
                                       href= <?php echo "$value[1]"; ?>><?php echo "$value[0]"; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php
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
