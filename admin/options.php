<?php
    $redirect = "admin/options";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");

    if (isset($_POST['submitOption'])) {
        unset($_POST['submitOption']);
        $adminOptions->postMew("service_charge", $_POST['service_charge']);
        $adminOptions->postMew("service_charge_premium", $_POST['service_charge_premium']);
        $adminOptions->postMew("max_days_approve", $_POST['max_days_approve']);
        $adminOptions->postMew("mail_interval", $_POST['mail_interval']);
        $adminOptions->postMew("result_per_page", $_POST['result_per_page']);
        $adminOptions->postMew("ad_per_page", $_POST['ad_per_page']);
        $adminOptions->postMew("search_per_page", $_POST['search_per_page']);
        $adminOptions->postMew("result_per_page_mobile", $_POST['result_per_page_mobile']);
        $adminOptions->postMew("ad_per_page_mobile", $_POST['ad_per_page_mobile']);
        $adminOptions->postMew("search_per_page_mobile", $_POST['search_per_page_mobile']);
        $adminOptions->postMew("product_ver", $_POST['product_ver']);
        $adminOptions->postMew("phone", $_POST['phone']);
        $adminOptions->postMew("email", $_POST['email']);
        $adminOptions->postMew("report_email", $_POST['report_email']);
        $tag = json_decode($_POST['text_filter'], true);
        $newTag = array();
        foreach($tag as $value) {
             $newTag[] = $value['value'];
        }
        unset($_POST['text_filter']);
        $_POST['text_filter'] = implode(",", $newTag);
        $adminOptions->postMew("text_filter", $_POST['text_filter']);

        header("location: ?done=".urlencode("Settings Saved"));

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
    <title>Administrator | Settings</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Administrator / Settings</h6>
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
                    <?php $adminOptions->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $adminOptions->pageContent(); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>