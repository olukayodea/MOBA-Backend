<?php
    $redirect = "ads";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if (isset($_REQUEST['view'])) {
        $view = trim($_REQUEST['view'], "/");
    } else {
        $view = "active";
    }
    if (isset($_REQUEST['remove'])) {
        $rem = $userPostedAds->removeDraft($_REQUEST['remove']);

        if ($rem) {
            header("location: ".URL.$redirect."/".$view."?done=".urlencode("Draft ad removed successfully"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("Draft ad not removed successfully"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Moba - Find the best artisans</title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<?php $userPostedAds->navigationBar($redirect); ?>
<div class="container-fluid">
    <?php $userPostedAds->pageContent($ref, $view, $redirect); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>