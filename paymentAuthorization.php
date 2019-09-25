<?php
    $redirect = "ads";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    //echo base64_encode(merchID.":".gateway_access_code);

    if (isset($_REQUEST['ref'])) {
        $project_id = base64_decode(trim($_REQUEST['ref'], "/"));
    } else {
        header("location: ./?error=".urlencode("No Ad payment to approve at this monment"));
    }

    if (isset($_REQUEST['auth_card'])) {
        $update = $userProject->savePayment($project_id);

        if ($update) {
            header("location: ".URL."confirmation/".base64_encode($project_id));
        } else {
            header("location: ".URL."payments/Authorization/".base64_encode($project_id)."?error=".urldecode("There was an error processing your card"));
        }
    } else if (isset($_POST['getPayment'])) {
        $add = $userPayment->postMew($_POST);
        if ($add) {
          if ($add['status'] == "OK") {
              header("location: ?auth_card");
          } else {
              header("location: ?error=".urldecode($add['message']));
          }
        } else {
            header("location: ?error=".urldecode("Payment Card not added"));
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
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
  <div class="row">
      <?php $userProject->pageContent($redirect, "continuation", $project_id); ?>
  </div>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>