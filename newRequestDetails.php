<?php
    $redirect = "newRequestDetails?id=".$_REQUEST['id'];
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $requestData = $request->listOne($id);
        $data = $category->listOne($requestData['category_id']);

        if ($requestData['user_id'] != $_SESSION['users']['ref']) {
          header("location: ".URL."ads?error=".urlencode("you can not view this page"));
        }
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

    <title>Moba - Find the best artisans</title>
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
		<?php $userHome->pageContent("categoryList", $id, true); ?>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>