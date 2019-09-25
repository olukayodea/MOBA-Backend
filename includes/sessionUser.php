<?php
	global $users;
	$urlData = explode("?", $_SERVER['REQUEST_URI']);
	//$_SESSION['users']['sessionTime'] = time() + 1800;
	if ((isset($_SESSION['users']['ref'])) && ($_SESSION['users']['status'] != "NEW")) {
		$last_name = trim($_SESSION['users']['last_name']);
		$other_names = trim($_SESSION['users']['other_names']);
		$screen_name = trim($_SESSION['users']['screen_name']);
		$email = trim($_SESSION['users']['email']);
        $ref = trim($_SESSION['users']['ref']);
        $verified = trim($_SESSION['users']['verified']);
        $sessionTime = trim($_SESSION['users']['sessionTime']);
		if ($users->checkExixst("users", "email", $email, "col") != $ref) {
            $users->logout();
			header("location: ".URL."login?redirect=".urldecode($redirect)."&error=please+login"."&".$urlData[1]);
		}

		if (($redirect == "ads") || ($redirect == "hire")) {
			$verified = $users->listOnValue($ref, "verified");
			if ($verified < 2) {
				if ($verified == 0) {
					$warning = "You must upload a government ID";
				} else if ($verified == 1) {
					$warning = "We are verifying your uploaded ID";
				}
			}
		}
	} else {
		header("location: ".URL."login?redirect=".urldecode($redirect)."&error=please+login"."&".$urlData[1]);
	}

?>