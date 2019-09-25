<?php
    $redirect = "notifications";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "all";
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Messages and Notification</title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<?php $userHome->navigationBarNotification($redirect); ?>
<div class="container-fluid">
    <?php $userHome->pageContent($redirect, false, $view); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>