<?php
$redirect = "requestDetails";
include_once("includes/functions.php");
include_once("includes/sessionUser.php");

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $data = $request->listOne($id);

    if ((($data['user_id'] != $_SESSION['users']['ref']) && ($data['client_id'] != $_SESSION['users']['ref'])) || ($data['user_id'] == "OPEN")) {
      header("location: ".URL."ads?error=".urlencode("you can not view this page"));
    }
} else {
    header("location: ".URL."/ads");
}


if (isset($_REQUEST['approve'])) {
  $add = $request->approve($_REQUEST['id'], "approve", $_SESSION['users']['ref']);

  if ($add) {
      header("location: ".URL."requestDetails?id=".$id."&done=".urldecode("Task marked as Complete"));
  } else {
      header("location: ".URL."requestDetails?id=".$id."&error=".urldecode("Could not mark this tax as complete"));
  }
} else if (isset($_REQUEST['request_approve'])) {
  $add = $request->approve($_REQUEST['id'], "request_approve", $_SESSION['users']['ref']);

  if ($add) {
      header("location: ".URL."requestDetails?id=".$id."&done=".urldecode("Review request sent successfully"));
  } else {
      header("location: ".URL."requestDetails?id=".$id."&error=".urldecode("Review request not sent successfully"));
  }
} else if (isset($_REQUEST['saveRate'])) {
  $add = $rating->addRate($_REQUEST);
  if ($add) {
      header("location: ".URL."requestDetails?id=".$id."&done=".urldecode("Rating posted sent successfully"));
  } else {
      header("location: ".URL."requestDetails?id=".$id."&error=".urldecode("Rating not posted successfully"));
  }
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