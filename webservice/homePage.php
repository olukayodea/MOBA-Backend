<?php
    $nofollow = true;
    include_once("../includes/functions.php");
    $uth = explode(" ", apache_request_headers()['Authorization']);

    $data = file_get_contents('php://input');
    $header['key'] = $_SERVER['HTTP_KEY'];
    $header['ver'] = $_SERVER['HTTP_VER'];
    $header['longitude'] = $_SERVER['HTTP_LONGITUDE'];
    $header['latitude'] = $_SERVER['HTTP_LATITUDE'];
    $header['method'] = $_SERVER['REQUEST_METHOD'];
    $header['auth'] = trim($uth[1]);
    header("Access-Control-Allow-Headers: Authorization, Content-Type, longitude, latitude, ver, key, HTTP_KEY, HTTP_VER, HTTP_LONGITUDE, HTTP_LATITUDE");
    header('content-type: application/json; charset=utf-8');
    echo $api->prep($header, $_REQUEST['request'], $data);
    //https://play.google.com/apps/testing/com.MOBA.app
?>