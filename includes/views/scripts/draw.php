<?php
include_once("../../functions.php");
$id = $_REQUEST['id'];

$image_Url = $users->listOnValue($id, "image_url");
$screen_name = $users->listOnValue($id, "screen_name");
echo $users->getProfileImage($id, "main_img");

?>