<?php
    $redirect = "ads";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if (isset($_REQUEST['ref'])) {
        $project_id = base64_decode(trim($_REQUEST['ref'], "/"));
    } else {
        header("location: ./?error=".urlencode("No Ad payment to approve at this monment"));
    }

    $data = $projects->listOne($project_id);
    if (($data['payment_status'] != "2") && ($data['project_type'] == "client")) {
        header("location: payments/Authorization/".base64_encode($project_id)."?error=".urlencode("We need to confirm your Credit Cards details to continue"));
    }

    

    if (isset($_POST['approveProject'])) {
        $userProject->approveProject($_POST['project_id']);
        header("location: ".URL."ads?done=".urlencode("Ad posted successfully"));
    }
?>

<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Confirmation</title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
      <?php $userProject->pageContent($redirect, "confirmation", $project_id); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>