<?php
	include_once("../../functions.php");
	$last_name = $common->get_prep($_REQUEST['last_name']);		
	$email = $common->get_prep($_REQUEST['email']);
	$tag = (html_entity_decode(($_REQUEST['tag'])));
	$other_names = $common->get_prep($_REQUEST['other_names']);
    
	$getname = explode(" ", $other_names);
	$token = base64_encode($other_names."+".$email)
?>
<html>
<head>
    <meta charset="utf-8">
<style type="text/css">
.title {
	font-family: Arial, Helvetica, sans-serif;
	padding: 5px;
	font-weight:bold;
	color: #FFFFFF;
	font-size: 16px;
}

.header {
    background: none repeat scroll 0% 0% #0E3F97;
    border-bottom: 3px solid #F0C237;
}

.title2 {
	font-family: Arial, Helvetica, sans-serif;
	padding: 5px;
	font-weight:bold;
	color: #000000;
	font-size: 14px;
}
.messege {
	font-family: Arial, Helvetica, sans-serif;
	padding: 5px;
	font-weight:bold;
	color: #000000;
	font-size: 12px;
}
.logoThumb{
	float:left;
	padding: 2px;
	margin: 3px;
	/*border: 1px solid #F0F0F0;*/
	text-align: center;
	vertical-align: middle;
}
.logoThumb img{border:0px}
body,td,th {
	font-family: tahoma;
	font-size: 11px;
	color: #FFFFFF;
}
.text {
	font-family: tahoma;
	font-size: 11px;
	color: #000000;
	padding: 5px;
}
</style>
<title><?php echo $common->get_prep($_REQUEST['subject']); ?></title>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="5">

  <tr>
    <td>
    <p class="text">Dear <?php echo ucwords(strtolower($getname[0])); ?>, </p>
    <p class="text">MOBA</p>
	<p class="text"><?php echo html_entity_decode($tag); ?></p>
  <span class="text">Regards,</span><br>
  <span class="text">MOBA</span><span class="text"></span></p>
      <p class="text">This email is intended for <strong><?php echo $last_name; ?> <?php echo $other_names; ?></strong>, please do not reply directly to this email. This email was sent from a notification-only address that cannot accept incoming email.</p>
<p class="text"><strong>Protect Your Password</strong><br>
Be alert to emails that request account information or urgent action.  Be cautious of websites with irregular addresses or those that offer unofficial payments to MOBA or other private accounts.<br>
</p></td>
  </tr>
  <tr>
    <td bgcolor="#009999">&copy; <?php echo date("Y"); ?> MOBA All Rights Reserved</td>
  </tr>
</table>

<div class="header">
</div>
</body>
</html>