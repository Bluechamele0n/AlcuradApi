<!DOCTYPE html>
<html lang="en">
    <?php
    // shared utilities
    include 'php/util.php';
    // get content from hompage.php
    include 'php/pagehandler.php';
    include 'php/dokumenthandler.php';
    include 'php/profilehandler.php';
    include 'php/languagehandler.php';
    //include 'php/alcuradapi.php';
    ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hellapi</title>
</head>
<body>
    <?php
    //print_r($_POST);

    // Handle the button click
    page("home");
    ?>
</body>
</html>