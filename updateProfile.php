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
    } else if (isset($_POST['updateProfile'])) {
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
<!DOCTYPE html>
<html lang="en">
<base href="<?php echo URL; ?>">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php $pageHeader->headerFiles(); ?>
    <title><?php echo $screen_name."'s Profile"; ?></title>
	</head>

  <body>
	<section>
	
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		<div class="moba-sban1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 py-5">
                            <h6><a href="<?php echo URL; ?>">Home</a> / <?php echo $screen_name."'s Profile"; ?></h6>
                    </div>
                    <div class="col-lg-6"></div>
                </div>
            </div>
		</div>
		
	</section>
	
	<section class="moba-details">
        <?php $userHome->pageContent($redirect, false, $type); ?>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>