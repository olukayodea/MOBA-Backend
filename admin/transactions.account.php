<?php
    $redirect = "admin/transactions.account";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");

    if (isset($_REQUEST['ref'])) {
        $card_id = $_REQUEST['ref'];
    } else {
        header("location: cards");
    }

    $view = "oneView";
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Payment Cards</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
  <?php $adminAccounts->pageContent($redirect, $view, $card_id); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>