<?php
    include_once("includes/functions.php");
    $response = json_decode($_REQUEST['response'], true);
    echo "<pre>";
    echo print_r($response);
    // $add = $wallet->validate3DSecure($response);

    // if (isset($_REQUEST['view']) && ($_REQUEST['view'] == "mobile")) {
    //     $url = URL."webView/cards";
    // } else {
    //     $url = URL."paymentCards";
    // }
    
    // if ($add) {
    //     if ($add['status'] == "OK") {
    //             header("location: ".$url."?done=".urldecode("Payment Card Added"));
    //     } else {
    //         header("location: ".$url."?error=".urldecode($add['message']));
    //     }
    // } else {
    //     header("location: ".$url."?error=".urldecode("Payment Card not added"));
    // }
?>