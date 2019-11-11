<?php
    $redirect = ltrim($_SERVER['REQUEST_URI'], "/");
    include_once("includes/functions.php");

    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $data = $category->listOne($id);
    } else {
        header("location: ".URL."allCategories");
    }
?>
<!DOCTYPE html>
<html lang="en">
<base href="<?php echo URL; ?>">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $userHome->headMeta($id); ?>

    <?php $pageHeader->headerFiles(); ?>
	</head>

  <body>
	<section>
	
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		<div class="moba-sban1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 py-5">
                            <h6><a href="<?php echo URL; ?>">Home</a> / <a href="<?php echo URL; ?>allCategories">All Categories</a>  /  <?php echo $data['category_title']; ?> </h6>
                    </div>
                    <div class="col-lg-6"></div>
                </div>
            </div>
		</div>
		
	</section>
	
	<section class="moba-details">
		<?php $userHome->pageContent("categoryHome", $id); ?>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>