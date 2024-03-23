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

if (strlen($errorTitle) > 0) {
    include $path . "includes/error.php";
} else {
    $objects = read_all_rights($mysqli, $userId, null);
    ?>
    <form class='w3-container' action='/tree/edited_rights.php' method='POST'>
        <div class="w3-container w3-padding-small">
            <div class="w3-container w3-white w3-center" style="max-width: 1000px; margin: auto">
                <h2>Tree Sharing</h2>
            </div>
            <div class="w3-container w3-padding-small">
                <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                    <h3>Existing permission:</h3>
                    <?php
                    if (count($objects) == 0) {
                        ?>
                        <p>You have not shared your tree</p>
                        <?php
                    } else {
                        ?>
                        <table class="w3-table" style="width: 100%">
                            <tr>
                                <th style="text-align: center">Name</th>
                                <th style="text-align: center">Read</th>
                                <th style="text-align: center">Write</th>
                                <th style="text-align: center">PDF</th>
                                <th style="text-align: center">Copy</th>
                                <th style="text-align: center">Delete</th>
                            </tr>
                            <?php foreach ($objects as $row) { ?>
                                <tr>
                                    <td class="fexpand" style="text-align: center">
                                        <?php
                                        $receiver = read_assoc($mysqli, 'user', $row['rights_receiver']);
                                        if($receiver == null) {
                                            echo "Error";
                                        } else {
                                            echo $receiver['username'];
                                        }
                                        ?>
                                    </td>
                                    <td class="fshrink" style="text-align: center">
                                        <span style='color: green;'>&#x2714;</span>
                                    </td>
                                    <td class="fshrink" style="text-align: center">
                                        <?php
                                        if ($row['write_allowed']) {
                                            echo "<span style='color: green;'>&#x2714;</span>";
                                        } else {
                                            echo "<span style='color: red;'>&#x274c;</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="fshrink" style="text-align: center">
                                        <?php
                                        if ($row['export_pdf_allowed']) {
                                            echo "<span style='color: green;'>&#x2714;</span>";
                                        } else {
                                            echo "<span style='color: red;'>&#x274c;</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="fshrink" style="text-align: center">
                                        <?php
                                        if ($row['download_allowed']) {
                                            echo "<span style='color: green;'>&#x2714;</span>";
                                        } else {
                                            echo "<span style='color: red;'>&#x274c;</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="fshrink" style="text-align: center">
                                        <a href="/tree/delete_rights.php?c=<?php echo $row['id']; ?>" style="text-decoration: none"
                                           data-toggle="tooltip" title="Delete rights"><span style='color: blue;'><b>&#x1F5D1;</b></span></a>
                                    </td>                
                                </tr>
                            <?php } ?>
                        </table>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="w3-container w3-padding-small">
                <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
                    <div class="w3-row">
                        <div class="w3-quarter"><h3>Offer rights to:</h3></div>
                        <div class="w3-threequarter">
                            <p><input type="username" class="w3-block" id="username" name="username" required
                                      maxlength="50" placeholder="Username" style="border: 1px solid black;"
                                      value= "" /></p>
                            <p><input type="checkbox" id="write_allowed" name="write_allowed" 
                                      style="border: 1px solid black;"> Allow this person to edit your tree. Only allow people you trust this privilege because they will be able to change and delete anything they want</input></p>
                            <p><input type="checkbox" id="export_pdf_allowed" name="export_pdf_allowed"
                                      style="border: 1px solid black;"> Allow this person to export a PDF copy of your tree</input></p>
                            <p><input type="checkbox" id="download_allowed" name="download_allowed"
                                      style="border: 1px solid black;"> Allow this person to save a copy of your tree in a GEDCOM file</input></p>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
            <div class="w3-bar">
                <input type="submit" name="submit" value="Make Offer" class="w3-bar-item w3-button w3-white w3-left" style="width:49%">
                <a href="/tree/tree.php" class="w3-bar-item w3-button w3-white w3-right" style="width:49%"><b>Exit</b></a>
            </div>
        </div>
    </form>


    <?php
}
include $path . 'includes/footer.php';
