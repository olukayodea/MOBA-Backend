<?php
    $redirect = "publicProfile";
    include_once("includes/functions.php");

    if (isset($_REQUEST['view'])) {
        if ($_REQUEST['view'] != "") {
            $view = $_REQUEST['view'];
        } else if ((isset($_SESSION['users']['ref'])) && ($_SESSION['users']['status'] != "NEW")) {
            $view = $_SESSION['users']['screen_name'];
        } else {
            header("location: ".URL);
        }
    } else {
        header("location: ".URL);
    }

    $data = $users->listOne($view, "screen_name");

    if (!$data) {
        header("location: ".URL."?error=".urlencode("User profile public page not found"));
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title><?php echo $view."'s Profile"; ?></title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $userHome->pageContent($redirect, $view); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>