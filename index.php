<?php
    $redirect = "index";
	include_once("includes/functions.php");

	if (isset($_REQUEST['page_name'])) {
		if ($_REQUEST['page_name'] == "handle_request") {
			if (isset($_REQUEST['main_request'])) {
				$requestData = $this->listOne($_REQUEST['ID']);
				header("location: ".$common->seo($_REQUEST['ID'], "request", $requestData['user_id']));
			} else {
				header("location: ".URL."newRequestDetails?id=".$_REQUEST['ID']);
			}
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

    <title>Moba - Find the best artisans</title>
    <?php $pageHeader->headerFiles(); ?>
  </head>

  <body>
	<section>
		<?php $userHome->pageContent("homeSelect"); ?>
	</section>
	<section class="moba-works">
		<?php $userHome->pageContent("homeList"); ?>
	</section>
	<section class="moba-category">
		<div class="container">
			<h4>WHAT SERVICE DO YOU REQUIRE?</h4>
			<p>Select the category that best fits the service you require.</p>

			<div class="row">
				<?php $userHome->pageContent("category"); ?>
			</div>
		</div>
	</section>
	
<?php $pageHeader->footer(); ?>
<?php $pageHeader->jsFooter(); ?>
  </body>

</html>