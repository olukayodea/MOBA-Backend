<?php
    echo "<pre>";
    $product_key = rand();
    //$u = "http://127.0.0.1/MOBA-Backend/";
    //$token = "1122SJB1TQKBXKKJP2N";
    $u = "https://dev.moba.com.ng/";
    $token = "11241RCL8B298BW0MHP";
    $gateway_passcode = base64_encode($product_key."_".$token);

    //common factors
    $header[] = "Content-Type: application/json";
    $header[] = "Authorization: Bearer ".$gateway_passcode;
	$header[] ='key: '.$product_key;
	$header[] ='ver: 1';
	$header[] ='longitude: 3.349149';
    $header[] ='latitude: 6.605874';
/*
    //data
    $url = $u."api/data";
    echo get($header, $url);*/
/*
    //login
    //type = "local", "social_media";
    $array['email'] = "olukayode.adebiyi@hotmail.co.uk";
    $array['password'] = "Password@1";
    $array['type'] = "local";
    $array['account_type_token'] = "";
    $array['firebase_token'] = "12334456543456544";

    $url = $u."api/users/login";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //register
    //type = "local", "social_media";
    //user_type = "0 for users", "1 for service provider", "2 for service admin";
    $array['user_type'] = 2;
    $array['last_name'] = "Doe";
    $array['other_names'] = "John";
    $array['screen_name'] = "demo";
    $array['email'] = "olukayode.adebiyi@hotmail.co.uk";
    $array['password'] = "Password@1";
    $array['account_type'] = "local";
    $array['account_type_token'] = "";
    $array['firebase_token'] = "12334456543456544";
    //service provider
    $array['mobile_number'] = "08182441752";
    $array['street'] = "Issuti road";
    $array['city'] = "Igando";
    $array['state'] = "Lagos";
    $array['category'] = "1,2,3,4,5,6,7";
    $array['id_type'] = "Kunzu";
    $array['id_expiry'] = "Kunzu";
    $array['id_number'] = "Kunzu";
    $array['photo_file'] = "Kunzu";
    $array['id_file'] = "Kunzu";
    $array['kin_name'] = "Kunzu";
    $array['kin_email'] = "Kunzu";
    $array['kin_phone'] = "Kunzu";
    $array['kin_relationship'] = "Kunzu";
print_r($array);
    $url = $u."api/users/register";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //recover
    $url = $u."api/users/recover/".urlencode("olukayode.adebiyi@hotmail.co.uk");
    echo get($header, $url); */
/*
    //category::listparent
    $url = $u."api/category/listParent";
    echo get($header, $url);*/
/*
    //category::listSub
    $url = $u."api/category/listSub/2";
    echo get($header, $url);*/
/*
    //category::list
    $url = $u."api/category/list";
    echo get($header, $url);*/
/*
    //advert::category
    $url = $u."api/category/advert/2/1";
    echo get($header, $url);*/
/*
    //advert::search
    $url = $u."api/advert/search/a/1";
    echo get($header, $url);*/
/*
    //advert::search
    $url = $u."api/advert/keywordSearch/ottawa/1";
    echo get($header, $url);*/
/*
    //advert::featured
    $url = $u."api/advert/list/featured";
    echo get($header, $url);*/
/*
    //advert::maps
    $url = $u."api/advert/list/map";
    echo get($header, $url);*/
/*
    //advert::aroundme
    $url = $u."api/advert/list/aroundMe/1";
    echo get($header, $url);*/
/*
    //advert::recentt
    $url = $u."api/advert/list/recent/1";
    echo get($header, $url);*/
/*
    //bank accounts
    //get list
    $url = $u."api/banks/list";
    echo get($header, $url);*/
/*    
    //add
    $array['last_name'] = "Adeniran";
    $array['first_name'] = "Olakunle";
    $array['bank'] = "2";
    $array['account_number'] = "1003766227";

    $url = $u."api/account/add/";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/

/*
    //edit
    $array['last_name'] = "Adeniran";
    $array['first_name'] = "Olakunle";
    $array['bank'] = "2";
    $array['account_number'] = "1003766227";
    $array['ref'] = 1;

    $url = $u."api/account/edit/";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*    
    //get
    $url = $u."api/account/get/1";
    echo get($header, $url);*/
/*
    //get default
    $url = $u."api/account/get/default";
    echo get($header, $url);*/
/*
    //list
    $url = $u."api/account/get/all/1";
    echo get($header, $url);*/
/*
    //make default
    $array['ref'] = 3;
    $url = $u."api/account/makeDefault";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //remove
    $url = $u."api/account/delete/2";
    $json_data = json_encode($array);
    echo delete($header, $url, $json_data);*/
/*
    //change status
    $array['ref'] = 1;
    $url = $u."api/account/changeStatus";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //payment cards
    //add
    $array['cc_last_name'] = "Adeniran";
    $array['cc_first_name'] = "Olakunle";
    $array['cardno'] = "4504481742333";
    $array['mm'] = "02";
    $array['yy'] = "20";
    $array['cvv'] = "123";

    $url = $u."api/cards/add/";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //get
    $url = $u."api/cards/get/1";
    echo get($header, $url);*/
/*
    //get default
    $url = $u."api/cards/get/default";
    echo get($header, $url);*/
/*
    //list
    $url = $u."api/cards/get/all/1";
    echo get($header, $url); */
/*
    //make default
    $array['ref'] = 1;
    $url = $u."api/cards/makeDefault";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //remove
    $url = $u."api/cards/delete/7";
    $json_data = json_encode($array);
    echo delete($header, $url, $json_data);*/
/*
    //change status
    $array['ref'] = 1;
    $url = $u."api/cards/changeStatus";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //transactions
    //get
    $url = $u."api/transaction/get/1";
    echo get($header, $url);*/
/*
    //list
    $url = $u."api/transaction/get/all/3";
    echo get($header, $url);*/
 /*
    //wallet
    //add
    $array['card'] = 1;
    $array['amount'] = 1000;

    $url = $u."api/wallet/deposit";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/

    //remove
    //$array['card'] = 1;
    $array['account'] = 1;
    $array['amount'] = 390;

    $url = $u."api/wallet/withdraw";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);
/*
    //get
    $url = $u."api/wallet/get/52";
    echo get($header, $url);*/
/*
    //list
    $url = $u."api/wallet/get/all";
    echo get($header, $url);*/
/*
    //balance
    $url = $u."api/wallet/get/balance";
    echo get($header, $url);*/
/*
    //users
    //get:contact
    $url = $u."api/users/get/contact/fai";
    echo get($header, $url); */
/*
    //get profile
    $url = $u."api/users/profile";
    echo get($header, $url);*/
/*
    //edit profile
    $array['last_name'] = "Adebiyi";
    $array['other_names'] = "Olukayode";
    $array['screen_name'] = "Kay";

    $url = $u."api/users/profile";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //edit password
    $array['old_password'] = "Olukayode";
    $array['new_password'] = "Password@1";

    $url = $u."api/users/password";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //edit profilePictue
    $array['file'] = base64_encode(file_get_contents($u."media/3086af664b539308321cf4adf2b49049ac21ed2b/561564329078709.jpg"));

    $url = $u."api/users/profilePicture";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //edit gov_id
    $array['file'] = base64_encode(file_get_contents($u."media/3086af664b539308321cf4adf2b49049ac21ed2b/561564329078709.jpg"));

    $url = $u."api/users/gov_id";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //Jobs
    //post jpb
    $array['project_type'] = "client"; //client or vendor
    $array['project_name'] = "Sample Demo";
    $array['project_dec'] = "Sample";
    $array['allow_remote'] = 1;
    $array['category_id'][] = 6;
    $array['category_id'][] = 8;
    $array['category_id'][] = 7;
    $array['category_id'][] = 2;
    $array['address'] = "1541 Riverside Drive, Ottawa, ON, Canada";
    $array['billing_type'] = "per_hour";
    $array['default_fee'] = "25";
    $array['card'] = 1; //if project_type is client
    $array['tag'] = "Sample, Samples, another one";
    $array['image'][] = base64_encode(file_get_contents($u."media/3086af664b539308321cf4adf2b49049ac21ed2b/561564329078709.jpg"));
    $array['image'][] = base64_encode(file_get_contents($u."media/3086af664b539308321cf4adf2b49049ac21ed2b/561564329078709.jpg"));
    $array['image'][] = base64_encode(file_get_contents($u."media/3086af664b539308321cf4adf2b49049ac21ed2b/561564329078709.jpg"));
    $array['image'][] = base64_encode(file_get_contents($u."media/3086af664b539308321cf4adf2b49049ac21ed2b/561564329078709.jpg"));

    $url = $u."api/advert/post";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //edit jpb
    $array['ref'] = 34;
    $array['project_type'] = "client"; //client or vendor
    $array['project_name'] = "Sample Demo";
    $array['project_dec'] = "Sample";
    $array['allow_remote'] = 1;
    $array['category_id'][] = 6;
    $array['category_id'][] = 8;
    $array['category_id'][] = 7;
    $array['category_id'][] = 2;
    $array['address'] = "1541 Riverside Drive, Ottawa, ON, Canada";
    $array['billing_type'] = "per_hour";
    $array['default_fee'] = "25";
    $array['card'] = 1; //if project_type is client
    $array['tag'] = "Sample, Samples, another one";
    //include if you want to add more images to 

    $url = $u."api/advert/post";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //remove image
    $url = $u."api/advert/image/67";
    $json_data = json_encode($array);
    echo delete($header, $url);*/
/*
    //List all advert
    $url = $u."api/advert/get/all";
    echo get($header, $url);*/
/*
    $url = $u."api/advert/get/active";
    echo get($header, $url);*/
/*    
    $url = $u."api/advert/get/conversation";
    echo get($header, $url);*/
/*    
    $url = $u."api/advert/get/on-going";
    echo get($header, $url);*/
/*    
    $url = $u."api/advert/get/running";
    echo get($header, $url);*/
/*    
    $url = $u."api/advert/get/past";
    echo get($header, $url);*/
/*    
    $url = $u."api/advert/get/archive";
    echo get($header, $url);*/
/*    
    $url = $u."api/advert/get/draft";
    echo get($header, $url);*/
/*
    $url = $u."api/advert/get/saved";
    echo get($header, $url);*/
/*
    $url = $u."api/advert/get/27/2";
    echo get($header, $url);*/
/*
    $url = $u."api/advert/get/counter/26";
    echo get($header, $url);*/
/*
    $url = $u."api/advert/delete/22";
    echo delete($header, $url);*/
/*
    //approve advert
    $url = $u."api/advert/approve/23/2";
    echo get($header, $url);*/
/*
    //advert messages
    $url = $u."api/advert/messages/25";
    echo get($header, $url);*/
/*
    //advert new messages
    $url = $u."api/advert/newMessages/25";
    echo get($header, $url);*/
/*
    //send message
    $array['message'] = "random message ".rand();
    $array['user_r_id'] = "1";
    $array['project_id'] = "25";
    $url = $u."api/advert/messages";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //featured ad
    $array['days'] = "100";
    $array['card'] = "1";
    $array['project_id'] = "1";
    $url = $u."api/advert/featured";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //get status
    $url = $u."api/advert/featured/26";
    echo get($header, $url);*/
/*
    //negotiate fees
    $array['negotiated_fee'] = "100";
    $array['user_r_id'] = "1"; //if the user is the ad owner, this is the id of the responder
    $array['project_id'] = "26";
    $url = $u."api/advert/negotiate";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*    
    //get negotiate status
    $url = $u."api/advert/negotiate/26/2";
    echo get($header, $url);*/
/*
    //respond to negotiate request
    $array['reponse'] = "y"; //y for yes, n for no
    $array['neg_id'] = "18";
    $array['msg_id'] = "129";
    $url = $u."api/advert/negotiate/respond";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //propose hours
    $array['hours'] = "100";
    $array['project_id'] = "25";
    $array['user_r_id'] = "2"; //if the user is the ad owner, this is the id of the responder
    $url = $u."api/advert/hours";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data); */
/*
    //get hour status
    $url = $u."api/advert/hours/25"; //2 is he ID  of the responder if user is the ad owner
    echo get($header, $url, $json_data);*/
/*
    //respond to hour request
    $array['reponse'] = "y"; //y for yes, n for no
    $array['hour_request_id'] = "13";
    $array['project_id'] = "27";
    $array['user_r_id'] = "2"; //if the user is the ad owner, this is the id of the responder
    $array['comment'] = "Rejection comment"; //this is compulsary for rejection
    $url = $u."api/advert/hours/respond";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //Approve hours
    $array['duration'] = 1;
    $array['hour_request_id'] = "14";
    $array['project_id'] = "23";
    $url = $u."api/advert/hours/approve";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //propose milestone
    $array['content'][0]['data'] = "milestone 1 API";
    $array['content'][0]['duration'] = "1";
    $array['content'][0]['duration_lenght'] = "Hour"; //Minute, Hour, Day, Week, Month
    $array['content'][1]['data'] = "milestone 2 API";
    $array['content'][1]['duration'] = "1";
    $array['content'][1]['duration_lenght'] = "Day"; //Minute, Hour, Day, Week, Month
    $array['content'][2]['data'] = "milestone 3 API";
    $array['content'][2]['duration'] = "1";
    $array['content'][2]['duration_lenght'] = "Week"; //Minute, Hour, Day, Week, Month
    $array['project_id'] = "27";
    $array['user_r_id'] = "2"; //if the user is the ad owner, this is the id of the responder
    $url = $u."api/advert/milestone";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //get milestone status
    $url = $u."api/advert/milestone/27/2"; //2 is he ID  of the responder if user is the ad owner
    echo get($header, $url);*/
/*
    //respond to milestone request
    $array['reponse'] = "y"; //y for yes, n for no
    $array['milestone_request_id'] = "17";
    $array['project_id'] = "27";
    $array['user_r_id'] = "2"; //if the user is the ad owner, this is the id of the responder
    $array['comment'] = "Rejection comment"; //this is compulsary for rejection
    $url = $u."api/advert/milestone/respond";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //request approval for milestone
    $array['milestone_index'] = 1;
    $array['milestone_request_id'] = "15";
    $array['project_id'] = "27";
    $url = $u."api/advert/milestone/request";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //Approve milestone
    $array['milestone_index'] = 1;
    $array['milestone_request_id'] = "15";
    $array['project_id'] = "27";
    $url = $u."api/advert/milestone/approve";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //mark ad as complete
    $url = $u."api/advert/complete/28";
    echo get($header, $url);*/
/*
    //request ad as complete
    $url = $u."api/advert/request/28";
    echo get($header, $url);*/
/*
    //review criteria
    $url = $u."api/advert/review/parameters/28";
    echo get($header, $url);*/
/*
    //get review
    $url = $u."api/advert/review/28";
    echo get($header, $url);*/
/*
    //post review
    $array['project_id'] = 28;
    $array['comment'] = "No Comment Via API";
    $array['rating'][0]['question_id'] = 4;
    $array['rating'][0]['score'] = 3;
    $array['rating'][1]['question_id'] = 5;
    $array['rating'][1]['score'] = 3;
    $array['rating'][2]['question_id'] = 6;
    $array['rating'][2]['score'] = 3;
    $array['rating'][3]['question_id'] = 7;
    $array['rating'][3]['score'] = 3;
    $url = $u."api/advert/review";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //messages
    //send
    $array['to_list'] = "2,3";
    $array['subject'] = "Testing API";
    $array['message'] = "this is a test message for the API";
    $array['previous_id'] = 19;

    $url = $u."api/messages/send";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //inbox
    $url = $u."api/messages/get/count";
    $json_data = json_encode($array);
    echo get($header, $url);*/
/*
    //inbox
    $url = $u."api/messages/get/inbox";
    $json_data = json_encode($array);
    echo get($header, $url);*/
/*
    //sent
    $url = $u."api/messages/get/sent";
    $json_data = json_encode($array);
    echo get($header, $url);*/
/*
    //read
    $url = $u."api/messages/get/15";
    $json_data = json_encode($array);
    echo get($header, $url);*/
/*
    //notification
    //sent
    $url = $u."api/notifications/get/all";
    $json_data = json_encode($array);
    echo get($header, $url);*/
/*
    //read
    $url = $u."api/notifications/get/29";
    $json_data = json_encode($array);
    echo get($header, $url);*/

    print_r($header);
    print_r($json_data);
    echo "<br>";
    echo $url;

    function get($header,$url) {
        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output = curl_exec($ch);
        // curl_close($ch);
        // return $output;
    }

    function post($header,$url, $data) {
        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // $output = curl_exec($ch);
        // curl_close($ch);
        
        // return $output;
    }
    
    function put($header,$url, $data) {
        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // $output = curl_exec($ch);
        // curl_close($ch);
        
        // return $output;
    }

    function delete($header,$url) {
        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output = curl_exec($ch);
        // curl_close($ch);
        // return $output;
    }
?>