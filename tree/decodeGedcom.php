<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
$problem = false;
if ($userId < 0) {
    $problem = true;
    $errorTitle = "Login Error";
    $errorMessage = "You are not logged in";
    $errorRedirect = "/user/login.php";
} else if ($userTreeId >= 0) {
    $problem = true;
    $errorTitle = "Upload Error";
    $errorMessage = "You cannot upload a GEDCOM file because you already have a tree<br>"
            . "If you want to upload a GEDCOM file you must delete your current tree. "
            . "However, you may want to download a copy before you delete it!";
    $errorRedirect = "/tree/tree.php";
} else if ($subscription != 1) {
    $problem = true;
    $errorTitle = "Upload Error";
    $errorMessage = "You cannot upload a GEDCOM file because you do not have a current subscription";
    $errorRedirect = "/tree/tree.php";
}

include $path . "includes/header.php";
include $path . "includes/navbar.php";

if ($problem) {
    include $path . "includes/error.php";
} else {
    ?>
    <script type="text/javascript">
        var firsttime = 'T';
        var feedback = "Unset";
        var xhr;

        function nextcnt() {
            decodeFile();
        }

        function decodeFile() {
            xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    feedback = xhr.responseText;
                    if (feedback == 'OK') {
                        document.getElementById("tipstr").innerHTML = "Decode complete!";
                        window.location.replace("/tree/finishedLoad.php");
                    } else if (feedback == 'Fail') {
                        document.getElementById("tipstr").innerHTML = "Decode error - Please check format";
                    } else {
                        document.getElementById("tipstr").innerHTML = feedback;
                        setTimeout("nextcnt()", 1); 
                    }
                }
            };

            xhr.open("post", "decode.php", true);
            xhr.setRequestHeader("X-First-Time", firsttime);
            firsttime = 'F';
            xhr.send();
        }
        
        setTimeout("nextcnt()", 1);
    </script>
    <noscript>
    Sorry... JavaScript is needed
    </noscript>

    <div class="w3-container w3-padding-small">
        <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
            <h2>GEDCOM file decode</h2>
        </div>
    </div>

    <div class="w3-container w3-padding-small">
        <div class="w3-container w3-white" style="max-width: 1000px; margin: auto">
            <div class="row">
                <div class="w3-quarter">
                </div>
                <div class="w3-half">
                    <div id="tipstr">Preparing to decode GEDCOM file ...</div><br><br>
                    <h3>This may take some time if the file is large</h3>
                </div>
                <div class="w3-quarter">
                </div>
            </div>
        </div>
    </div>

    </body>
    </html>
    <?php

}

include $path . 'includes/footer.php';
