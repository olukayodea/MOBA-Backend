<?php
    include_once("../../functions.php");
    
    $latitude = $_SESSION['location']['latitude'];
    $longitude = $_SESSION['location']['longitude'];

    $data = $projects->getMapHome($longitude, $latitude);
    
    header('Content-Type: application/json');
    echo json_encode($data);
?>