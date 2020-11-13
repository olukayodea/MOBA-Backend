<?php
    $redirect = "paystackProcessor";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");


    if ((isset($_REQUEST['addCard'])) || (isset($_REQUEST['addCardMobile']))) {
        if (isset($_REQUEST['addCard'])) {
            $tag = "addCard";
        } else if (isset($_REQUEST['addCardMobile'])) {
            $tag = "addCardMobile";
        }
        $fields = [
        'email' => $_SESSION['users']['email'],
        'amount' => 50*100,
        'cartid' => $_SESSION['users']['ref']."_".time(),
        'callback_url' => URL."paystack/return/".$tag,
        'channels' => array("card")
        ];
    }


    $url = "https://api.paystack.co/transaction/initialize";
    $fields_string = http_build_query($fields);
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer sk_live_a2ed6378f889da4c4e80b2ebd86509d28755751e",
    "Cache-Control: no-cache",
    ));

    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

    //execute post
    $result = curl_exec($ch);

    $data = json_decode($result, true);

    if ($data['status']) {
        header("location: ".$data['data']['authorization_url']);
    } else {
        header("location: ".URL."paymentCards?done=".urldecode("Error saving payment card"));
    }
?>