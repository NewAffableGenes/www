<?php

session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
include $path . "includes/globals.php";
include $path . "includes/GEDCOMLoader.php";

if (($userId < 0) || ($subscription != 1)) {
    do_log($mysqli,"No subscription");
    echo 'Fail - No subscription';
} else if (!isset($_SERVER['HTTP_X_FIRST_TIME'])) {
    echo 'X_FIRST_TIME parameter required';
} else {
    $firsttime = $_SERVER['HTTP_X_FIRST_TIME'];
    $GEDFile = new GEDCOMLoader($userId, $mysqli);
    if ($firsttime == 'T') {
        $treeId = createDefaultTree($mysqli, $userId, $media_path);
        set_user_tree($mysqli, $userId, $treeId);
        $GEDFile->treeId = $treeId;
    } else {
        $GEDFile->loc = $_SESSION['loc'];
        $GEDFile->numChunk = $_SESSION['numChunk'];
        $GEDFile->nextChunk = $_SESSION['nextChunk'];
        $GEDFile->currentChunk = $_SESSION['currentChunk'];
        $GEDFile->treeId = $_SESSION['treeId'];
        $GEDFile->ag_file = $_SESSION['ag_file'];
        $GEDFile->LineCnt = $_SESSION['LineCnt'];
    }
    
    $ok = $GEDFile->decode($media_path);
    if(!$ok){
        echo 'Decode failed';
    } else if ($GEDFile->complete) {
        echo 'OK';
    } else {
        echo ($GEDFile->nextChunk - 1) . '/' . $GEDFile->numChunk;
    }

    $_SESSION['loc'] = $GEDFile->lastloc;
    $_SESSION['numChunk'] = $GEDFile->numChunk;
    $_SESSION['nextChunk'] = $GEDFile->nextChunk;
    $_SESSION['currentChunk'] = $GEDFile->currentChunk;
    $_SESSION['treeId'] = $GEDFile->treeId;
    $_SESSION['ag_file'] = $GEDFile->ag_file;
    $_SESSION['LineCnt'] = $GEDFile->LineCnt;
}

