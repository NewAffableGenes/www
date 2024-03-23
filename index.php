<?php
session_start();
$path = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
// echo $path . '<br>';
// phpinfo();

$redirect = 'welcome.php';

require ($path . "includes/redirect.php");
