<?php
    $redirect = "admin/rating";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");


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
    
    if (isset($_POST['submitRating'])) {
        unset($_POST['submitRating']);
        $add = $adminRating->postMew($_POST);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Rating Question created"));
        } else {
            header("location: ?error=".urlencode("Rating Question not created"));
        }
    }
    if (isset($_REQUEST['delete'])) {
        $add = $adminRating->removeRating($_REQUEST['delete']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Rating Question Removed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Rating Question not Removed"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminRating->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Rating Question Status changed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Rating Question Status not changed"));
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
    <title>Administrator | Rating Questions</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Administrator / Rating Questions</h6>
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
                    <?php $adminRating->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $adminRating->pageContent($redirect, $view, $edit); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>