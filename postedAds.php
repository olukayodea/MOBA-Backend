<?php
    $redirect = "ads";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if (isset($_REQUEST['view'])) {
        $view = trim($_REQUEST['view'], "/");
    } else {
        $view = "active";
    }
    if (isset($_REQUEST['report'])) {
        $rem = $request->updateOneRow("status", "PAUSED", $_REQUEST['id']);
        if ($rem) {
            header("location: ".URL.$redirect."/".$view."?done=".urlencode("This request has been reported and all activities has been paused"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("there was an error reporting this request"));
        }
    } else if (isset($_REQUEST['remove'])) {
        $rem = $request->removeDraft($_REQUEST['id']);

        if ($rem) {
            header("location: ".URL.$redirect."/".$view."?done=".urlencode("Open request cancelled removed successfully"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("Open request not cancelled successfully"));
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
    <title>Moba - Find the best artisans</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Requests</h6>
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
                    <?php $userHome->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $userHome->requestPageContentpublic($ref, $view, $redirect); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>