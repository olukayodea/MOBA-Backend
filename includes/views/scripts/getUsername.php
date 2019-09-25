<?php
    include_once("../../functions.php");
    $username = $common->get_prep($_REQUEST['username']);
    
    if ($database->checkExixst("users", "screen_name", $username) == 0) {
        $data['status'] = "OK";
        $data['message'] = "This username is available";
    } else {
        $data['status'] = "ERROR";
        $message = "This username is not available<br><br>Suggestion<br>";
        $message .= "<strong>".strtolower($users->confirmUnique($users->createUnique($username)))."</strong><br>";
        $data['message'] = $message;
    }
        
    header('Content-Type: application/json');
    echo json_encode($data);
?>