<?php
    include_once("../../functions.php");
	$term = $common->get_prep($_REQUEST['term']);
	$countryCode = $country->getLoc($_SESSION['location']['code'])['ref'];
    $data = $search->category($countryCode, $term);
    	
	for ($i = 0; $i < count($data); $i++) {
		$row['value'] = $data[$i]['category_title'];
		$row['id'] = $common->seo( $data[$i]['ref'], "view" );
		$result[] = $row;
	}
	
	echo $raw = json_encode($result);
?>