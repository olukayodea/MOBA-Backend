<?php
    $redirect = "ads";
    include_once("../includes/functions.php");
    include_once("../includes/sessionUser.php");

    if (isset($_REQUEST['view'])) {
        $view = trim($_REQUEST['view'], "/");
    } else {
        $view = "active";
    }
    if (isset($_REQUEST['remove'])) {
        $rem = $userPostedAds->removeDraft($_REQUEST['remove']);

        if ($rem) {
            header("location: ".URL.$redirect."/".$view."?done=".urlencode("Draft ad removed successfully"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("Draft ad not removed successfully"));
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
						<h6><a href="<?php echo URL; ?>">Moba</a> / Administrator  /  Requests</h6>
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
                    <?php $adminAdvert->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $adminAdvert->pageContent($ref, $view, $redirect); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>