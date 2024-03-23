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

$type = "submitter";
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

    $addressData = read_assoc($mysqli, "address", $object['address_id']);
    ?>
    <form action="/edit/edited_submitter.php?subm=<?php echo $return[$type]; ?>" method="POST">
        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center">
                <h2>Submitter</h2>
            </div>
        </div>

        <?php include $path . "includes/referenced_from.php"; ?>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-container w3-white w3-center w3-padding">
                <div class="w3-row">
                    <div class="w3-half w3-padding-small">
                        <div class="w3-row">
                            <div class="w3-quarter"><p>Name:</p></div>
                            <div class="w3-threequarter">
                                <p><input type="text" class="w3-block" id="name" name="name"
                                          maxlength="60" placeholder="Name" style="border: 1px solid black;"
                                          value= "<?php echo $object['name']; ?>" /></P>
                            </div>
                        </div>
                        <div class="w3-row">
                            <div class="w3-quarter"><p>RFN:</p></div>
                            <div class="w3-threequarter">
                                <p><input type="text" class="w3-block" id="registered_RFN" name="registered_RFN"
                                          maxlength="30" placeholder="Record file number" style="border: 1px solid black;"
                                          value= "<?php echo $object['registered_RFN']; ?>" /></P>
                            </div>
                        </div>
                    </div>
                    <div class="w3-half w3-padding-small">
                        <div class="w3-quarter"><p>Address:</p></div>
                        <div class="w3-threequarter">
                            <p><input type="text" class="w3-block" id="line1" name="line1"
                                      maxlength="60" placeholder="Line 1" style="border: 1px solid black;"
                                      value= "<?php echo $addressData['line1']; ?>" />
                                <input type="text" class="w3-block" id="line2" name="line2"
                                       maxlength="60" placeholder="Line 2" style="border: 1px solid black; border-top: 0px"
                                       value= "<?php echo $addressData['line2']; ?>" />
                                <input type="text" class="w3-block" id="line3" name="line3"
                                       maxlength="60" placeholder="Line 3" style="border: 1px solid black; border-top: 0px"
                                       value= "<?php echo $addressData['line3']; ?>" />
                                <input type="text" class="w3-block" id="city" name="city"
                                       maxlength="60" placeholder="City" style="border: 1px solid black; border-top: 0px"
                                       value= "<?php echo $addressData['city']; ?>" />
                                <input type="text" class="w3-block" id="state" name="state"
                                       maxlength="60" placeholder="State" style="border: 1px solid black; border-top: 0px"
                                       value= "<?php echo $addressData['state']; ?>" />
                                <input type="text" class="w3-block" id="postal_code" name="postal_code"
                                       maxlength="10" placeholder="Postal code" style="border: 1px solid black; border-top: 0px"
                                       value= "<?php echo $addressData['postal_code']; ?>" />
                                <input type="text" class="w3-block" id="country" name="country"
                                       maxlength="60" placeholder="Country" style="border: 1px solid black; border-top: 0px"
                                       value= "<?php echo $addressData['country']; ?>" />
                                <input type="text" class="w3-block" id="phone" name="phone"
                                       maxlength="25" placeholder="Phone" style="border: 1px solid black; border-top: 0px"
                                       value= "<?php echo $addressData['phone']; ?>" /></P>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <input type="submit" value="Submit" class="w3-bar-item w3-button w3-white w3-left" style="width:49%">
                <a href="/browseLink.php?type=submitter" class="w3-bar-item w3-button w3-white w3-right" style="width:49%"><b>Cancel</b></a>
            </div>
        </div>

    </form>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>

    <?php
}
include $path . 'includes/footer.php';
