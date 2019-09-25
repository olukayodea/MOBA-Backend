<?php
    $redirect = "profile";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title><?php echo $screen_name."'s Profile"; ?></title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $userHome->pageContent($redirect); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>