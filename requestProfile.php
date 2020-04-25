<?php
$redirect = "requestProfile";
include_once("includes/functions.php");
include_once("includes/sessionUser.php");

if (isset($_REQUEST['view'])) {
    if ($_REQUEST['view'] != "") {
        $urlData = $common->splitURL($_REQUEST['view']);
        $user_id = $urlData[0];
        $request_id = $urlData[2];
        $view = $urlData[3];
        $requestData = $request->listOne($request_id);

        $r = $request->listOne($request_id);
        
        if (($r['status'] == "ACTIVE") && (($r['client_id'] == $ref) || ($r['user_id'] == $ref))) {
            header("location: ".URL."requestDetails?id=".$request_id);
        } else if (($r['status'] != "OPEN") && (($r['client_id'] == $ref) || ($r['user_id'] == $ref))) {
            header("location: ".URL."requestDetails?id=".$request_id);
        } else if ($usersCategory->findMe($ref, $r['category_id']) == 0) {
            header("location: ".URL."ads?error=".urldecode("You are not authorized to view this request"));
        } else if ($r['status'] != "OPEN") {
            header("location: ".URL."ads?error=".urldecode("The link you followed has expired"));
        }
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

if (isset($_POST['accept_req'])) {
    if ($request_accept->requestResponse($_POST)) {
        header("location: ".URL."ads?done=".urlencode("Job request Accepted, you will be notified if you selected for the job"));
    } else {
        header("location: ".URL."ads?error=".urlencode("An error occured, this request has expired"));
    }
} elseif(isset($_POST['reject_req'])) {
    header("location: ".URL."ads?done=".urlencode("Job request rejected"));
} elseif (isset($_POST['negotiate'])) {
    $add = $request_negotiate->negotiatePrize($_POST);

    if ($add) {
        header("location: ".$common->seo($_POST['user_r_id'], "profile").$_POST['post_id']."/message?done=".urlencode("Negotiation request sent"));
    } else {
        header("location: ".$common->seo($_POST['user_r_id'], "profile").$_POST['post_id']."/message?error=".urlencode("Negotiation request failed"));
    }
} elseif (isset($_REQUEST['cancel'])) {
    $add = $request_negotiate->remove($_REQUEST['cancel']);

    if ($add) {
        $messages->remove( $messages->findNegotiate(  $_REQUEST['cancel']  ) );
        header("location: ".$common->seo($user_id, "profile").$request_id."/message?done=".urlencode("Negotiation request cancelled"));
    } else {
        header("location: ".$common->seo($user_id, "profile").$request_id."/message?error=".urlencode("Negotiation request not cancelled"));
    }
} elseif (isset($_REQUEST['approve'])) {
    if ($_REQUEST['n_answer'] == "y") {
        $res_data['status'] = 2;
        $msg = "Approved";
    } else{
        $res_data['status'] = 1;
        $msg = "Declined";
    }

    $requestData = $request_negotiate->listOne($_REQUEST['approve']);
    $res_data['ref'] = $_REQUEST['approve'];
    $res_data['message'] = $messages->findNegotiate($_REQUEST['approve']);
    $add = $request_negotiate->negotiateResponse($res_data);
    
    if ($add) {
        header("location: ".$common->seo($requestData['user_id'], "profile").$requestData['post_id']."/message?done=".urlencode("Negotiation request ").$msg);
    } else {
        header("location: ".$common->seo($requestData['user_id'], "profile").$requestData['post_id']."/message?error=".urlencode("Negotiation response failed"));
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
                            <h6><a href="<?php echo URL; ?>">Home</a> / <a href="<?php echo URL; ?>allCategories">All Categories</a>  / <a href="<?php echo URL; ?>newRequestDetails?id=<?php echo $request_id; ?>"><?php echo $category->getSingle($requestData['category_id']); ?></a> / Service Provider Profile</h6>
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