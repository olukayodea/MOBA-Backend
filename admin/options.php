<?php
    $redirect = "admin/options";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");

    if (isset($_POST['submitOption'])) {
        unset($_POST['submitOption']);
        $adminOptions->postMew("service_charge", $_POST['service_charge']);
        $adminOptions->postMew("service_charge_premium", $_POST['service_charge_premium']);
        $adminOptions->postMew("minimum_post", $_POST['minimum_post']);
        $adminOptions->postMew("max_days_approve", $_POST['max_days_approve']);
        $adminOptions->postMew("mail_interval", $_POST['mail_interval']);
        $adminOptions->postMew("result_per_page", $_POST['result_per_page']);
        $adminOptions->postMew("ad_per_page", $_POST['ad_per_page']);
        $adminOptions->postMew("search_per_page", $_POST['search_per_page']);
        $adminOptions->postMew("result_per_page_mobile", $_POST['result_per_page_mobile']);
        $adminOptions->postMew("ad_per_page_mobile", $_POST['ad_per_page_mobile']);
        $adminOptions->postMew("search_per_page_mobile", $_POST['search_per_page_mobile']);
        $adminOptions->postMew("product_ver", $_POST['product_ver']);
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
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | System Settings</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $adminOptions->pageContent(); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>