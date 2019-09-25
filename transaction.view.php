<?php
    $redirect = "transaction.view";
    include_once("includes/functions.php");
    include_once("includes/sessions.php");

    if (isset($_REQUEST['ref'])) {
        $tx_id = $_REQUEST['ref'];
    } else {
        header("location: transaction");
    }

    $view = "oneView";
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Transactions</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
  <?php $adminTransactions->pageContent($redirect, $view, $tx_id); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>