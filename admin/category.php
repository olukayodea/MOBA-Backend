<?php
    $redirect = "admin/category";
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
        
        if (isset($_FILES['img'])) {
            $file = $_FILES;
        } else {
            $file = false;
        }
        $add = $adminCategory->postMew($_POST, $file);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Category List Updated"));
        } else {
            header("location: ?error=".urlencode("Category not created"));
        }
    }
    if (isset($_REQUEST['updateImg'])) {
        $add = $adminCategory->removeImage($_REQUEST['edit']);

        if ($add) {
            header("location: ".URL.$redirect."/create?edit=".$_REQUEST['edit']."&done=".urlencode("Category icon removed"));
        } else {
            header("location: ".URL.$redirect."/create?edit=".$_REQUEST['edit']."&error=".urlencode("Category icon not removed"));
        }
    } else if (isset($_REQUEST['delete'])) {
        $add = $adminCategory->removeCate($_REQUEST['delete']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Category removed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Category not removed"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminCategory->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Category Status changed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Category Status not changed"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Categories</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
<?php $adminCategory->navigationBar($redirect); ?>
    <?php $adminCategory->pageContent($redirect, $view, $edit); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>