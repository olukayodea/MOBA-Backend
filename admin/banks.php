<?php
    $redirect = "admin/banks";
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

    if (isset($_POST['submitCat'])) {
        unset($_POST['submitCat']);
        $add = $adminBanks->postMew($_POST);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Bank Added"));
        } else {
            header("location: ?error=".urlencode("Bank not Added"));
        }
    }
    if (isset($_REQUEST['delete'])) {
        $add = $adminBanks->removeCate($_REQUEST['delete']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Bank removed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Bank not removed"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminBanks->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Bank Status changed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Bank Status not changed"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Banks</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
<?php $adminBanks->navigationBar($redirect); ?>
    <?php $adminBanks->pageContent($redirect, $view, $edit); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>