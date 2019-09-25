<?php
    $redirect = ltrim($_SERVER['REQUEST_URI'], "/");
    include_once("includes/functions.php");

    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $projects->getowner($id, "active");
    } else {
        header("location: ./");
    }

    if (isset($_POST['negotiate'])) {
        if (isset($_REQUEST['viewResponse'])) {
            $and = "&viewResponse=".$_REQUEST['viewResponse'];
        }
        unset($_POST['viewResponse']);
        $add = $userPostedAds->negotiatePrize($_POST);

        if ($add) {
            header("location: ".$common->seo($id, "view")."?done=".urlencode("Negotiation request sent").$and);
        } else {
            header("location: ".$common->seo($id, "view")."?error=".urlencode("Negotiation request failed").$and);
        }
    }
    if (isset($_REQUEST['n_answer'])) {
        if (isset($_REQUEST['viewResponse'])) {
            $and = "&viewResponse=".$_REQUEST['viewResponse'];
        }
        if ($_REQUEST['n_answer'] == "y") {
            $data['status'] = 2;
            $msg = "Approved";
        } else{
            $data['status'] = 1;
            $msg = "Declined";
        }
        $data['ref'] = $_REQUEST['neg_id'];
        $data['message'] = $_REQUEST['msg_id'];
        $add = $userPostedAds->negotiateResponse($data);

        if ($add) {
            header("location: ".$common->seo($id, "view")."?done=".urlencode("Negotiation request ".$msg).$and);
        } else {
            header("location: ".$common->seo($id, "view")."?error=".urlencode("Negotiation request failed").$and);
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $userPostedAds->headMeta($id); ?>
<?php $pageHeader->headerFiles(); ?>
<style>
	*	{
		margin:0px;
		padding:0px;
	}
	ol#update {
		list-style:none;
	}
</style>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $userPostedAds->pageContent($id, "view_page", false); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>