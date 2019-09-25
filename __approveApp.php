<?php
include_once("includes/functions.php");
//get ad details
$array = $_REQUEST;
$run = $projects->authorizeProject($array);

if ($run['status'] == "COMPLETE") {
    header("location: ".URL."ads/on-going?done=".urlencode("This advert is now running. You can click on this advert to monitor its progress"));
} else {
    header("location: ".$common->seo($array['id'], "view")."?error=".urlencode("This advert can not be approved, there was a problem authorizing the transaction"));
}
?>