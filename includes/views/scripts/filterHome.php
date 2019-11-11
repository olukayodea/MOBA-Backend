<?php
    include_once("../../functions.php");
	$term = $common->get_prep($_REQUEST['term']);
	
	$data = $search->keywordSearch($_SESSION['location'], $term);
	
	for ($i = 0; $i < count($data); $i++) {
		$row['value'] = $data[$i]['project_name'];
		$row['id'] = $common->seo( $data[$i]['ref'], "view" );
		$result[] = $row;
	}
	
	echo $raw = json_encode($result);
?>