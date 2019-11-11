<?php
    $redirect = "wallet";
    include_once("includes/functions.php");
    include_once("includes/sessions.php");
    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else if (isset($_POST['post'])) {
        $view = "confirm";
        $ref = $_POST;
    } else {
        $view = $_SESSION['location']['code'];
    }

    if (isset($_POST['postWallet'])) {
        $post = $userPayment->postWallet($_POST);
        if ($post['code'] == 1) {
            header("location: ".URL.$redirect."?done=".urldecode($post['message']).$and);
        } else {
            header("location: ".URL.$redirect."?error=".urldecode("Payment Error: ".$post['message']).$and);
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
    <title>Wallet</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Wallet</h6>
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
                    <?php $userPayment->navigationBarWallet($redirect, $_SESSION['users']['ref']); ?>
                </div>
                <div class="col-lg-9">
                    <?php $userPayment->pageContent($redirect, $view, $ref); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>