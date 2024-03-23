<!DOCTYPE html>
<html>
    <head>
        <title><?php
            if (!isset($title) || (gettype($title) !== "string") || (strlen($title) == 0)) {
                $title = "Title";
            }
            if (!isset($redirect) || (gettype($redirect) !== "string") || (strlen($redirect) == 0)) {
                $redirect = '/welcome.php';
            }
            echo $title;
            ?> 
        </title>
    </head>
    <body>
        <!-- <p>Redirect: <?php echo $redirect; ?></p><br> --> <!-- uncomment for debug -->
        <script>
            window.location.replace("<?php echo $redirect; ?>");
        </script>
    </body>
</html>