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

if ((strlen($errorTitle) == 0) && ($return['note'] == null)) {
    $errorTitle = "Note Selection Error";
    $errorMessage = "You have not selected a note in this tree";
    $errorRedirect = "/tree/tree.php";
}

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    if (!$writeAllowed) {
        ?>
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-light-grey w3-center">
                You do not have write access to this tree - changes will be ignored
            </div>
        </div>
        <?php
    }
    $type = "note";
    $object = read_assoc($mysqli, $type, $return[$type]);
    if ($type == "source") {
        $objectlinks = read_all_assoc($mysqli, 'citation', $treeId);
    } else {
        $objectlinks = read_all_assoc($mysqli, $type . '_link', $treeId);
    }
    $treeData = read_assoc($mysqli, 'tree', $treeId);
    $author = $treeData['author'];
    $line = [];
    if (($type == "submitter") && ($object['id'] == $author)) {
        array_push($line, "Options (Author)");
        array_push($line, "/edit/edit_options.php");
    }
    foreach ($objectlinks as $nl) {
        if ($nl[$type . '_id'] == $object['id']) {
            addDecriptionAndRedirectForObject($mysqli, $line, $nl, $class);
        }
    }
    $nLink = (sizeof($line) / 2);
    ?>
    <form action="/edit/edited_note.php?n=<?php echo $return['note']; ?>" method="POST">
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h2>Note</h2>
            </div>
        </div>

        <?php include $path . "includes/referenced_from.php"; ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-padding-small">
                <div class="w3-row w3-white">
                    <div class="w3-col" style="width: 100px;">
                        <p> Note:</p>
                    </div>
                    <div class="w3-rest">
                        <textarea ROWS=3 
                                  id="note" name="note" 
                                  style="width: 100%; resize: none; height: 200px; overflow-y: scroll; overflow-x: auto; text-align: left; white-space: normal;"
                                  ><?php echo $object['note']; ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $FALStrings = completeFALList($mysqli, "note", $return['note'], $treeId, $class);
        include $path . "includes/facts_and_links.php";
        ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <button class="w3-bar-item w3-button w3-white w3-left" style="width:49%" type="submit" name="submit" 
                        value="confirm">Submit Changes</button>
                <button class="w3-bar-item w3-button w3-white w3-right" style="width:49%" type="submit" name="submit" 
                        value="cancel">Cancel Changes</button>
            </div>
        </div>

    </form>
    <?php
}
include $path . 'includes/footer.php';
