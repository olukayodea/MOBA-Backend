<?php
    $redirect = "paystackProcessor";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");

    $data = $wallet->verifyTx($_REQUEST['reference']);
    if ((isset($_REQUEST['addCard'])) || (isset($_REQUEST['addCardMobile']))) {
        if ($data['status']) {
            if (($data['data']['status'] = "success") && ($data['data']['gateway_response'] = "Successful")) {
                $wallet->refund($data['data']['reference'],$data['data']['amount']);

                $userData = $users->listOne($data['data']['customer']['email'], "email");
                if ($userData) {
                    $card['pan'] = $data['data']['authorization']['last4'];
                    $card['gateway_token'] = $data['data']['authorization']['authorization_code'];
                    $card['expiry_month'] = $data['data']['authorization']['exp_month'];
                    $card['expiry_year'] = $data['data']['authorization']['exp_year'];
                    $card['card_name'] = $data['data']['authorization']['card_type'];
                    $card['user_id'] = $userData['ref'];

                    $card['user_id'] = $userData['ref'];

                    if ($wallet->create($card)) {
                        if (isset($_REQUEST['addCard'])) {
                            header("location: ".URL."paymentCards?done=".urldecode("Payment card saved"));
                        } else if (isset($_REQUEST['addCardMobile'])) {
                            header("location: ".URL."cards/?done=".urldecode("Payment card saved ".$data['message']));
                        }
                    }
                }
            }
        } else {
            if (isset($_REQUEST['addCard'])) {
                header("location: ".URL."paymentCards?error=".urldecode("Error saving payment card"));
            } else if (isset($_REQUEST['addCardMobile'])) {
                header("location: ".URL."cards/?error=".urldecode("Error saving payment card ".$data['message']));
            }
        }
    }


?>