<?php
    $redirect = "admin/users.view";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");
    if (isset($_REQUEST['ref'])) {
        $user_id = $_REQUEST['ref'];
    } else {
        header("location: users");
    }

    $view = "oneView";

    if (isset($_POST['approve'])) {
        $add = $users->verify($_POST['ref'], 1);

        if ($add) {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification completed"));
        } else {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification not completed"));
        }
    } else if (isset($_POST['reject'])) {
        $add = $users->verify($_POST['ref'], 0);

        if ($add) {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification refused"));
        } else {
            header("location: ".URL.$redirect."?ref=".$_POST['ref']."&done=".urlencode("Account verification not completed"));
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
    <title>Administrator | Users and Administrators</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Administrator / Users and Administrators</h6>
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
                    <?php $adminUsers->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $adminUsers->pageContent($redirect, $view, $user_id); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>