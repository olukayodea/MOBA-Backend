<?php
    $redirect = "admin/users.view";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");
    if (isset($_REQUEST['ref'])) {
        $user_id = $_REQUEST['ref'];
    } else {
        header("location: users");
    }

    $view = "oneView";

    if (isset($_POST['approve'])) {
        $add = $users->verify($_POST['ref'], 1);

        if ($add) {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification completed"));
        } else {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification not completed"));
        }
    } else if (isset($_POST['reject'])) {
        $add = $users->verify($_POST['ref'], 0);

        if ($add) {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification refused"));
        } else {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification not completed"));
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
    <?php $adminUsers->pageContent($redirect, $view, $user_id); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>