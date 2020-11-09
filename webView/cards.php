<?php
include_once("../includes/functions.php");
$token = $_REQUEST['token'];
include_once("session.php");

if ( (!isset($_REQUEST['done'])) && (!isset($_REQUEST['error'])) && (!isset($_REQUEST['warning']))) {
    header("location: ".URL."paystack/addCardMobile");
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
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>