<?php
    $redirect = "requestProfile";
    include_once("includes/functions.php");

    if (isset($_REQUEST['view'])) {
        if ($_REQUEST['view'] != "") {
            $urlData = $common->splitURL($_REQUEST['view']);
            $user_id = $urlData[0];
            $request_id = $urlData[2];
            $view = $urlData[3];
            $requestData = $request->listOne($request_id);
        } else {
            header("location: ".URL);
        }
    } else {
        header("location: ".URL);
    }

    $data = $users->listOne($user_id);

    if (!$data) {
        header("location: ".URL."?error=".urlencode("User profile public page not found"));
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title><?php echo $data['screen_name']."'s Profile"; ?></title>
</head>

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
                            <h6><a href="<?php echo URL; ?>">Home</a> / <a href="<?php echo URL; ?>allCategories">All Categories</a>  /  <a href="<?php echo $common->seo($requestData['category_id'], "category"); ?>"><?php echo $category->getSingle($requestData['category_id']); ?></a> / <a href="<?php echo URL; ?>newRequestDetails?id=<?php echo $request_id; ?>">Request</a> / Service Provider Profile</h6>
                    </div>
                    <div class="col-lg-6"></div>
                </div>
            </div>
		</div>
		
	</section>
	
	<section class="moba-details">
		<?php $userHome->pageContent("requestProfile", $urlData); ?>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>