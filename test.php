<?php
    echo "<pre>";
    $product_key = rand();
    // $u = "http://127.0.0.1/MOBA-Backend/";
    // $token = "111HEVQ38JEOKVNB1EJ";
    $u = "https://moba.com.ng/";
    $token = "440XVMA5TFBXJ16VN06";
    $gateway_passcode = base64_encode($product_key."_".$token);

    // //common factors
    $header[] = "Content-Type: application/json";
    $header[] = "Authorization: Bearer ".$gateway_passcode;
	$header[] ='key: '.$product_key;
	$header[] ='ver: 1';
	$header[] ='longitude: 3.349149';
    $header[] ='latitude: 6.605874';
    //common factors
    // $header[] = "Content-Type: application/json";
    // $header[] = "Authorization: Bearer ODYzM182NjBKNTBSWks5MVhRMzVJS0k1";
	// $header[] ='key: 8633';
	// $header[] ='ver: 1';
	// $header[] ='longitude: 7.4268125';
    // $header[] ='latitude: 9.1228643';
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

    // $img[] = "tmp/1.jpeg";
    // $img[] = "tmp/2.jpeg";
    // $img[] = "tmp/2.jpg";
    // $img[] = "tmp/2.png";
    // $img[] = "tmp/3.jpg";
    // $img[] = "tmp/4.png";
    // $sImg = shuffle($img);
    // //1395 bank street  9am to 1:30pm tuesdays to friday, Elter Krity; 613 745 8124 x5360
    // //register
    // //type = "local", "social_media";
    // //user_type = "0 for users", "1 for service provider", "2 for service admin";
    // $array['user_type'] = 1;
    // $array['last_name'] = "Fighi".rand();
    // $array['other_names'] = "Fighi".rand();
    // $array['email'] = "Chi@gh.bjr".rand();
    // $array['password'] = "nigsu111";
    // $array['account_type'] = "local";
    // $array['account_type_token'] = "";
    // $array['firebase_token'] = "asdfasdfa";
    // //service provider
    // $array['mobile_number'] = "080".rand(10000000, 99999999);
    // $array['street'] = "Issuti road";
    // $array['city'] = "Igando";
    // $array['state'] = "Lagos";
    // $array['country'] = "Nigeria";
    // $array['category'] = rand(1,52).",".rand(1,52).",".rand(1,52).",".rand(1,52).",".rand(1,52).",".rand(1,52);
    // $array['id_type'] = 1;
    // $array['id_expiry'] = "Kunzu";
    // $array['id_number'] = "Kunzu";
    // $array['photo_file'] = base64_encode(file_get_contents($img[$sImg]));
    // $array['id_file'] = base64_encode(file_get_contents($img[$sImg]));
    // $array['kin_name'] = "Kunzu".rand();
    // $array['kin_email'] = rand().'@'.rand().".com";
    // $array['kin_phone'] = rand();
    // $array['kin_relationship'] = "Family";

    // $url = $u."api/users/join";
    // $json_data = json_encode($array);
    // echo post($header, $url, $json_data);
/*
    //recover
    $url = $u."api/users/recover/".urlencode("olukayode.adebiyi@hotmail.co.uk");
    echo get($header, $url); */
/*
    //profile
    $url = $u."api/users/profile";
    echo get($header, $url);*/
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
    //category::Questionnaire
    $url = $u."api/category/questionnaire/35";
    echo get($header, $url);*/
/*
    Posts
    //posts::category
    $url = $u."api/posts/category/1/1";
    echo get($header, $url);*/
/*
    //posts::search
    $url = $u."api/posts/search/a/1";
    echo get($header, $url);*/
/*
    //posts::featured
    $url = $u."api/posts/featured";
    echo get($header, $url);*/
/*
    //posts::aroundme
    $url = $u."api/posts/aroundMe/1";
    echo get($header, $url);*/
 
    // // Request
    // // request::create
    // $array['fee']  = "250.00";
    // $array['time']  = "60";
    // $array['address']  = "147 isuti road, Igando Lagos";
    // $array['description']  = "this is just a description text";
    // $array['category_id']  = 1;
	// $array['longitude'] = 3.349149;
    // $array ['latitude'] = 6.605874;

    // $array['data'][0]['question']  = "What do you need an AC Technician for?";
    // $array['data'][0]['answer']  = "I need my AC serviced.";
    // $array['data'][1]['question']  = "How soon do you want this done?";
    // $array['data'][1]['answer']  = "soon";
    // $array['media'][0]  = base64_encode(file_get_contents($img[$sImg]));
    // $array['media'][1]  = base64_encode(file_get_contents($img[$sImg]));
    // $array['media'][2]  = base64_encode(file_get_contents($img[$sImg]));

    // $url = $u."api/request/create";
    // echo $json_data = json_encode($array);
    // echo post($header, $url, $json_data);

/*
    //request::hire
    $array['user_r_id'] = "2";
    $array['post_id'] = "2";

    $url = $u."api/request/hire";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*
    //negotiate fees
    $array['negotiated_fee'] = "100";
    $array['user_r_id'] = "2"; //if the user is the ad owner, this is the id of the responder
    $array['post_id'] = "1";
    $url = $u."api/request/negotiate";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
/*    
    //get negotiate status
    $url = $u."api/request/negotiate/1/2";
    echo get($header, $url);*/
/*
    //respond to negotiate request
    $array['reponse'] = "y"; //y for yes, n for no
    $array['neg_id'] = "1";
    $array['msg_id'] = "5";
    $url = $u."api/request/negotiate/respond";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/
/*
    //List all advert
    $url = $u."api/advert/get/all";
    echo get($header, $url);*/
/*
    $url = $u."api/advert/get/all";
    echo get($header, $url);*/
/*
    $url = $u."api/request/get/open";
    echo get($header, $url);*/
/*    
    $url = $u."api/request/get/running";
    echo get($header, $url);*/
/*    
    $url = $u."api/request/get/current";
    echo get($header, $url);*/
/*    
    $url = $u."api/request/get/past";
    echo get($header, $url);*/
/*
    $url = $u."api/request/get/available";
    echo get($header, $url);*/
/*    
    $url = $u."api/request/get/active";
    echo get($header, $url);*/

    $url = $u."api/request/get/61";
    echo get($header, $url);
/*
    $url = $u."api/request/delete/1";
    echo delete($header, $url);*/
/*
    //request messages
    $url = $u."api/request/messages/87"; //post ID
    echo get($header, $url);*/
/*
    //request new messages
    $url = $u."api/request/newMessages/1/2";//post ID //user ID
    echo get($header, $url);*/
/*
    //request message
    $array['message'] = "random message ".rand();
    $array['user_r_id'] = "2";
    $array['post_id'] = "1";
    $url = $u."api/request/messages";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
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
    $array['cardno'] = "5531886652142950";
    $array['mm'] = "02";
    $array['yy'] = "20";
    $array['cvv'] = "123";

    $url = $u."api/cards/add/";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/

    // //add
    // $array['card_id'] = "10";
    // //$array['pin'] = "3310";
    // $array['otp_code'] = "12345";

    // $url = $u."api/cards/verify";
    // $json_data = json_encode($array, JSON_PRETTY_PRINT);
    // echo put($header, $url, $json_data);
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
/*
    //remove
    //$array['card'] = 1;
    $array['account'] = 1;
    $array['amount'] = 390;

    $url = $u."api/wallet/withdraw";
    $json_data = json_encode($array);
    echo post($header, $url, $json_data);*/
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

    //service provider
    $array['mobile_number'] = "08182441752";
    $array['street'] = "Issuti road";
    $array['city'] = "Igando";
    $array['state'] = "Lagos";
    $array['category'] = "1,2,3,4,5,6,7";
    $array['kin_name'] = "Kunzu";
    $array['kin_email'] = "Kunzu";
    $array['kin_phone'] = "Kunzu";
    $array['kin_relationship'] = "Kunzu";

    print_r($array);
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
    $array['id_type'] = "Kunzu";
    $array['id_expiry'] = "Kunzu";
    $array['id_number'] = "Kunzu";
    $array['file'] = base64_encode(file_get_contents($u."media/3086af664b539308321cf4adf2b49049ac21ed2b/561564329078709.jpg"));

    $url = $u."api/users/gov_id";
    $json_data = json_encode($array);
    echo put($header, $url, $json_data);*/

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
    $url = $u."api/advert/featured/26";Fr
    echo get($header, $url);*/
/*
    //mark ad as complete
    $url = $u."api/request/complete/28";
    echo get($header, $url);*/
/*
    //request ad as complete
    $url = $u."api/request/alert/25";
    echo get($header, $url);*/
/*
    //review criteria
    $url = $u."api/request/review/parameters/28";
    echo get($header, $url);*/
/*
    //get review
    $url = $u."api/request/review/28";
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
    //lst
    $url = $u."api/notifications/get/project";
    $json_data = json_encode($array);
    echo get($header, $url);*/
/*
    //read
    $url = $u."api/notifications/get/29";
    $json_data = json_encode($array);
    echo get($header, $url);*/

    print_r($header);
    @print_r($json_data);
    echo "<br>";
    echo $url;

    function get($header,$url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function post($header,$url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        
        return $output;
    }
    
    function put($header,$url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        
        return $output;
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