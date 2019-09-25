<?php
    $redirect = "index";
    include_once("includes/functions.php");
?>
<html>
<head>
  <title>MOBA</title>
<?php $pageHeader->headerFiles(); ?>
  <style>
    .map {height: 50%;}
    html, body {height: 100%; margin: 0; padding: 0;}  
  </style>
<head>
<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<?php $pageHeader->selector(); ?>
<?php $userHome->drawMap(); ?>
<div class="container-fluid">
    <?php $userHome->pageContent($redirect); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>
