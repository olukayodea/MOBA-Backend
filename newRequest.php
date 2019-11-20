<?php
    $redirect = "newRequest?id=".$_REQUEST['id'];
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $data = $category->listOne($id);
    } else {
        header("location: ".URL."allCategories");
    }

    if (isset($_POST['sendMessageButton'])) {
        $_POST['address'] = $_POST['city']." ".$_POST['state']." ".$_POST['postal_code']." ".$_POST['country'];
        unset($_POST['city']);
        unset($_POST['state']);
        unset($_POST['postal_code']);
        unset($_POST['country']);
        unset($_POST['autocomplete']);
        $regionData = $country->getLoc($_POST['country_code']);
        unset($_POST['country_code']);
        $_POST['region'] = $regionData['ref'];
        $_POST['user_id'] = $_SESSION['users']['ref'];
        $_POST['latitude'] = $_POST['lat'];
        $_POST['category_id'] = $_POST['id'];
        $_POST['longitude'] = $_POST['lng'];
        unset($_POST['id']);
        unset($_POST['lat']);
        unset($_POST['lng']);
        unset($_POST['sendMessageButton']);
        $_POST['media'] = $media->reArrayFiles($_FILES['uploadFile']);
        $_POST['web'] = true;

        $add = $request->create($_POST);
        if ($add['status'] == "ok") {
            header("location: ".URL."newRequestDetails?id=".$add['id']);
        } else {
            header("location: ".URL.$redirect."&error=".urldecode("There was an Error Performing this request. Reason: ".$add['msg']));
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<base href="<?php echo URL; ?>">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <?php $pageHeader->headerFiles(); ?>
    <?php $userHome->headMeta($id); ?>
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
		<?php $userHome->pageContent("categoryHome", $id, true); ?>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>