<?php
include_once("includes/functions.php");
if (isset($_REQUEST['token'])) {
	$token = $common->get_prep($_REQUEST['token']);
	$token = base64_decode($token);
	$token = explode("+", $token);
	
	$name = $token[0];
	$email = $token[1];
	
	$modify = $users->modifyUser("status", "ACTIVE", $email, "email");
	
	if ($modify) {
		$users->logout();
        echo "<html>";
        echo "<head>";
        echo '<meta http-equiv="refresh" content="3; url=\''.URL.'\'">';
        echo "</head>";
        echo "<body>";
		echo "Thanks ".$name.", your accont is now active, you will now be redirected";
        echo "</body>";
        echo "</html>";
	} else {
		echo "you can not activate this account at this time";
	}
} else {
	echo "this is not a valid activation link";
}
?>