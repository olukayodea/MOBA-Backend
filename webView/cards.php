<?php
include_once("../includes/functions.php");
$token = $_REQUEST['token'];
include_once("session.php");


if (isset($_POST['getPaymentVerify'])) {
    $add = $userPayment->verifyPayment($_POST);
    if ($add) {
        if (($add['status'] == "OK") || ($add['status'] == "PENDING-OTP")) {
            if ($add['message'] == "complete") {
                header("location: ".URL."webView/cards/".$_REQUEST['token']."?done");
            }  else if (($add['message'] == "incomplete") || isset($add['fields'])) {
                header("location: ".URL."webView/cards/".$_REQUEST['token']."?id=".$add['card_id']."&fields=".urldecode($add['fields'])."&warning=".urldecode($add['additional_message']));
            } else {
                header("location: ".URL."webView/cards/".$_REQUEST['token']."?error=".urldecode($add['additional_message']));
            }
        } else if ($add['status'] == "PENDING-URL") {
            header("location: ".$add['authurl']);
        } else {
            header("location: ".URL."webView/cards/".$_REQUEST['token']."?error=".urldecode($add['message']));
        }
    }
} else if (isset($_POST['getPayment'])) {
    $add = $userPayment->postMew($_POST, true);
    if ($add) {
        if ($add['status'] == "OK") {
            if ($add['message'] == "complete") {
                header("location: ".URL."webView/cards/".$_REQUEST['token']."?done");
            } else {
                if ($add['fields'] != "url_excape") {
                    header("location: ".URL."webView/cards/".$_REQUEST['token']."?id=".$add['card_id']."&fields=".urldecode($add['fields'])."&warning=".urldecode($add['additional_message']));
                }
                header("location: ".URL."webView/cards/".$_REQUEST['token']."?id=".$add['card_id']."&fields=".urldecode($add['fields'])."&warning=".urldecode($add['additional_message']));
            }
        } else if ($add['status'] == "PENDING-URL") {
            header("location: ".$add['authurl']);
        } else {
            header("location: ".URL."webView/cards/".$_REQUEST['token']."?error=".urldecode($add['message']));
        }
    } else {
        header("location: ".URL."webView/cards/".$_REQUEST['token']."?error=".urldecode("Payment Card not added"));
    }
}

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Post and Find Jobs</title>
</head>

<body>
<div class="container-fluid">
    <div align="center">
    <?php if (isset($_REQUEST['done'])) { ?>
        <div class="alert alert-success" role="alert">
            <strong><?php echo $_REQUEST['done']; ?></strong>
        </div>
    <?php } ?>
    <?php if (isset($errorMessage)) { ?>
        <div class="alert alert-danger" role="alert">
            <strong><?php echo $errorMessage; ?></strong>
        </div>
    <?php } ?>
    <?php if (isset($_REQUEST['error'])) { ?>
        <div class="alert alert-danger" role="alert">
            <strong><?php echo $_REQUEST['error']; ?></strong>
        </div>
    <?php } ?>
    <?php if (isset($_REQUEST['warning'])) { ?>
        <div class="alert alert-warning" role="alert">
            <strong><?php echo $_REQUEST['warning']; ?></strong>
        </div>
    <?php } ?>
    <?php if (isset($warning)) { ?>
        <div class="alert alert-warning" role="alert">
            <strong><?php echo $warning; ?></strong>
        </div>
    <?php } ?>
    <?php if ((isset($_SESSION['users'])) && ($_SESSION['users']['status'] == "NEW")) {
        if ($users->listOnValue($_SESSION['users']['ref'], "status") == "NEW") { ?>
        <div class="alert alert-danger" role="alert">
            <strong>Your account is inactive, you will not be able to perform major functions on this site until you activate your account.<br>Click on the activation link in the Welcome E-Mail sent to you to activate this account</strong>
        </div>
        <?php } ?>
    <?php } else if ((isset($_SESSION['users'])) && ($_SESSION['users']['status'] == "INACTIVE")) { ?>
        <div class="alert alert-danger" role="alert">
            <strong>Your account has been deadivated.<br>Please contact us to resolve this issue</strong>
        </div>
    <?php } ?>
</div>
  <div class="row">
      <?php $userPayment->createNew(true); ?>
  </div>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>