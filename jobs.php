<?php
    $redirect = "jobs";
    include_once("includes/functions.php");
    if (isset($_REQUEST['id'])) {
        $cat_id = $_REQUEST['id'];
    } else {
        header("location: ./");
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $userHome->headMeta($cat_id); ?>
<?php $pageHeader->headerFiles(); ?>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $userHome->pageContent($redirect, $cat_id); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>