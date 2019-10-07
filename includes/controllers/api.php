<?php
    class api extends database {
        public function prep($header, $request, $data) {
            global $users;
            global $category;
            global $projects;
            global $bank_account;
            global $wallet;
            global $userPayment;
            global $country;
            global $inbox;
            global $media;
            global $notifications;
            global $banks;
            global $identity;

            $requestData = explode("/", $request);
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

            $returnedData = json_decode($data, true);
            if ($this->methodCheck($header['method'], $mode.":".$action)) {
                if ($mode == "data") {
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['data']['id_type'] = $identity->apiGetList($location);
                    $return['data']['banks'] = $banks->apiGetList($location);
                    $return['data']['category'] = $category->apiGetList($location);
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
                            $return['status'] = "200";
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
                        $return['status'] = "200";
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
                        $token = $this->getToken();
                        $userData = $users->apiGetList("getOne", $_SESSION['users']['ref']);

                        if ($_SESSION['users']['status'] == "NEW") {
                            $return['status'] = "403";
                            $return['message'] = "Forbidden";
                            $return['additional_message'] = "Your account is inactive, you will not be able to perform major functions on this site until you activate your account. Click on the activation link in the Welcome E-Mail sent to you to activate this account<";
                        } else if ($_SESSION['users']['status'] == "INACTIVE") {
                            $return['status'] = "403";
                            $return['message'] = "Forbidden";
                            $return['additional_message'] = "Your account has been deadivated.<br>Please contact us to resolve this issue";
                        } else {
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['additional_message'] = "Login complete";
                            $return['token'] = $token;
                            $return['data'] = $userData['data'];
                            $return['wallet'] = $wallet->apiGetWalletList("balance", $location['ref'], $_SESSION['users']['ref'])['data'];
                            $return['bank_accounts'] =  $bank_account->listAllUserData($_SESSION['users']['ref'], 0, 20)['list'];
                            $return['bank_cards'] = $wallet->listAllUserData($_SESSION['users']['ref'], 0, 20)['list'];
                        }
                    } else {
                        $return['status'] = "404";
                        $return['message'] = "Not Found";
                        $return['additional_message'] = "email and password combination is not correct";
                    }
                } else if (($mode == "category") && ($action == "listparent")) {
                    //list all parent category
                    $list = $category->apiGetList($location,"parent");
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if (($mode == "category") && ($action == "listsub")) {
                    //list all sub category
                    $list = $category->apiGetList($location, $string);
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if (($mode == "category") && ($action == "list")) {
                    //list all 
                    $list = $category->apiGetList($location, "all");
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if (($mode == "category") && ($action == "advert")) {
                    //list all advert in category
                    $return = $projects->apiGetList($location, "category", $page, false, $string);
                } else if ($mode == "banks") {
                    //list all 
                    $list = $banks->apiGetList($regionData);
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['data'] = $list;
                } else if (($mode == "advert") && ($action == "list")) {
                    //list all
                    $return = $projects->apiGetList($location, $string, $page);
                } else if (($mode == "advert") && ($action == "search")) {
                    //list all
                    $return = $projects->apiGetList($location, $action, $page, false, $string);
                } else if (($mode == "advert") && ($action == "keywordsearch")) {
                    //list all
                    $return = $projects->apiGetList($location, $action, $page, false, $string);
                } else if ($this->authenticate($header)) {
                    $userData = $this->authenticatedUser($header['auth']);
                    //authenticated users only
                    if (($mode == "advert") && ($action == "post")) {
                        $act = "post";
                        if (isset($returnedData['ref'])) {
                            $act = "edit";
                        }
                        $returnedData['user_id'] = $userData['ref'];
                        $return = $projects->postApi($returnedData);
                    } else if (($mode == "advert") && ($action == "image")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $returnedData['image_id'] = intval($string);
                        $return = $media->removeAPI($returnedData);
                    } else if (($mode == "advert") && ($action == "negotiate")) {
                        $act = "post";
                        if ((is_numeric($string)) && (intval($page) > 0)) {
                            $returnedData['project_id'] = $string;
                            $returnedData['user_r_id'] = $page;
                            $act = "check";
                        } else if ($string == "respond") {
                            
                            $act = "respond";
                        }
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $return = $projects->apiMegotiate($returnedData, $act);
                    } else if (($mode == "advert") && ($action == "featured")) {
                        $act = "post";
                        if (is_numeric($string)) {
                            $returnedData['project_id'] = $string;
                            $act = "check";
                        }
                        $returnedData['user_id'] = $userData['ref'];
                        $return = $projects->apiFeatured($returnedData, $act);
                    } else if (($mode == "advert") && ($action == "hours")) {
                        $act = "post";
                        if (is_numeric($string)) {
                            $returnedData['project_id'] = $string;
                            $returnedData['user_r_id'] = $page;
                            $act = "check";
                        } else if ($string == "respond") {
                            
                            $act = "respond";
                        } else if ($string == "approve") {
                            
                            $act = "approve";
                        }
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $return = $projects->apiHours($returnedData, $act);
                    } else if (($mode == "advert") && ($action == "milestone")) {
                        $act = "post";
                        if (is_numeric($string)) {
                            $returnedData['project_id'] = $string;
                            $returnedData['user_r_id'] = $page;
                            $act = "check";
                        } else if ($string == "respond") {
                            
                            $act = "respond";
                        } else if ($string == "approve") {
                            
                            $act = "approve";
                        } else if ($string == "request") {
                            
                            $act = "request";
                        }
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $return = $projects->apiMilestone($returnedData, $act);
                    } else if (($mode == "advert") && ($action == "delete")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $returnedData['ref'] = intval($string);
                        $return = $projects->apiDelete($returnedData);
                    } else if (($mode == "advert") && ($action == "get")&& ($string == "counter")) {
                        $return = $projects->apiCounter($page);
                    } else if (($mode == "advert") && ($action == "get")) {
                        $return = $projects->apiGetList($location, $string, $page, $userData['ref'], $page);
                    } else if (($mode == "advert") && (($action == "complete") || ($action == "request"))) {
                        //$returnedData['user_id'] = $userData['ref'];
                        $returnedData['user_id'] = 2;
                        $returnedData['project_id'] = intval($string);
                        $return = $projects->apiComplete($returnedData, $action);
                    } else if (($mode == "advert") && ($action == "approve")) {
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $returnedData['project_id'] = intval($string);
                        $returnedData['user_r_id'] = intval($page);
                        $return = $projects->apiAPI($returnedData);
                    } else if (($mode == "advert") && ($action == "review")) {
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        if ($string == "parameters") {
                            $act = "list";
                            $returnedData['project_id'] = intval($page);
                        } else if (intval($string) > 0) {
                            $act = "get";
                            $returnedData['project_id'] = intval($string);
                        } else {
                            $act = "post";
                        }
                        
                        $return = $projects->apiReview($returnedData, $act);
                    } else if (($mode == "advert") && (($action == "messages") || ($action == "newmessages"))) {
                        $returnedData['user_id'] = $userData['ref'];
                        //$returnedData['user_id'] = 2;
                        $act = "post";
                        if (is_numeric($string)) {
                            $returnedData['user_r_id'] = intval($page);
                            $returnedData['project_id'] = intval($string);
                            if ($action == "newmessages") {
                                $act = "new";
                            } else {
                                $act = "list";
                            }
                        }
                        $return = $projects->apiMessages($returnedData, $act);
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

                        $add = $users->saveProfilePicture($ref, $file, "profile");
                        
                        if ($add) {
                            if ($add['title'] === "ERROR") {
                                $return['status'] = "406";
                                $return['message'] = "Not Acceptable";
                                $return['additional_message'] = $add['desc'];
                            } else {
                                $return['status'] = "200";
                                $return['message'] = "OK";
                                $return['path'] = URL.$add['desc'];
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "An error occured while updating this profile picture";
                        }
                    } else if (($mode == "users") && ($action == "gov_id")) {
                        $ref = $userData['ref'];
                        $file = $returnedData['file'];

                        $add = $users->saveProfilePicture($ref, $file, "gov_id");
                        
                        if ($add) {
                            if ($add['title'] === "ERROR") {
                                $return['status'] = "406";
                                $return['message'] = "Not Acceptable";
                                $return['additional_message'] = $add['desc'];
                            } else {
                                $return['status'] = "200";
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
                                $return['status'] = "200";
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
                                $return['status'] = "200";
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
                            $return['status'] = "200";
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
                    } else if (($mode == "cards") && ($action == "add")) {
                        $returnedData['user_id'] = $userData['ref'];
                        $add = $wallet->create($returnedData);
                        if ($add) {
                            if ($add['status'] == "OK") {
                                $return['status'] = "201";
                                $return['message'] = "Created";
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
                                $return['status'] = "200";
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
                            $return['status'] = "200";
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
            $token = $_SESSION['users']['ref'].$_SESSION['users']['ref'].$_SESSION['users']['user_type'].$this->createRandomPassword(15);
            $users->modifyUser("token", $token, $_SESSION['users']['ref'], "ref");
            return $token;
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
                $array[] = "advert:hours";
                $array[] = "advert:messages";
                $array[] = "advert:milestone";
                $array[] = "advert:negotiate";
                $array[] = "advert:review";
                $array[] = "advert:post";
                $array[] = "users:register";
                $array[] = "users:login";
                $array[] = "account:add";
                $array[] = "cards:add";
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
                $array[] = "advert:approve";
                $array[] = "advert:complete";
                $array[] = "advert:featured";
                $array[] = "advert:hours";
                $array[] = "advert:messages";
                $array[] = "advert:newmessages";
                $array[] = "advert:milestone";
                $array[] = "advert:negotiate";
                $array[] = "advert:request";
                $array[] = "users:recover";
                $array[] = "advert:review";
                $array[] = "advert:keywordsearch";
                $array[] = "advert:search";
                $array[] = "users:get";
                $array[] = "users:profile";
                $array[] = "category:advert";
                $array[] = "category:listparent";
                $array[] = "category:listsub";
                $array[] = "category:list";
                $array[] = "advert:list";
                $array[] = "advert:get";
                $array[] = "account:get";
                $array[] = "cards:get";
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
                $array[] = "advert:negotiate";
                $array[] = "advert:hours";
                $array[] = "advert:milestone";
                $array[] = "advert:post";
                $array[] = "account:edit";
                $array[] = "account:makedefault";
                $array[] = "account:changestatus";
                $array[] = "cards:makedefault";
                $array[] = "cards:changestatus";
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
                $array[] = "advert:delete";
                $array[] = "advert:image";
                $array[] = "account:delete";
                $array[] = "cards:delete";
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
			echo json_encode($data);
		}
    }
?>