<?php
    $redirect = "publicProfile";
    include_once("includes/functions.php");

    if (isset($_REQUEST['view'])) {
        if ($_REQUEST['view'] != "") {
            $view = $_REQUEST['view'];
        } else if ((isset($_SESSION['users']['ref'])) && ($_SESSION['users']['status'] != "NEW")) {
            $view = $_SESSION['users']['screen_name'];
        } else {
            header("location: ".URL);
        }
    } else {
        header("location: ".URL);
    }

    $data = $users->listOne($view, "screen_name");

    if (!$data) {
        header("location: ".URL."?error=".urlencode("User profile public page not found"));
    }
?>
<!DOCTYPE html>
<html lang="en">
<base href="<?php echo URL; ?>">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php $pageHeader->headerFiles(); ?>
    <title><?php echo $data['screen_name']."'s Profile"; ?></title>
	</head>

  <body>
	<section>
	
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		<div class="moba-sban1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 py-5">
                            <h6><a href="<?php echo URL; ?>">Home</a> / <?php echo $data['screen_name']."'s Profile"; ?></h6>
                    </div>
                    <div class="col-lg-6"></div>
                </div>
            </div>
		</div>
		
	</section>
	
	<section class="moba-details">
        <?php $userHome->pageContent($redirect, $view); ?>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>