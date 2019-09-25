<?php
    include_once("includes/functions.php");
    if (isset($_REQUEST['project'])) {
        $project_id = $_REQUEST['project'];
    } else {
        header("location: ./");
    }
    if (isset($_REQUEST['type'])) {
        $type = $_REQUEST['type'];
    } else {
        header("location: ./");
    }
    if (isset($_POST['setup_hours'])) {
        $add = $userPostedAds->processHours($_POST);
        if (isset($_POST['viewResponse'])) {
            $and = "&viewResponse=".$_POST['viewResponse'];
        } else {
            $and = "";
        }
        if ($add) {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Duration defined, waiting for approval").$and);
        } else {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Duration not defined").$and);
        }
    } else if (isset($_POST['approve_tx'])) {
        unset($_POST['type']);
        unset($_POST['approve_tx']);
        unset($_POST['project']);
        if ($type == "featured_ad") {
            $num_days = $_POST['num_days'];
            unset($_POST['num_days']);
        }
        
        if (isset($_POST['viewResponse'])) {
            $and = "&viewResponse=".$_POST['viewResponse'];
        } else {
            $and = "";
        }
        $post = $userPostedAds->postTransaction($_POST);
        if ($post['code'] == 1) {
            if ($type == "featured_ad") {
                $projects->setFeatured($project_id, $num_days);

                header("location: ".$common->seo($project_id, "view")."?done=".urldecode($post['message']).$and);
            }
        } else {
            if ($type == "featured_ad") {
                header("location: ".$common->seo($project_id, "view")."?error=".urldecode("Payment Error: ".$post['message']).$and);
            }
        }
    } else if (isset($_POST['SubmitProposal'])) {
        $add = $userPostedAds->processMilestone($_POST);
        if (isset($_POST['viewResponse'])) {
            $and = "&viewResponse=".$_POST['viewResponse'];
        } else {
            $and = "";
        }
        if ($add) {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Milestone defined, waiting for approval").$and);
        } else {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Milestone not defined").$and);
        }

    } else if (isset($_POST['rj_h_proposal'])) {
        $add = $userPostedAds->rejectMilestone($_POST, "duration");
        if (isset($_POST['viewResponse'])) {
            $and = "&viewResponse=".$_POST['viewResponse'];
        } else {
            $and = "";
        }
        if ($add) {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Duration Proposal rejected").$and);
        } else {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("There was an error while rejecting the Duration proposal").$and);
        }
    } else if (isset($_POST['rj_proposal'])) {
        $add = $userPostedAds->rejectMilestone($_POST);
        if (isset($_POST['viewResponse'])) {
            $and = "&viewResponse=".$_POST['viewResponse'];
        } else {
            $and = "";
        }
        if ($add) {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Milestone Proposal rejected").$and);
        } else {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("There was an error while rejecting the Milestone proposal").$and);
        }
    } else if (isset($_REQUEST['ap_proposal'])) {
        $array['project'] = $_REQUEST['project'];
        $array['ref'] = $_REQUEST['ref'];

        if (isset($_REQUEST['viewResponse'])) {
            $and = "&viewResponse=".$_REQUEST['viewResponse'];
        } else {
            $and = "";
        }
        
        $add = $userPostedAds->approveMilestone($array);
        
        if ($add) {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Milestone Proposal Approved").$and);
        } else {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("There was an error while approving the Milestone proposal").$and);
        }
    } else if (isset($_REQUEST['ap_h_proposal'])) {
        $array['project'] = $_REQUEST['project'];
        $array['ref'] = $_REQUEST['ref'];

        if (isset($_REQUEST['viewResponse'])) {
            $and = "&viewResponse=".$_REQUEST['viewResponse'];
        } else {
            $and = "";
        }
        
        $add = $userPostedAds->approveMilestone($array, "duration");
        
        if ($add) {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("Duration Proposal Approved").$and);
        } else {
            header("location: ".$common->seo($project_id, "view")."?done=".urldecode("There was an error while approving the Duration proposal").$and);
        }
    }

    if (($type == "review_milstone") || ($type == "review_hours")) {
        $_POST['project'] = $_REQUEST['project'];
        $_POST['user_r'] = $_REQUEST['user_r'];
        $_POST['user'] = $_REQUEST['user'];
        $_POST['type'] = $_REQUEST['type'];
        if (isset($_REQUEST['viewResponse'])) {
            $_POST['viewResponse'] = $_REQUEST['viewResponse'];
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Request Confirmation</title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $userPostedAds->pageContent($project_id, $type, false, $_POST); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>