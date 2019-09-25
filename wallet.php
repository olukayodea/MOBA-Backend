<?php
    $redirect = "wallet";
    include_once("includes/functions.php");
    include_once("includes/sessions.php");
    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else if (isset($_POST['post'])) {
        $view = "confirm";
        $ref = $_POST;
    } else {
        $view = $_SESSION['location']['code'];
    }

    if (isset($_POST['postWallet'])) {
        $post = $userPayment->postWallet($_POST);
        if ($post['code'] == 1) {
            header("location: ".URL.$redirect."?done=".urldecode($post['message']).$and);
        } else {
            header("location: ".URL.$redirect."?error=".urldecode("Payment Error: ".$post['message']).$and);
        }
    }

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Wallet</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $userPayment->navigationBarWallet($redirect, $_SESSION['users']['ref']); ?>
    <?php $userPayment->pageContent($redirect, $view, $ref); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>