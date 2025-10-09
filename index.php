<!DOCTYPE html>
<html lang="en">
    <?php
    // shared utilities

    // get content from hompage.php
    include __DIR__.'/php/pagehandler.php';
    // constantly load fonts

    //include 'php/alcuradapi.php';
    ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__.'/php/fonts.php'; ?>
    <link rel="icon" href="favicon.ico">
    <title>AlcuradApi</title>
</head>
<body>
    <?php
    //print_r($_POST);
    fontLoader();
    // Handle the button click
    //generateGoogleFontLinks(3000);
    webbsite();
    requests();
    ?>
</body>
</html>