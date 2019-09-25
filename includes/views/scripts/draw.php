<?php
include_once("../../functions.php");
$id = $_REQUEST['id'];

$image_Url = $users->listOnValue($id, "image_url");
$screen_name = $users->listOnValue($id, "screen_name");

if (trim($image_Url) != "") { ?>
    <img class="<?php echo $class; ?> profile" src="<?php echo URL.$image_Url; ?>" alt="<?php echo $screen_name; ?>">
<?php } else { ?>
    <img class="<?php echo $class; ?> main_img profile" data-name="<?php echo $screen_name; ?>" alt="<?php echo $screen_name; ?>">
    <script src="<?php echo URL; ?>js/initial.js"></script>
<?php }
?>