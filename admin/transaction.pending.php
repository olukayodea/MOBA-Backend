<?php
    $redirect = "admin/transaction.pending";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");
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
  <?php $adminPayments->pageContent(); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>