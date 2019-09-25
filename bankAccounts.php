<?php
    $redirect = "bankAccounts";
    include_once("includes/functions.php");
    include_once("includes/sessions.php");

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

    if (isset($_POST['getPayment'])) {
        unset($_POST['getPayment']);
        $add = $userBankAccount->postMew($_POST);
        if ($add) {
            header("location: ".URL.$redirect."?done=".urldecode("Bank Account Card Added"));
        } else {
            header("location: ".URL.$redirect."/".$view."/?error=".urldecode("Bank Account not added"));
        }
    } else if (isset($_POST['set_is_default'])) {
      $add = $userBankAccount->setDefault($_POST['is_default']);
      if ($add) {
          header("location: ".URL.$redirect."?done=".urlencode("Default Bank Account set"));
      } else {
          header("location: ?error=".urlencode("Default Bank Account not set"));
      }
  }
  if (isset($_REQUEST['delete'])) {
      $add = $userBankAccount->removeCard($_REQUEST['delete']);

      if ($add) {
        if ($add == "0000") {
            header("location: ".URL.$redirect."?error=".urlencode("Bank Account not removed"));
        } else {
            header("location: ".URL.$redirect."?done=".urlencode("Payment Card removed"));
        }
      } else {
          header("location: ".URL.$redirect."?error=".urlencode("Bank Account not removed"));
      }
  } else if (isset($_REQUEST['statusChange'])) {
      $add = $userBankAccount->toggleStatus($_REQUEST['statusChange']);

      if ($add) {
          header("location: ".URL.$redirect."?done=".urlencode("Bank Account Status changed"));
      } else {
          header("location: ".URL.$redirect."?error=".urlencode("Bank Account Status not changed"));
      }
  }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Manage Bank Account</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>

<div class="container-fluid">
<?php $userBankAccount->navigationBar($redirect); ?>
  <?php $userBankAccount->pageContent($redirect, $view, $edit); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>