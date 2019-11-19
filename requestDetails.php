<?php
$redirect = "requestDetails";
include_once("includes/functions.php");
include_once("includes/sessionUser.php");

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
} else {
    header("location: ".URL."/ads");
}

?>
<!DOCTYPE html>
<html lang="en">
<base href="<?php echo URL; ?>">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php $pageHeader->headerFiles(); ?>
    <?php $userHome->headMeta($requestData['category_id']); ?>
	</head>

  <body>
	<section>
	
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		<div class="moba-sban1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 py-5">
                            <h6><a href="<?php echo URL; ?>">Home</a> / <a href="<?php echo URL; ?>allCategories">All Categories</a>  / <?php echo $category->getSingle($data['category_id']); ?></h6>
                    </div>
                    <div class="col-lg-6"></div>
                </div>
            </div>
		</div>
		
	</section>
	
	<section class="moba-details">
		<?php $userHome->pageContent("requestDetails", $id); ?>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>