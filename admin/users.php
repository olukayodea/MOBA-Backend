<?php
    $redirect = "admin/users";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");
    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "users";
    }
    if (isset($_REQUEST['edit'])) {
        $edit = $_REQUEST['edit'];
        if ($users->toggleAdmin($edit)) {
            header("location: ".URL.$redirect."?done=".urlencode("Modification completed on User account"));
        } else {
            header("location: ?error=".urlencode("User account not modified"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminUsers->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."/".$view."?done=".urlencode("User Status changed"));
        } else {
            header("location: ".URL.$redirect."/".$view."?error=".urlencode("User Status not changed"));
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
                    <?php $adminUsers->pageContent($redirect, $view); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>