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

$type = "media";
if ((strlen($errorTitle) == 0) && ($return[$type] == null)) {
    $errorTitle = "Selection Error: $type";
    $errorMessage = "You have not selected a $type in this tree";
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
    <form action="/edit/edited_media.php?m=<?php echo $return[$type]; ?>" method="POST">
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h2>Media</h2>
            </div>
        </div>

        <?php include $path . "includes/referenced_from.php"; ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-padding-small">
                <div class="w3-row w3-white">
                    <div class="w3-col" style="width: 75px"><p>Title:</p></div>
                    <div class="w3-twothird">
                        <p><input type="text" class="w3-border" id="title" name="title"
                                  maxlength="60" placeholder="Title" style="width: 100%"
                                  value= "<?php echo $object['title']; ?>"/></p>
                    </div>
                    <div class="w3-rest"><p>, Format: <?php echo $object['format']; ?></p></div>
                </div>

                <?php

                $missing = ($object['content'] === null);

                if ($missing) {// Missing - Allow to upload
                    echo '<div class="w3-contaner w3-center">';
                    echo '<img src = "/img/Missing.jpg?1" alt="Missing Media" align="centre">';
                    echo '</div>';
                    if ($writeAllowed) {
                        ?>
                        <button class="w3-button w3-block" type="submit" name="submit" 
                                value="upload">Upload media</button>
                                <?php
                            }
                        } else {
                            // Show the media if we can 
                            echo '<div class="w3-contaner w3-center">';
                            switch ($object['format']) {
                                case "wav":
                                    echo '<embed src="data:' . $object['mime_type'] . '; base64, ' .
                                    $object['content'] .
                                    '" alt="No preview" width="100%" align="centre" />';
                                    break;
                                case "pdf":
                                    echo '<iframe src="data:application/pdf; base64,' .
                                    $object['content'] .
                                    '" alt="No preview" width="100%" height="300" align="centre"></iframe>';
                                    break;
                                default:
                                    echo '<img src="data:' . $object['mime_type'] . '; base64, ' .
                                    $object['content'] .
                                    '" alt="No preview" style="max-width:100%" align="centre" />';
                                    break;
                            }
                            echo '</div>';
                            // Offer to replace
                            if ($writeAllowed) {
                                ?>

                        <div class="w3-bar">
                            <button class="w3-bar-item w3-button w3-left w3-light-grey" style="width:49%" type="submit" name="submit" 
                                    value="upload">Replace media</button>
                            <button class="w3-bar-item w3-button w3-right w3-light-grey" style="width:49%" type="submit" name="submit" 
                                    value="download">Download media</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

            <?php
            $FALStrings = completeFALList($mysqli, "media", $return['media'], $treeId, $class);
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
