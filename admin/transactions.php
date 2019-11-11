<?php
    $redirect = "admin/transactions";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");

    
    if (isset($_REQUEST['view'])) {
      $view = $_REQUEST['view'];
    } else {
        $view = "all";
    }
    if (isset($_REQUEST['sort'])) {
      $sort = $_REQUEST['sort'];
    } else {
      $sort = $_SESSION['location']['code'];
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
    <title>Administrator | All Transactions</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Administrator / All Transactions</h6>
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
                    <?php $adminTransactions->navigationBar($redirect, $view, $sort); ?>
                </div>
                <div class="col-lg-9">
                    <?php $adminTransactions->pageContent($redirect, $view, $sort); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>