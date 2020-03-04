<?php
    include_once("includes/functions.php");
    $pay_gateway['card'] = 7;
    $pay_gateway['gross_total'] = 1000;
    $pay_gateway['tx_id'] = rand(111,999);
    $makePayment = $wallet->processPay($pay_gateway);
?>