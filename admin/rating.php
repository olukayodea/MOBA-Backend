<?php
    $redirect = "admin/rating";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");


    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "list";
    }
    if (isset($_REQUEST['edit'])) {
        $edit = $_REQUEST['edit'];
    } else {
        $edit = 0;
    }
    
    if (isset($_POST['submitRating'])) {
        unset($_POST['submitRating']);
        $add = $adminRating->postMew($_POST);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Rating Question created"));
        } else {
            header("location: ?error=".urlencode("Rating Question not created"));
        }
    }
    if (isset($_REQUEST['delete'])) {
        $add = $adminRating->removeRating($_REQUEST['delete']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Rating Question Removed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Rating Question not Removed"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminRating->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Rating Question Status changed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Rating Question Status not changed"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Rating Questions</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $adminRating->navigationBar($redirect); ?>
    <?php $adminRating->pageContent($redirect, $view, $edit); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>