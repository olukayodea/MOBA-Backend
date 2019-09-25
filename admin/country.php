<?php
    $redirect = "admin/country";
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
    
    if (isset($_POST['submitCountry'])) {
        unset($_POST['submitCountry']);
        $_POST['name'] = $_POST['name'];
        $_POST['code'] = strtoupper($_POST['code']);
        $_POST['currency'] = strtoupper($_POST['currency']);
        $add = $adminCountry->postMew($_POST);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Country created"));
        } else {
            header("location: ?error=".urlencode("COuntry not created"));
        }
    } else if (isset($_POST['set_is_default'])) {
        $add = $adminCountry->setDefault($_POST['is_default']);
        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Default COuntry Set"));
        } else {
            header("location: ?error=".urlencode("Default COuntry not Set"));
        }
    }
    if (isset($_REQUEST['delete'])) {
        $add = $adminCountry->removeCountry($_REQUEST['delete']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Country created"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Country not created"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminCountry->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Country Status changed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Country Status not changed"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Countries</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
<?php $adminCountry->navigationBar($redirect); ?>
    <?php $adminCountry->pageContent($redirect, $view, $edit); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>