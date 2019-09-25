<?php
    $redirect = "paymentCards";
    include_once("includes/functions.php");
    include_once("includes/sessions.php");

    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "list";
    }

    if (isset($_POST['getPayment'])) {
        $add = $userPayment->postMew($_POST);
        if ($add) {
            if ($add['status'] == "OK") {
                header("location: ".URL.$redirect."?done=".urldecode("Payment Card Added"));
            } else {
                header("location: ".URL.$redirect."/".$view."/?error=".urldecode($add['message']));
            }
        } else {
            header("location: ".URL.$redirect."/".$view."/?error=".urldecode("Payment Card not added"));
        }
    } else if (isset($_POST['set_is_default'])) {
        $add = $userPayment->setDefault($_POST['is_default']);
        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Default card set"));
        } else {
            header("location: ?error=".urlencode("Default card not set"));
        }
    }
    if (isset($_REQUEST['delete'])) {
        $add = $userPayment->removeCard($_REQUEST['delete']);

        if ($add) {
            if ($add == "0000") {
                header("location: ".URL.$redirect."?error=".urlencode("Payment card not removed"));
            } else {
                header("location: ".URL.$redirect."?done=".urlencode("Payment Card removed"));
            }
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Payment card not removed"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $userPayment->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Payment card Status changed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Payment card Status not changed"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Manage Payment Cards</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>

<div class="container-fluid">
<?php $userPayment->navigationBar($redirect); ?>
  <?php $userPayment->pageContent($redirect, $view, $edit); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>