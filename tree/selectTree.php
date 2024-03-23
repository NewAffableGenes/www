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
    $objects = read_all_rights($mysqli, NULL, $userId);
    $nRights = count($objects);
    ?>
    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white">
            <h2>Select Tree</h2>
        </div>
    </div>

    <div class="w3-container w3-padding-small" style="max-width: 1000px; margin: auto">
        <div class="w3-container w3-white">
            <?php
            if ($nRights == 0) {
                ?>
                <p>You have not been offered the right to see anyone else's tree<br>Why not ask a friend to share their tree with you?</p>
                <a type="button" class="w3-button w3-block" href="/tree/tree.php">OK</a> 
                <?php
            } else {
                ?>
                <h3>Permission:</h3>
                <div style="overflow-x: auto">
                    <div class="w3-container w3-padding"  style="min-width: 500px">
                        <table class="w3-table-all">
                            <tr class="w3-grey">
                                <th style="text-align: center">From</th>
                                <th style="text-align: center">Read</th>
                                <th style="text-align: center">Write</th>
                                <th style="text-align: center">PDF</th>
                                <th style="text-align: center">Copy</th>
                                <th style="text-align: center">Accepted</th>
                                <th style="text-align: center">Delete</th>
                            </tr>
                            <?php
                            foreach ($objects as $row) {
                                $giver = read_assoc($mysqli, 'user', $row['rights_giver']);
                                if ($giver == null) {
                                    $giver_name = 'Error';
                                } else {
                                    $giver_name = $giver['username'];
                                }
                                ?>
                                <tr>
                                    <?php
                                    if ($row['rights_accepted']) {
                                        ?>
                                        <td class="w3-center">
                                            <a href="/tree/view_tree_with_rights.php?c=<?php echo $row['id']; ?>"  style="text-decoration: none"
                                               class="w3-block w3-hover-grey" data-toggle="tooltip" title="View tree"><span style='color: blue;'><?php echo $giver_name; ?></span></a>
                                        </td>      
                                        <?php
                                    } else {
                                        ?>
                                        <td class="w3-center">
                                            <span style='color: black;'><?php echo $giver_name; ?></span>
                                        </td>      
                                        <?php
                                    }
                                    ?>
                                    <td class="w3-center">
                                        <?php
                                        echo "<span style='color: green;'>&#x2714;</span>";
                                        ?>
                                    </td>
                                    <td class="w3-center">
                                        <?php
                                        if ($row['write_allowed']) {
                                            echo "<span style='color: green;'>&#x2714;</span>";
                                        } else {
                                            echo "<span style='color: red;'>&#x274c;</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="w3-center">
                                        <?php
                                        if ($row['export_pdf_allowed']) {
                                            echo "<span style='color: green;'>&#x2714;</span>";
                                        } else {
                                            echo "<span style='color: red;'>&#x274c;</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="w3-center">
                                        <?php
                                        if ($row['download_allowed']) {
                                            echo "<span style='color: green;'>&#x2714;</span>";
                                        } else {
                                            echo "<span style='color: red;'>&#x274c;</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="w3-center">
                                        <?php
                                        if ($row['rights_accepted']) {
                                            echo "<span style='color: green;'>&#x2714;</span>";
                                        } else {
                                            ?>
                                            <a href="/tree/accept_rights.php?c=<?php echo $row['id']; ?>" style="text-decoration: none"
                                               class="w3-block w3-hover-grey" data-toggle="tooltip" title="Accept rights"><span style='color: blue;'><b>Click to Accept</b></span></a>
                                               <?php
                                           }
                                           ?>
                                    </td>
                                    <td class="w3-center">
                                        <a href="/tree/delete_given_rights.php?c=<?php echo $row['id']; ?>" style="text-decoration: none"
                                           class="w3-block w3-hover-grey" data-toggle="tooltip" title="Delete rights"><span style='color: blue;'><b>&#x1F5D1;</b></span></a>
                                    </td>                
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
                <br>
                <p>Tip: You must accept the offered rights before you can view the tree. If you have the right to modify a tree please be careful and respect the feelings of the giver</p>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}
include $path . 'includes/footer.php';
