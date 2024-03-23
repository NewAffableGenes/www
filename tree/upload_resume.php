<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";

if (($userId < 0) ||
        ($userTreeId >= 0) ||
        ($subscription != 1)) {
    echo "Upload error (UID = $userId | TreeID = $userTreeId | Subs = $subscription)";
} else {

    if (!isset($_SERVER['HTTP_X_FILE_NAME'])) {
        echo 'Name required';
    } else if (!isset($_SERVER['HTTP_X_FILE_SIZE'])) {
        echo 'Size required';
    } else if (!isset($_SERVER['HTTP_X_TOTAL'])) {
        echo 'Number of chunks required';
    } else if (!isset($_SERVER['HTTP_X_HASH'])) {
        echo 'Hash required';
    } else if (!preg_match('/^[0-9]+$/', $_SERVER['HTTP_X_TOTAL'])) {
        echo 'Number of chunks error';
    } else if (!preg_match('/^[0-9]+$/', $_SERVER['HTTP_X_FILE_SIZE'])) {
        echo 'File size error';
    } else {
        $filename = $_SERVER['HTTP_X_FILE_NAME'];
        $filesize = intval($_SERVER['HTTP_X_FILE_SIZE']);
        $hash = $_SERVER['HTTP_X_HASH'];
        $numchunk = intval($_SERVER['HTTP_X_TOTAL']);

        $userdata = read_assoc($mysqli, "user", $userId);
        if (($userdata['upload_filename'] == $filename) &&
                ($userdata['upload_filesize'] == $filesize) &&
                ($userdata['upload_hash'] == $hash) &&
                ($userdata['upload_numchunk'] == $numchunk)) {
            $nextchunk = $userdata['upload_lastchunk'] + 1;
            echo $nextchunk;
        } else {
            delete_chunks($mysqli, $userId);
            $userdata = read_assoc($mysqli, "user", $userId);
            do_log($mysqli, "New upload $filename, $filesize, $hash, $numchunk");
            $userdata['upload_filename'] = $filename;
            $userdata['upload_filesize'] = $filesize;
            $userdata['upload_hash'] = $hash;
            $userdata['upload_numchunk'] = $numchunk;
            $userdata['upload_lastchunk'] = -1;
            update_assoc($mysqli, 'user', $userId, $userdata);
            echo '0';
        }
    }
}

