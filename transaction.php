<?php
    $redirect = "transaction";
    include_once("includes/functions.php");
    include_once("includes/sessions.php");
    
    if (isset($_REQUEST['view'])) {
      $view = $_REQUEST['view'];
    } else {
        $view = "all";
    }

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | All Transactions</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<?php $adminTransactions->navigationBar($redirect, $view, $ref, "user"); ?>
<div class="container-fluid">
  <?php $adminTransactions->pageContent($redirect, $view, $ref); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>