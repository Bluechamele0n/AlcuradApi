<!DOCTYPE html>
<html lang="en">
    <?php
    // shared utilities

    // get content from hompage.php
    include 'php/pagehandler.php';

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
    requests();
    ?>
</body>
</html>