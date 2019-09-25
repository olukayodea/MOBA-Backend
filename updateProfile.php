<?php
    $redirect = "updateProfile";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    if ((isset($_REQUEST['type'])) && ($_REQUEST['type'] != "")) {
        $type = $_REQUEST['type'];
    } else {
        $type = "profile";
    }
    if (isset($_GET['removeImage'])) {
        if ($users->removeProfilePicture($ref)) {
            header("location: ?done=".urldecode("Profile Picture removed"));
        } else {
            header("location: ?error=".urldecode("Profile Picture not removed"));
        }
    } else if (isset($_POST['updateUsername'])) {
        unset($_POST['updateUsername']);
        $update = $userHome->updateScreenName($_POST);
        if ($update) {
            header("location: ?done=".urldecode("Profile updated"));
        } else {
            header("location: ?error=".urldecode("Profile not updated"));
        }
    } else if (isset($_POST['updateScreenName'])) {
        unset($_POST['updateProfile']);
        $update = $userHome->updateProfile($_POST);
        if ($update) {
            header("location: ?done=".urldecode("Profile updated"));
        } else {
            header("location: ?error=".urldecode("Profile not updated"));
        }
    } else if (isset($_POST['updatePassword'])) {
        unset($_POST['updatePassword']);
        $update = $userHome->updatePassword($_POST);
        if ($update) {
            if ($update === "invalid") {
                header("location: ?error=".urldecode("Invalid current password. Password not updated"));
            } else {
                header("location: ?done=".urldecode("Password updated"));
            }
        } else {
            header("location: ?error=".urldecode("Password not updated"));
        }
    } 
    
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title><?php echo $screen_name."'s Profile"; ?></title>
</head>

<body>
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div class="container-fluid">
    <?php $userHome->pageContent($redirect, false, $type); ?>
</div>
<?php $pageHeader->jsFooter(); ?>
</body>
</html>