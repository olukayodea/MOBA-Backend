<?php
    $redirect = "paymentCards";
    include_once("includes/functions.php");
    include_once("includes/sessions.php");

    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "list";
    }

    if (isset($_POST['getPaymentVerify'])) {
        $add = $userPayment->verifyPayment($_POST);
        if ($add) {
            if (($add['status'] == "OK") || ($add['status'] == "PENDING-OTP")) {
                if ($add['message'] == "complete") {
                    header("location: ".URL.$redirect."?done=".urldecode("Payment Card Added"));
                }  else if (($add['message'] == "incomplete") || isset($add['fields'])) {
                    header("location: ".URL.$redirect."/".$view."/?id=".$add['card_id']."&fields=".urldecode($add['fields'])."&warning=".urldecode($add['additional_message']));
                } else {
                    header("location: ".URL.$redirect."/".$view."/?error=".urldecode($add['additional_message']));
                }
            } else {
                header("location: ".URL.$redirect."/".$view."/?error=".urldecode($add['message']));
            }
        }
    } else if (isset($_POST['getPayment'])) {
        $add = $userPayment->postMew($_POST);
        if ($add) {
            if ($add['status'] == "OK") {
                if ($add['message'] == "complete") {
                    header("location: ".URL.$redirect."?done=".urldecode("Payment Card Added"));
                } else {
                    if ($add['fields'] != "url_excape") {
                        header("location: ".URL.$redirect."/".$view."/?id=".$add['card_id']."&fields=".urldecode($add['fields'])."&warning=".urldecode($add['additional_message']));
                    }
                }
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
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <?php $pageHeader->headerFiles(); ?>
    <title>Manage Payment Cards</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Manage Payment Cards</h6>
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
                    <?php $userPayment->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $userPayment->pageContent($redirect, $view, $edit); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>