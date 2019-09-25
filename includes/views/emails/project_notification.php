<?php
	include_once("../../functions.php");
	$last_name = $common->get_prep($_REQUEST['last_name']);		
    $other_names = $common->get_prep($_REQUEST['other_names']);
	$id = $common->get_prep($_REQUEST['id']);
	$getname = explode(" ", $other_names);
    
    $data = $projects->listOne($id);
    $getAlbum = $media->getAlbum($id);
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
    <?php if ($data['status'] == "ACTIVE") { ?>
    <p class="text">Congratulations, your ad has been posted.</p>
    <?php } ?>
    <table width="100%" border="0">
  <tr>
    <td class="text">Listed Category(s)</td>
    <td class="text"><?php echo $common->getTagFromWord($data['category_id'], "category", "blank"); ?></td>
    <td rowspan="10">
        <?php if (count($getAlbum) > 0) {
        for ($i = 0; $i < count($getAlbum); $i++) { ?>
            <img src="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:150px;">
        <?php }
        } else { ?>
            <img src="<?php echo $media->mediaDefault(); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:250px;">
        <?php } ?>

    </td>
  </tr>
  <tr>
    <td class="text">Ad Type</td>
    <td class="text"><?php echo $common->cleanText( $data['project_type'] ); ?></td>
  </tr>
  <tr>
    <td class="text">Billing Type</td>
    <td class="text"><?php echo $common->cleanText( $data['billing_type'] ); ?></td>
  </tr>
  <tr>
    <td class="text">Default Fee</td>
    <td class="text"><?php echo $country->getCountryData( $data['country'] )." ".number_format($data['default_fee'], 2);; ?></td>
  </tr>
  <tr>
    <td class="text">Status</td>
    <td class="text"><?php echo $data['status']; ?></td>
  </tr>
  <tr>
    <td class="text">Payment Status</td>
    <td class="text"><?php echo $common->cleanText( $data['payment_status'] ); ?></td>
  </tr>
  <tr>
    <td class="text">Created</td>
    <td class="text"><?php echo $data['create_time']; ?></td>
  </tr>
  <tr>
    <td class="text">Last Modified</td>
    <td class="text"><?php echo $data['modify_time']; ?></td>
  </tr>
  <tr>
    <td class="text">Tags</td>
    <td class="text"><?php echo $common->getTagFromWord($data['tag'], "tag", "blank"); ?></td>
  </tr>
</table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#009999">&copy; <?php echo date("Y"); ?> MOBA All Rights Reserved</td>
  </tr>
</table>

<div class="header">
</div>
</body>
</html>