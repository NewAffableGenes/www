<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if (($userId < 0) ||
        ($userTreeId >= 0) ||
        ($subscription != 1)) {
    echo "Upload error (UID = $userId | TreeID = $userTreeId | Subs = $subscription)";
} else {

    // echo $filename . " " . $filesize . " " . $index . " " . $total;
    // die();
    // do_log($mysqli, 'Upload slice: ' . $index);

    if (!isset($_SERVER['HTTP_X_INDEX'])) {
        echo 'Index required';
    } else if (!preg_match('/^[0-9]+$/', $_SERVER['HTTP_X_INDEX'])) {
        echo 'Index error ' . $_SERVER['HTTP_X_INDEX'];
    } else {
        $index = $_SERVER['HTTP_X_INDEX'];
        $userdata = read_assoc($mysqli, "user", $userId);
        $data = array(
            "content" => null,
            "count" => $index,
            "user_id" => $userId);
        $chunkId = create_assoc($mysqli, "chunk", $data);
        $data['content'] = file_get_contents("php://input");
        update_assoc($mysqli, 'chunk', $chunkId, $data);
        $userdata['upload_lastchunk'] = $index;
        update_assoc($mysqli, 'user', $userId, $userdata);
        // do_log($mysqli, 'Written slice: ' . $index);
        echo 'OK';
    }
}

