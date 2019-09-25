<?php
    $redirect = "admin/users";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");
    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "users";
    }
    if (isset($_REQUEST['edit'])) {
        $edit = $_REQUEST['edit'];
        if ($users->toggleAdmin($edit)) {
            header("location: ".URL.$redirect."?done=".urlencode("Modification completed on User account"));
        } else {
            header("location: ?error=".urlencode("User account not modified"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminUsers->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."/".$view."?done=".urlencode("Country Status changed"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("Country Status not changed"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Users and Administrators</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $adminUsers->pageContent($redirect, $view); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>