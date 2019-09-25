<?php
    $redirect = "admin/adverts";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");

    if (isset($_REQUEST['view'])) {
      $view = trim($_REQUEST['view'], "/");
    } else {
      $view = "active";
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Administrator | Posted Ads</title>
</head>

<body>
<?php $pageHeader->loginStrip(); ?>
<?php $pageHeader->navigation(); ?>
<?php $adminAdvert->navigationBar($redirect); ?>
<div class="container-fluid">
  <?php $adminAdvert->pageContent($view, $redirect); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>