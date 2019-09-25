<?php
    $redirect = "search";
    include_once("includes/functions.php");

    if ((isset($_REQUEST['type'])) && (isset($_REQUEST['s']))) {
        $type = $_REQUEST['type'];
        $s = $_REQUEST['s'];
    } else {
        header("location: ./");
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Search</title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<?php $pageHeader->selector(); ?>
<div class="container-fluid">
    <?php $userHome->pageContent($redirect, $s, $type); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>