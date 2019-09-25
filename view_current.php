<?php
    $redirect = ltrim($_SERVER['REQUEST_URI'], "/");
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        
        $projects->getowner($id, "profile");
    } else {
        header("location: ./");
    }

    if (isset($_POST['log'])) {
        $add = $projects_data->updateDate($_POST);

        if ($_POST['log'] == "log_hours") {
            $msg = "Hours";
        } else if ($_POST['log'] == "log_mil_review") {
            $msg = "Review notification";
        } else {
            $msg = "Milestone";
        }
        if ($add) {
            header("location: ".$common->seo($id, "profile")."?done=".urldecode($msg." logged"));
        } else {
            header("location: ".$common->seo($id, "profile")."?error=".urldecode($msg." not logged"));
        }
    }

    if (isset($_POST['approve'])) {
        $add = $projects->approve($_POST['project_id'], "approve", true);

        if ($add) {
            header("location: ".$common->seo($id, "profile")."?done=".urldecode("Task marked as Complete"));
        } else {
            header("location: ".$common->seo($id, "profile")."?error=".urldecode("Could not mark this tax as complete"));
        }
    } else if (isset($_POST['request_approve'])) {
        $add = $projects->approve($_POST['project_id'], "request_approve", true);

        if ($add) {
            header("location: ".$common->seo($id, "profile")."?done=".urldecode("Review request sent successfully"));
        } else {
            header("location: ".$common->seo($id, "profile")."?error=".urldecode("Review request not sent successfully"));
        }
    } else if (isset($_POST['saveRate'])) {
        $add = $rating->addRate($_POST);
        if ($add) {
            header("location: ".$common->seo($id, "profile")."?done=".urldecode("Rating posted sent successfully"));
        } else {
            header("location: ".$common->seo($id, "profile")."?error=".urldecode("Rating not posted successfully"));
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
    <?php $userPostedAds->pageContent($id, "view_current", false); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>