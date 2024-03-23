<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Beautiful Genealogy Family Tree Editor and Viewer with PDF Output">
        <meta name="author" content="The Affable Genes Company">
        <title>Affable Genes</title>   
        <link rel="shortcut icon" href="/img/AGfavicon.ico?<?php echo time(); ?>">
        <link rel="stylesheet" href="/css/w3.css">
        <link rel="stylesheet" href="/css/affablegenes.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
        <style>
            body  {
                background-image: url("<?php echo "/img/Tartan.jpg?" . time(); ?>");
                background-color: #cccccc;
                font-family: "Lato", sans-serif
            }
        </style>
    </head>
    <body>

        <!-- Navbar -->
        <div class="w3-top">
            <div class="w3-bar w3-black w3-card">
                <a class="w3-bar-item w3-button w3-padding-large w3-hide-medium w3-hide-large w3-right" href="javascript:void(0)"
                   onclick="navDemoFunction()" title="Toggle Navigation Menu"><b>&#x2630;</b></a>
                <a href="/index.php" class="w3-bar-item w3-button w3-padding-large"><b>Affable Genes</b></a>
                <a href="/help.php" class="w3-bar-item w3-button w3-padding-large w3-hide-small">Help</a>

                <?php if ($userId >= 0) { ?> 
                    <a href="/user/logout.php" class="w3-bar-item w3-button w3-padding-large w3-hide-small w3-right">Log out</a>
                    <?php if ($treeId >= 0) { ?> 
                        <div class="w3-dropdown-hover w3-hide-small w3-right">
                            <button class="w3-padding-large w3-button" title="Browse">Browse &#x25bc;</button>     
                            <div class="w3-dropdown-content w3-bar-block w3-card-4">
                                <a href="/tree/tree.php" class="w3-bar-item w3-button">Trees</a>
                                <a href="/browse.php?type=individual" class="w3-bar-item w3-button">People</a>
                                <a href="/browse.php?type=family" class="w3-bar-item w3-button">Families</a>
                                <a href="/browseLink.php?type=note" class="w3-bar-item w3-button">Notes</a>
                                <a href="/browseLink.php?type=media" class="w3-bar-item w3-button">Media</a>
                                <a href="/browseLink.php?type=source" class="w3-bar-item w3-button">Sources</a>
                                <a href="/browseLink.php?type=submitter" class="w3-bar-item w3-button">Submitters</a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <a href="/user/register.php" class="w3-bar-item w3-button w3-padding-large w3-hide-small w3-right">Register</a>
                    <a href="/user/login.php" class="w3-bar-item w3-button w3-padding-large w3-hide-small w3-right">Login</a>
                <?php } ?>
            </div>
        </div>

        <!-- Navbar on small screens -->
        <div id="navDemo" class="w3-bar-block w3-black w3-hide w3-hide-large w3-hide-medium w3-top" style="margin-top:46px">
            <?php if ($userId < 0) { ?> 
                <a href="/user/login.php" class="w3-bar-item w3-button">Login</a>  
                <a href="/user/register.php" class="w3-bar-item w3-button">Register</a>
                <?php
            } else {
                if ($treeId >= 0) {
                    ?>
                    <a onclick="browseDropdownFunction()" href="javascript:void(0)" class="w3-button w3-block w3-black w3-left-align" id="myBtn">
                        Browse: <i>&#x25BC;</i>
                    </a>
                    <div id="browseDropdown" class="w3-bar-block w3-hide w3-padding-large w3-medium">
                        <a href="/tree/tree.php" class="w3-bar-item w3-button">Trees</a>
                        <a href="/browse.php?type=individual" class="w3-bar-item w3-button">People</a>
                        <a href="/browse.php?type=family" class="w3-bar-item w3-button">Families</a>
                        <a href="/browseLink.php?type=note" class="w3-bar-item w3-button">Notes</a>
                        <a href="/browseLink.php?type=media" class="w3-bar-item w3-button">Media</a>
                        <a href="/browseLink.php?type=source" class="w3-bar-item w3-button">Sources</a>
                        <a href="/browseLink.php?type=submitter" class="w3-bar-item w3-button">Submitters</a>
                    </div>
                    <?php
                }
            }
            ?>
            <a href="/help.php" class="w3-bar-item w3-button">Help</a>
            <?php if ($userId >= 0) { ?> 
                <a href="/user/logout.php" class="w3-bar-item w3-button">Log out</a>
            <?php } ?>

        </div>

        <!-- Page content -->
        <div class="w3-content" style="max-width:2000px;margin-top:46px">
