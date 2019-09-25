<?php
    $redirect = "admin\accuntVerification";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");

    if (isset($_REQUEST['delete'])) {
        $add = $userPayment->removeCard($_REQUEST['delete']);
  
        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Payment Card removed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Payment card not removed"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Account Verification</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
  <?php $adminUsers->pageContent($redirect, "verification"); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>