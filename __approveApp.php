<?php
include_once("includes/functions.php");
//get ad details
$array = $_REQUEST;
$run = $request->apiApprove($array);

if ($run['status'] == "200") {
    header("location: ".URL."requestDetails?id=".$array['post_id']."&done=".urlencode("This advert is now running and the service provider has been notified. You can click on this advert to monitor its progress"));
} else {
    header("location: ".$common->seo($array['post_id'], "profile")."/view?error=".urlencode("This advert can not be approved:".$run['additional_message']));
}
?>