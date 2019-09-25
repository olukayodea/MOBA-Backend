<?php
    $redirect = "hire";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");


    if (isset($_REQUEST['project_type'])) {
        $project_type = $_REQUEST['project_type'];
    } else {
        $project_type = "client";
    }
    if (isset($_REQUEST['edit'])) {
        $edit = $_REQUEST['edit'];
    } else {
        $edit = 0;
    }
    
    if (isset($_POST['submitProject'])) {
        unset($_POST['country_code']);
        unset($_POST['submitProject']);
        $add = $userProject->postMew($_POST, $_FILES);
        if ($add) {
            header("location: ".URL."confirmHire?ref=".$add."&edit=".$edit);
        } else {
            header("location: ".URL.$redirect."/".$project_type."?error=".urlencode("Ad not created"));
        }
    } else if (isset($_GET['del'])) {
        $rem = $media->removeOne($_GET['del']);
        if ($rem) {
            header("location: ".URL.$redirect."/".$project_type."?edit=".$edit."&done=".urlencode("Image removed"));
        } else {
            header("location: ".URL.$redirect."/".$project_type."?edit=".$edit."&error=".urlencode("Image not removed"));
        }
    } else if (isset($_GET['relist'])) {
        $rem = $userProject->duplicate($_GET['relist']);
        
        if ($rem) {
            header("location: ".URL."confirmHire?ref=".$rem."&edit=".$edit);
        } else {
            header("location: ".URL."/ads/archive?error=".urlencode("Ad not duplicated"));
        }
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Post and Find Jobs</title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
  <div class="row">
      <?php $userProject->pageContent($redirect, $project_type, $edit); ?>
  </div>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>