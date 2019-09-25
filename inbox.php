<?php
    $redirect = "inbox";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");
    
    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "inbox";
    }
    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
    } else {
        $id = 0;
    }
    if (isset($_REQUEST['user'])) {
        $user = $_REQUEST['user'];
    } else {
        $user = 0;
    }

    if (isset($_POST['forward'])) {
        $view = "compose";
        $id = $_POST['forward'];
    } else if (isset($_POST['sendMail'])) {
        unset($_POST['sendMail']);
        $add = $inbox->add($_POST);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Message sent"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("Message not sent"));
        }
    } else if (isset($_POST['inboxAction'])) {
        $add = $inbox->manage($_POST);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Message sent"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("Message not sent"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Messages</title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $inboxPages->navigationBar($redirect); ?>
    <?php $inboxPages->pageContent($view, $id, $user); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>