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
    // delete_chunks($mysqli, $userId);
    ?>
    <script>
        const BYTES_PER_CHUNK = 32 * 1024; // 1024 * 1024; // 1MB chunk sizes.
        var blob;
        var filename;
        var filesize;
        var numchunk;
        var hash;
        var slice;

        function nextWrite() {
            if (slice < numchunk) {
                uploadFile();
            } else {
                document.getElementById("tipstr").innerHTML = "Upload complete. Now wait for decode";
                window.location.replace("/tree/decodeGedcom.php"); // validateGedcom.php");
            }
        }

        String.prototype.hashCode = function () {
            var hash = 0, i, chr;
            if (this.length === 0)
                return '00000000';
            for (i = 0; i < this.length; i++) {
                chr = this.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
            }
            if (hash < 0)
            {
                hash = 0xFFFFFFFF + hash + 1;
            }
            return hash.toString(16).toUpperCase();
        };

        function sendRequest() {
            document.getElementById("myBtn").disabled = true;
            document.getElementById("fileToUpload").disabled = true;
            blob = document.getElementById('fileToUpload').files[0];
            filename = blob.name;
            filesize = blob.size;
            numchunk = Math.ceil(filesize / BYTES_PER_CHUNK);
            hash = (filename + filesize.toString()).hashCode();
            slice = 0;
            setTimeout("uploadResume()", 1);
        }

        var str2ab_blobreader = function (str, callback) {
            var blob;
            BlobBuilder = window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder;
            if (typeof (BlobBuilder) !== 'undefined') {
                var bb = new BlobBuilder();
                bb.append(str);
                blob = bb.getBlob();
            } else {
                blob = new Blob([str]);
            }
            var f = new FileReader();
            f.onload = function (e) {
                callback(e.target.result);
            };
            f.readAsArrayBuffer(blob);
        };

        function uploadFile() {
            var xhr;
            var chunk;
            start = slice * BYTES_PER_CHUNK;
            end = (slice + 1) * BYTES_PER_CHUNK;
            if (end > filesize) {
                end = filesize;
            }
            xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState === 4) { // Finished
                    if (this.status === 200) {
                        if (this.responseText === 'OK') {
                            document.getElementById("tipstr").innerHTML = "".concat(slice.toString(), "/", numchunk.toString(), " : OK");
                            setTimeout("uploadResume()", 1);
                        } else {
                            // failed to return OK response text 200 !!
                            document.getElementById("tipstr").innerHTML = "Upload error message: " + this.responseText;
                        }
                    } else {
                        document.getElementById("tipstr").innerHTML = "Upload error status: " + this.status.toString() + ". Retry in 10s...";
                        setTimeout("uploadResume()", 10000);
                    }
                }
            };

            if (blob.webkitSlice) {
                chunk = blob.webkitSlice(start, end);
            } else if (blob.mozSlice) {
                chunk = blob.mozSlice(start, end);
            } else {
                chunk = blob.slice(start, end);
            }

            xhr.open("post", "upload.php", /* async */ true);
            xhr.setRequestHeader("X-Index", slice);

            if (blob.webkitSlice) {
                var buffer = str2ab_blobreader(chunk, function (buf) {
                    xhr.send(buf);
                });
            } else {
                xhr.send(chunk);
            }
        }

        function uploadResume() {
            var xhr;
            xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState === 4) { // Finished
                    if (this.status === 200) {
                        slice = parseInt(this.responseText);
                        // document.getElementById("tipstr").innerHTML = "Next chunk will be: " + this.responseText + " = " + slice.toString();
                        setTimeout("nextWrite()", 1);
                    } else {
                        document.getElementById("tipstr").innerHTML = "Upload_resume error status: " + this.status.toString();
                    }
                }
            };

            xhr.open("post", "upload_resume.php", /* async */ true);
            xhr.setRequestHeader("X-File-Name", filename);
            xhr.setRequestHeader("X-File-Size", filesize);
            xhr.setRequestHeader("X-Hash", hash);
            xhr.setRequestHeader("X-Total", numchunk);
            xhr.send();
        }
    </script>
    <noscript>
    Sorry... JavaScript is needed to go ahead.
    </noscript>

    <div class="w3-row w3-padding-small">
        <div class="w3-quarter w3-container">
        </div>
        <div class="w3-half w3-container">
            <div class="w3-container w3-padding-small w3-white">
                <h1>GEDCOM file upload</h1>
            </div>

            <div class="w3-container w3-padding-small w3-white">
                <div id="tipstr">Select a file to upload then press send</div><br>
                <input type="file" name="file" id="fileToUpload"><br>
            </div>
            <div class="w3-container w3-padding-small w3-white">
                <button onclick="sendRequest()" class="w3-button w3-light-grey w3-border w3-block" id="myBtn">Send</button> 
                <h3>This may take some time if the file is large</h3>
            </div>

        </div>
        <div class="w3-quarter w3-container">
        </div>
    </div>

    <?php

}
include $path . 'includes/footer.php';
