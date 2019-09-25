<?php
    include_once("includes/functions.php");
    if (isset($_REQUEST['social_media'])) {
        $data = base64_decode($_REQUEST['data']);
        $cleanData = explode("+", $data);

        $user_data = $users->listOne( $cleanData[2], "email");
        $tagLink = $cleanData[3];
        if ($user_data) {
            if ($users->autoLogin($user_data['email'], "email")) {
                $users->modifyUser("image_url", $cleanData[1], $cleanData[2], "email");
                $users->modifyUser("login_type", "social_media", $cleanData[2], "email");
                header("location: ".$tagLink);
            } else {
                header("location: ".URL."logout");
            }
        } else {
            header("location: ".URL."join?google&data=".$_REQUEST['data']);
        }
    }
?>