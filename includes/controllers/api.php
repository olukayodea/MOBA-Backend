<?php
    class api extends database {
        protected $user_id;
        protected $user_type;
		function dumpData($data, $output) {
            // error_log(json_encode($data));
            // error_log(json_encode($output));
			/*global $db;
			try {
				$sql = $db->prepare("INSERT INTO `dump` (`url`, `data`, `output`) VALUES (:url, :data, :output)");
				$sql->execute(
					array(	':url' => $_SERVER['REQUEST_URI'],
							':data' => $data,
							':output' => $output)
						);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}*/
        }
        
        public function prep($header, $postPequest, $data) {
            global $users;
            global $post;
            global $request;
            global $category;
            global $bank_account;
            global $wallet;
            global $userPayment;
            global $country;
            global $inbox;
            global $notifications;
            global $banks;
            global $identity;
            global $options;
            global $currentLocation;

            $requestData = explode("/", $postPequest);
            $mode = @strtolower($requestData[0]);
			$action = @strtolower($requestData[1]);
            $string = @strtolower($requestData[2]);
            $page = @strtolower($requestData[3]);

            $location['longitude'] = $header['longitude'];
            $location['latitude'] = $header['latitude'];

            $loc = $this->getCode($location);
            
            $regionData = $country->getLoc($loc['country_code']);
            $location['ref'] = $regionData['ref'];
            $location['code'] = $regionData['code'];
            $location['city'] = $loc['city'];
            $location['state'] = $loc['province'];
            $location['state_code'] = $loc['province_code'];
            $location['country'] = $loc['country'];
            $location['address'] = $loc['address'];

			$this->dumpData($header, json_encode($requestData));

            $returnedData = json_decode($data, true);
            if ($this->methodCheck($header['method'], $mode.":".$action)) {
                if ($mode == "data") {
                    $return['status'] = 200;
                    $return['message'] = "OK";
                    $return['data']['id_type'] = $identity->apiGetList($location);
                    $return['data']['country'] = $country->countryAPI();
                    $return['data']['banks'] = $banks->apiGetList($location);
                    $return['data']['category'] = $category->apiGetList($location);
                    $return['data']['censored_words'] = explode( ",", $options->get("text_filter") );
                } else if (($mode == "users") && ($action == "join")) {
                    //registeration 
                    $register = $users->create($returnedData);
                    $userData = $users->apiGetList("getOne", $register);
                    if ($register) {
                        if ($register == "error") {
                            $return['status'] = "503";
                            $return['message'] = "Service Unavailable";
                            $return['additional_message'] = "there was an error creating this account, please try again";
                        } else if ($register == "duplicate data") {
                            $return['status'] = "409";
                            $return['message'] = "Conflict";
                            $return['additional_message'] = "this account already exist, please login or reset your password to continue";
                        } else {
                            $token = $this->getToken();
                            $userData = $users->apiGetList("getOne", $register);
                            $return['status'] = 200;
                            $return['message'] = "OK";
                            $return['additional_message'] = "account created successfully";
                            $return['token'] = $token;
                            $return['data'] = $userData['data'];
                        }
                    } else {
                        $return['status'] = "500";
                        $return['message'] = "Internal Server Error";
                    }
                } else if (($mode == "users") && ($action == "recover")) {
                    //recover password
                    $add = $users->validateAcc($string);
                    if ($add) {
                        $return['status'] = 200;
                        $return['message'] = "OK";
                        $return['additional_message'] = "A link has been sent to ".$string.". Click on the Link to continue. This Link expires in 24 hours. Ignore the email if you change your mind at any time";
                    } else {
                        $return['status'] = "404";
                        $return['message'] = "Not Found";
                        $return['additional_message'] = $string." does not look like a valid email in our database!";
                    }
                } else if (($mode == "users") && ($action == "login")) {
                    //login with credentials
                    $login = $users->login($returnedData);

                    if ($login) {
                        $this->user_id = $login['ref'];
                        $this->user_type = $login['user_type'];
                        $token = $this->getToken();
                        $userData = $users->apiGetList("getOne", $login['ref']);

                        if ($login['status'] == "NEW") {
                            $return['status'] = "403";
                            $return['message'] = "Forbidden";
                            $return['additional_message'] = "Your account is inactive, you will not be able to perform major functions on this site until you activate your account. Click on the activation link in the Welcome E-Mail sent to you to activate this account<";
                        } else if ($login['status'] == "INACTIVE") {
                            $return['status'] = "403";
                            $return['message'] = "Forbidden";
                            $return['additional_message'] = "Your account has been deadivated.<br>Please contact us to resolve this issue";
                        } else {
                            $return['status'] = 200;
                            $return['message'] = "OK";
                            $return['additional_message'] = "Login complete";
                            $return['token'] = $token;
                            $return['data'] = $userData['data'];
                            $return['wallet'] = $wallet->apiGetWalletList("balance", $location['ref'], $login['ref'])['data'];
                            $return['bank_accounts'] =  $bank_account->listAllUserData($login['ref'], 0, 20)['list'];
                            $return['bank_cards'] = $wallet->listAllUserData($login['ref'], 0, 20)['list'];
                        }
                    } else {
                        $return['status'] = "404";
                        $return['message'] = "Not Found";
                        $return['additional_message'] = "email and password combination is not correct";
                    }
                } else if (($mode == "category") && ($action == "questionnaire")) {
                    //list all parent category
                    $list = $category->apiGetQustions($string);
                    $return['status'] = 200;
                    $return['message'] = "OK";
                    $return['questionnaire'] = $list;
                } else if (($mode == "category") && ($action == "listparent")) {
                    //list all parent category
                    $list = $category->apiGetList($location,"parent");
                    $return['status'] = 200;
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if (($mode == "category") && ($action == "listsub")) {
                    //list all sub category
                    $list = $category->apiGetList($location, $string);
                    $return['status'] = 200;
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if (($mode == "category") && ($action == "list")) {
                    //list all 
                    $list = $category->apiGetList($location, "all");
                    $return['status'] = 200;
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if ($mode == "banks") {
                    //list all 
                    $list = $banks->apiGetList($regionData);
                    $return['status'] = 200;
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if (($mode == "posts") && (($action == "category") || ($action == "search") || ($action == "featured") || ($action == "aroundme"))) {
                    //list all categories
                    $return = $post->postAPI($location, $action, $string, $page);
                } else if ($this->authenticate($header)) {
                    $userData = $this->authenticatedUser($header['auth']);

                    $currentLocation->set($userData['ref'], $location['longitude'], $location['latitude'], $location['city'], $location['state'], $location['country']);

                    //authenticated users only
                    if (($mode == "request") && ($action == "create")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $return = $request->creatAPI($location, $returnedData);
                    } else if (($mode == "request") && (($action == "messages") || ($action == "newmessages"))) {
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $act = "post";
                        if (is_numeric($string)) {
                            $returnedData['user_r_id'] = intval($page);
                            $returnedData['post_id'] = intval($string);
                            if ($action == "newmessages") {
                                $act = "new";
                            } else {
                                $act = "list";
                            }
                        }
                        $return = $request->apiMessages($returnedData, $act);
                    } else if (($mode == "request") && ($action == "hire")) {
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $return = $request->apiApprove($returnedData);
                    } else if (($mode == "request") && ($action == "negotiate")) {
                        $act = "post";
                        if ((is_numeric($string)) && (intval($page) > 0)) {
                            $returnedData['post_id'] = $string;
                            $returnedData['user_r_id'] = $page;
                            $act = "check";
                        } else if ($string == "respond") {
                            
                            $act = "respond";
                        }
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $return = $request->apiMegotiate($returnedData, $act);
                    } else if (($mode == "advert") && ($action == "featured")) {
                        // $act = "post";
                        // if (is_numeric($string)) {
                        //     $returnedData['post_id'] = $string;
                        //     $act = "check";
                        // }
                        // $returnedData['user_id'] = $userData['ref'];
                        // $return = $projects->apiFeatured($returnedData, $act);
                    } else if (($mode == "request") && ($action == "delete")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $returnedData['ref'] = intval($string);
                        $return = $request->apiDelete($returnedData);
                    } else if (($mode == "request") && ($action == "get")) {
                        $return = $request->apiGetList($string, $page, $userData['ref'], $page, $location);
                    } else if (($mode == "request") && (($action == "complete") || ($action == "alert"))) {
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $returnedData['post_id'] = intval($string);
                        $return = $request->apiComplete($returnedData, $action);
                    } else if (($mode == "request") && ($action == "review")) {
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        if ($string == "parameters") {
                            $act = "list";
                            $returnedData['post_id'] = intval($page);
                        } else if (intval($string) > 0) {
                            $act = "get";
                            $returnedData['post_id'] = intval($string);
                        } else {
                            $act = "post";
                        }
                        
                        $return = $request->apiReview($returnedData, $act);                
                    } else if (($mode == "users") && ($action == "get") && ($string == "contact")) {
                        $return = $users->apiGetList("contact", $userData['ref'], $page);
                    } else if (($mode == "users") && ($action == "profile")) {
                        if ($header['method'] == "GET") {
                            $return = $users->apiGetList("getOne", $userData['ref'], false, false, $location);
                        } else if ($header['method'] == "PUT") {
                            unset($returnedData['email']);
                            $returnedData['ref'] = $userData['ref'];
                            $add = $users->modify($returnedData);
                            
                            if ($add) {
                                $return = $users->apiGetList("getOne", $userData['ref']);
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "An error occured while updating this profile";
                            }
                        }
                    } else if (($mode == "users") && ($action == "profilepicture")) {
                        $ref = $userData['ref'];
                        $file = $returnedData['file'];
                        if (strtoupper($header['method'] == "PUT")) {

                            $add = $users->saveProfilePicture($ref, $file, "profile");
                            
                            if ($add) {
                                if ($add['title'] === "ERROR") {
                                    $return['status'] = "406";
                                    $return['message'] = "Not Acceptable";
                                    $return['additional_message'] = $add['desc'];
                                } else {
                                    $return['status'] = 200;
                                    $return['message'] = "OK";
                                    $return['path'] = URL.$add['desc'];
                                }
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "An error occured while updating this profile picture";
                            }
                        } else if (strtoupper($header['method'] == "DELETE")) {
                            $add = $users->removeProfilePicture($ref);
                            
                            if ($add) {
                                if ($add['title'] === "ERROR") {
                                    $return['status'] = "406";
                                    $return['message'] = "Not Acceptable";
                                    $return['additional_message'] = $add['desc'];
                                } else {
                                    $return['status'] = 200;
                                    $return['message'] = "OK";
                                }
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "An error occured while updating this profile picture";
                            }
                        }
                    } else if (($mode == "users") && ($action == "gov_id")) {
                        $ref = $userData['ref'];
                        $file = $returnedData['file'];

                        $add = $users->saveProfilePicture($ref, $file, "gov_id", $returnedData);
                        
                        if ($add) {
                            if ($add['title'] === "ERROR") {
                                $return['status'] = "406";
                                $return['message'] = "Not Acceptable";
                                $return['additional_message'] = $add['desc'];
                            } else {
                                $return['status'] = 200;
                                $return['message'] = "OK";
                                $return['path'] = URL.$add['desc'];
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while updating this profile ID";
                        }
                    } else if (($mode == "users") && ($action == "password")) {
                        $returnedData['ref'] = $userData['ref'];
                        $add = userHome::updatePassword($returnedData);

                        if ($add) {
                            if ($add === "invalid") {
                                $return['status'] = "406";
                                $return['message'] = "Not Acceptable";
                                $return['additional_message'] = "Invalid current password. Password not updated";
                            } else {
                                $return['status'] = 200;
                                $return['message'] = "OK";
                            }
                            
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while updating this profile password";
                        }
                    } else if (($mode == "account") && ($action == "edit")) {
                        //edit account number
                        if ($returnedData['ref'] > 0) {
                            //check if the edit ref is passed
                            $returnedData['user_id'] = $userData['ref'];
                            $add = $bank_account->create($returnedData);
                            if ($add) {
                                $return['status'] = "202";
                                $return['message'] = "Accepted";
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "An error occured while creating this account";
                            }
                        } else {
                            $return['status'] = "400";
                            $return['message'] = "Bad Request";
                            $return['additional_message'] = "Account REF field missing";
                        }
                    } else if (($mode == "account") && ($action == "get") && ($string == "all")) {
                        //get one row
                        $return = $bank_account->apiGetList("list", $userData['ref'], false, $page);
                    } else if (($mode == "account") && ($action == "get") && ($string == "default")) {
                        //get one row
                        $return = $bank_account->apiGetList("default", $userData['ref']);
                    } else if (($mode == "account") && ($action == "get")) {
                        //get one row
                        $return = $bank_account->apiGetList("getOne", $userData['ref'], $string);
                    } else if (($mode == "account") && ($action == "add")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $returnedData['region'] = $location['ref'];
                        $add = $bank_account->create($returnedData);
                        if ($add) {
                            $return['status'] = "201";
                            $return['message'] = "Created";
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while creating this account";
                        }
                    } else if (($mode == "account") && ($action == "makedefault")) {
                        $add = $bank_account->setDefault($returnedData['ref']);
                        if ($add) {
                            $return['status'] = "202";
                            $return['message'] = "Accepted";
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while making this account default";
                        }
                    } else if (($mode == "account") && ($action == "delete")) {
                        $add = $bank_account->remove($string, $userData['ref']);
                        if ($add) {
                            if ($add === "0000") {
                                $return['status'] = "403";
                                $return['message'] = "Forbidden";
                            } else {
                                $return['status'] = 200;
                                $return['message'] = "OK";
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while deleting this account";
                        }
                    } else if (($mode == "account") && ($action == "changestatus")) {
                        $add = $bank_account->toggleStatus($returnedData['ref']);
                        if ($add) {
                            $return['status'] = 200;
                            $return['message'] = "OK";
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while deleting this account";
                        }
                    } else if (($mode == "cards") && ($action == "get") && ($string == "all")) {
                        //get one row
                        $return = $wallet->apiGetList("list", $userData['ref'], false, $page);
                    } else if (($mode == "cards") && ($action == "get") && ($string == "default")) {
                        //get one row
                        $return = $wallet->apiGetList("default", $userData['ref']);
                    } else if (($mode == "cards") && ($action == "get")) {
                        //get one row
                        $return = $wallet->apiGetList("getOne", $userData['ref'], $string);
                    } else if (($mode == "cards") && ($action == "verify")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $add = $wallet->verifyPayment($returnedData);
                        
                        if ($add) {
                            if ($add['status'] == "OK") {
                                if ($add['message'] == "incomplete") {
                                    $return['status'] = 100;
                                    $return['message'] = "Continue";
                                    $return['url'] = URL."api/cards/verify";
                                    $return['additional_message'] = $add['additional_message'];
                                    $return['card_id'] = $add['card_id'];
                                    $return['required_fields'] = $add['fields'];
                                } else {
                                    $return['status'] = "201";
                                    $return['message'] = "Created";
                                }
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = $add['message'];
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while creating this payment card";
                        }
                    } else if (($mode == "cards") && ($action == "add")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $add = $wallet->create($returnedData);
                        if ($add) {
                            if ($add['status'] == "OK") {
                                if ($add['message'] == "incomplete") {
                                    $return['status'] = 100;
                                    $return['message'] = "Continue";
                                    $return['url'] = URL."api/cards/verify";
                                    $return['additional_message'] = $add['additional_message'];
                                    $return['card_id'] = $add['card_id'];
                                    $return['required_fields'] = $add['fields'];
                                } else {
                                    $return['status'] = "201";
                                    $return['message'] = "Created";
                                }
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = $add['message'];
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while creating this payment card";
                        }
                    } else if (($mode == "cards") && ($action == "makedefault")) {
                        $add = $wallet->setDefault($returnedData['ref']);
                        if ($add) {
                            $return['status'] = "202";
                            $return['message'] = "Accepted";
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while making this payment card default";
                        }
                    } else if (($mode == "cards") && ($action == "delete")) {
                        $add = $wallet->remove($string, $userData['ref']);
                        if ($add) {
                            if ($add === "0000") {
                                $return['status'] = "403";
                                $return['message'] = "Forbidden";
                            } else {
                                $return['status'] = 200;
                                $return['message'] = "OK";
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while deleting this payment card, you cannot delete a default card";
                        }
                    } else if (($mode == "cards") && ($action == "changestatus")) {
                        $add = $wallet->toggleStatus($returnedData['ref']);
                        if ($add) {
                            $return['status'] = 200;
                            $return['message'] = "OK";
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while deleting this payment card";
                        }
                    } else if (($mode == "transaction") && ($action == "get") && ($string == "all")) {
                        //get one row
                        $return = $wallet->apiGetTransList("list", $userData['ref'], false, $page);
                    } else if (($mode == "transaction") && ($action == "get")) {
                        //get one row
                        $return = $wallet->apiGetTransList("getOne", $userData['ref'], $string);
                    } else if (($mode == "wallet") && ($action == "deposit")) {
                        $feedData['net_total'] = $returnedData['amount'];
                        $feedData['tx_dir'] = "CR";
                        $feedData['card'] = $returnedData['card'];
                        $feedData['type'] = "CC";
                        $feedData['user_id'] = $userData['ref'];
                        $feedData['region'] = $regionData['ref'];
                        $add = $userPayment->postWallet($feedData);

                        if ($add['code'] == 1) {                            
                            $return['status'] = "202";
                            $return['message'] = "Accepted";
                            $return['additional_message'] = $add['message'];
                        } else {
                            $return['status'] = "406";
                            $return['message'] = "Not Acceptable";
                            $return['additional_message'] = $add['message'];
                        }
                    } else if (($mode == "wallet") && ($action == "withdraw")) {
                        $feedData['net_total'] = $returnedData['amount'];
                        $feedData['tx_dir'] = "DR";
                        if (intval($returnedData['account']) > 0) {
                            $feedData['card'] = $returnedData['account'];
                            $feedData['type'] = "BA";
                        } else if (intval($returnedData['card']) > 0) {
                            $feedData['card'] = $returnedData['card'];
                            $feedData['type'] = "CC";
                        }
                        $feedData['user_id'] = $userData['ref'];
                        $feedData['region'] = $regionData['ref'];
                        $add = $userPayment->postWallet($feedData);

                        if ($add['code'] == 1) {                            
                            $return['status'] = "202";
                            $return['message'] = "Accepted";
                            $return['additional_message'] = $add['message'];
                        } else {
                            $return['status'] = "406";
                            $return['message'] = "Not Acceptable";
                            $return['additional_message'] = $add['message'];
                        }
                    } else if (($mode == "wallet") && ($action == "get") && ($string == "all")) {
                        //get one row
                        $return = $wallet->apiGetWalletList("list", $regionData['ref'], $userData['ref'], false, $page);
                    } else if (($mode == "wallet") && ($action == "get") && ($string == "balance")) {
                        //get one row
                        $return = $wallet->apiGetWalletList("balance", $regionData['ref'], $userData['ref']);
                    } else if (($mode == "wallet") && ($action == "get")) {
                        //get one row
                        $return = $wallet->apiGetWalletList("getOne", $regionData['ref'], $userData['ref'], $string);
                    } else if (($mode == "messages") && ($action == "get") && ($string == "inbox")) {
                        $return = $inbox->apiGetList("inbox", $regionData['ref'], $userData['ref'], false, $page);
                    } else if (($mode == "messages") && ($action == "get") && ($string == "sent")) {
                        $return = $inbox->apiGetList("sent", $regionData['ref'], $userData['ref'], false, $page);
                    } else if (($mode == "messages") && ($action == "get") && ($string == "count")) {
                        $return = $inbox->apiGetList("count", $regionData['ref'], $userData['ref']);
                    } else if (($mode == "messages") && ($action == "get")) {
                        $return = $inbox->apiGetList("getOne", $regionData['ref'], $userData['ref'], $string);
                    } else if (($mode == "messages") && ($action == "send")) {
                        $returnedData['from_id'] = $userData['ref'];
                        $add = $inbox->add($returnedData);
                        
                        if ($add) {
                            $return['status'] = "201";
                            $return['message'] = "Created";
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while sending this message";
                        }
                    
                    
                    } else if (($mode == "notifications") && ($action == "get") && ($string == "project")) {
                        if ($page == "") {
                            $page = 1;
                        } else if (intval($page) == 0) {
                            $page = 1;
                        } else {
                            $page = intval($page);
                        }

                        $return = $request->apiMessageList($userData['ref'], $page);
                    } else if (($mode == "notifications") && ($action == "get") && ($string == "all")) {
                        $id = false;
                        $act = "list";
                        if ($page == "") {
                            $page = 1;
                        } else if (intval($page) == 0) {
                            $page = 1;
                        } else {
                            $page = intval($page);
                        }
                        $return = $notifications->apiGetList($act, $userData['ref'], $id, $page);
                    } else if (($mode == "notifications") && ($action == "get")) {
                        $id = is_numeric($string);
                        $act = "getOne";
                        $return = $notifications->apiGetList($act, $userData['ref'], $id, $page);
                    }
                } else {
                    $return['status'] = "401";
                    $return['message'] = "Unauthorized";
                }
            } else {
                $return['status'] = "400";
                $return['message'] = "Bad Request";
            }
            //print_r($return);
            return $this->convert_to_json($return);
        }

        private function getToken () {
            global $users;
            $userData = $users->listOne($this->user_id, "ref");

            if ($userData['token'] != "") {
                return $userData['token'];
            } else {
                $token = substr( $this->user_id.rand().time().$this->createRandomPassword(15).rand(), 0, 32);
                $users->modifyUser("token", $token, $this->user_id, "ref");
                return $token;
            }
        }

        private function authenticatedUser($authToken) {
            global $users;
            $split = explode("_", base64_decode($authToken));
            $token = $split[1];

            return $users->listOne($token, "token");
        }

		private function authenticate($header) {
            $split = explode("_", base64_decode($header['auth']));
            $token = $split[1];
            if ($header['key'] == $split[0]) {
                if ($this->checkExixst("users", "token", $token) == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        private function methodCheck($method, $type) {
            $array = array();
            if ($method == "POST") {
                $array[] = "advert:featured";
                $array[] = "users:join";
                $array[] = "users:login";
                $array[] = "account:add";
                $array[] = "cards:add";
                $array[] = "request:create";
                $array[] = "request:messages";
                $array[] = "request:negotiate";
                $array[] = "request:hire";
                $array[] = "request:review";
                $array[] = "wallet:deposit";
                $array[] = "wallet:withdraw";
                $array[] = "messages:send";
                if (array_search($type, $array) === false) {
                    return false;
                } else {
                    return true;
                }
            } else if ($method == "GET") {
                $array[] = "data:";
                $array[] = "banks:list";
                $array[] = "users:get";
                $array[] = "users:recover";
                $array[] = "users:profile";
                $array[] = "category:advert";
                $array[] = "category:listparent";
                $array[] = "category:listsub";
                $array[] = "category:list";
                $array[] = "category:questionnaire";
                $array[] = "account:get";
                $array[] = "cards:get";
                $array[] = "posts:category";
                $array[] = "posts:search";
                $array[] = "posts:aroundme";
                $array[] = "posts:featured";
                $array[] = "request:messages";
                $array[] = "request:newmessages";
                $array[] = "request:negotiate";
                $array[] = "request:get";
                $array[] = "request:complete";
                $array[] = "request:alert";
                $array[] = "request:review";
                $array[] = "transaction:get";
                $array[] = "wallet:get";
                $array[] = "messages:get";
                $array[] = "notifications:get";
                if (array_search($type, $array) === false) {
                    return false;
                } else {
                    return true;
                }
            } else if ($method == "PUT") {
                $array[] = "account:edit";
                $array[] = "account:makedefault";
                $array[] = "account:changestatus";
                $array[] = "cards:verify";
                $array[] = "cards:makedefault";
                $array[] = "cards:changestatus";
                $array[] = "request:negotiate";
                $array[] = "users:profile";
                $array[] = "users:password";
                $array[] = "users:profilepicture";
                $array[] = "users:gov_id";
                if (array_search($type, $array) === false) {
                    return false;
                } else {
                    return true;
                }
            } else if ($method == "DELETE") {
                $array[] = "account:delete";
                $array[] = "cards:delete";
                $array[] = "request:delete";
                $array[] = "users:profilepicture";
                if (array_search($type, $array) === false) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }

        private function getCode($location) {
            $data = $this->googleGeoLocation($location['longitude'], $location['latitude']);

            return $data;
        }
        
		private function convert_to_json($data) {
			header('Content-type: application/json');
			echo json_encode($data, JSON_PRETTY_PRINT);
		}
    }
?>