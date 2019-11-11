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
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <?php $pageHeader->headerFiles(); ?>
    <title>Manage Bank Account</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Manage Bank Account</h6>
				</div>
				<div class="col-lg-6"></div>
				
			</div>
		</div>
		</div>
		
	</section>
	<section class="moba-details">
		<div class="container my-5">
            <div class="row py-5">
                <div class="col-lg-3">
                    <?php $userBankAccount->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $userBankAccount->pageContent($redirect, $view, $edit); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>