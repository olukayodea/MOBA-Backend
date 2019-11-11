<?php
class request extends database {
    public function create($array) {
        global $media;
        if (isset($array['web'])) {
            $web = true;
            unset($array['web']);
        } else {
            $web = false;
        }
        if (isset($array['media'])) {
            $mediaFiles = $array['media'];
            unset($array['media']);
            
        }
        $array['time'] = ($array['time']*60)+time();
        $create = $this->insert("request", $array);
        if ($create) {
            
            if ($web === true) {
                if (count($mediaFiles) > 0) {
                    $mediaArray['user_id'] = $array['user_id'];
                    $mediaArray['post_id'] = $create;
                    $media->create($mediaFiles, $mediaArray);
                }
            } else {
                for ($i = 0; $i < count($mediaFiles); $i++) {
                    $file = $media->uploadAPI($create, $mediaFiles[$i], "request");
                    if ($file['title'] == "OK") {
                        $data['user_id'] = $array['user_id'];
                        $data['post_id'] = $create;
                        $data['media_type'] = "image/png";
                        $data['media_url'] = $file['desc'];

                        $this->insert("media", $data);
                    }
                }
            }
            //make payment
            $paymentData = $this->authorize($create);
            if ($paymentData['status'] == "COMPLETE") {
                return array("status" => "ok", "id" => $create);
            } else if ($paymentData['status'] == "FAILED") {
                $this->clean($create, $paymentData['tx_id']);
                return array("status" => "failed", "msg" => "your payment card could not be processed and you do not have enough balance in your wallet to initiate this request");
            }
        } else {
            return false;
        }
    }

    private function clean($id, $tx_id) {
        global $media;
        $this->delete("request", $id);
        $this->delete("transactions", $tx_id);
        $media->remove($id);
    }

    function remove($id) {
        $this->updateOne("request", "status", "DELETED", $id, "ref");
        return true;
    }

    function getSingle($name, $tag, $ref="ref") {
        return $this->getOneField("request", $name, $ref, $tag);
    }

    function listOne($id) {
        return $this->getOne("request", $id, "ref");
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll("request", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    function getList($start=false, $limit=false, $order="project_name", $dir="ASC", $where=false, $type="list") {
        return $this->lists("request", $start, $limit, $order, $dir, $where, $type);
    }

    public function updateOneRow($tag, $value, $id) {
        return $this->updateOne("request", $tag, $value, $id, "ref");
    }

    public function findRequest($location, $post_id) {
        global $search;
        return $search->catSearchData($location, $post_id, 0, 20);
    }

    public function listAllData($ref, $view, $start, $limit) {
        if ($view == "open") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'OPEN' AND `user_id` = ".$ref);
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", "`status` = 'OPEN' AND `user_id` = ".$ref, "count"));
            $return['tag'] = "All Open Request";
        } else if ($view == "running") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'ACTIVE' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", "`status` = 'ACTIVE' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")", "count"));
            $return['tag'] = "All Open Request";
        } else if ($view == "current") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'OPEN' AND `user_id` != ".$ref." AND `ref` IN (SELECT `post_id` FROM `messages` WHERE `user_id` = ".$ref." OR `user_r_id` = ".$ref." )");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", "`status` = 'OPEN' AND `user_id` != ".$ref." AND `ref` IN (SELECT `post_id` FROM `messages` WHERE `user_id` = ".$ref." OR `user_r_id` = ".$ref." )", "count"));
            $return['tag'] = "Current Interests.";
        } else if ($view == "past") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'COMPLETED' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", "`status` = 'COMPLETED' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")", "count"));
            $return['tag'] = "All Completed Request";
        } else if ($view == "all") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", " (`user_id` = ".$ref." OR `client_id` = ".$ref.") AND `status` != 'DELETED'");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", " (`user_id` = ".$ref." OR `client_id` = ".$ref.") AND `status` != 'DELETED'", "count"));
            $return['tag'] = "All Requests";
        } else {
            $return['list'] = $this->getSortedList("ACTIVE", "status", "user_id", $ref, false, false,"ref", "ASC", "AND", $start, $limit);
            $return['listCount'] = intval($this->getSortedList("ACTIVE", "status", "user_id", $ref, false, false,"ref", "ASC", "AND", false, false, "count"));
            $return['tag'] = "All Active Ads";
          }

        return $return;
    }

    public function removeDraft($id) {
        $rem = $this->remove($id);

        if ($rem) {
            //reverse transaction
            return true;
        } else {
            return false;
        }
    }

    private function authorize($id) {
        global $wallet;
        global $transactions;
        global $users;
        global $country;
        $payment_card = new payment_card;
        //get ad details
        $data = $this->listOne($id, "ref");   
        
        $regionData = $country->getLoc($data['region'], "ref");

        //get wallet balnce
        $wallet_balance = floatval($wallet->balance($data['user_id'], $data['region']));
        $tax = $data['fee']*($regionData['tax']/100);
        $fee = $data['fee'];
        $remainder = ($fee+$tax)-$wallet_balance;
        $complete = false;
        if ($wallet_balance < $fee) {
            //get lists of all cards
            $list = $payment_card->getSortedList($data['user_id'], "user_id");
            $tx_pay['user_id'] = $data['user_id'];
            $tx_pay['tx_type_id'] = $id;
            $tx_pay['tx_type'] = "request";
            $tx_pay['tx_dir'] = "DR";
            $tx_pay['card'] = 0;
            $tx_pay['region'] = $data['region'];
            $tx_pay['net_total'] = $fee;
            $tx_pay['tax_total'] = $tax;
            $tx_pay['gross_total'] = $remainder;
            $tx_id = $transactions->createTx($tx_pay);

            $pay_gateway['gross_total'] = $remainder;
            //try all the cards until one goes
            for ($j = 0; $j < count($list); $j++) {
                $pay_gateway['card'] = $list[$j]['ref'];
                //get balance from gateway
                $makePayment = $transactions->bambora_pay($pay_gateway);
                $this->updateOne("transactions", "gateway_data", serialize($makePayment), $tx_id, "ref");
                $this->updateOne("transactions", "gateway_status", $makePayment['message'], $tx_id, "ref");
                if (($makePayment['approved'] == 1) && ($makePayment['message'] == "Approved")) {
                    $this->updateOne("transactions", "status", 2, $tx_id, "ref");
                    $tx_wallet['user_id'] = $data['user_id'];
                    $tx_wallet['tx_id'] = $tx_id;
                    $tx_wallet['ref_id'] = 0;
                    $tx_wallet['tx_desc'] = "CR transaction in wallet";
                    $tx_wallet['tx_dir'] = "CR";
                    $tx_wallet['region'] = $data['region'];
                    $tx_wallet['amount'] = $remainder-$tax;
                    $tx_wallet['status'] = 1;
                    $wallet->createWallet($tx_wallet);
                    $this->updateOneRow("card", $pay_gateway['card'], $id);
                    $complete = true;
                    break;
                }
            }
        } else {
            //post transaction to wallet and credit the 
            $tx_pay['user_id'] = $data['user_id'];
            $tx_pay['tx_type_id'] = $id;
            $tx_pay['tx_type'] = "Request";
            $tx_pay['tx_dir'] = "DR";
            $tx_pay['card'] = 0;
            $tx_pay['region'] = $data['region'];
            $tx_pay['net_total'] = $fee;
            $tx_pay['tax_total'] = $tax;
            $tx_pay['gross_total'] = $fee+$tax;
            $tx_pay['gateway_status'] = "Approved";
            $tx_pay['status'] = 2;
            $tx_id = $transactions->createTx($tx_pay);
            $complete = true;
        }
        
        //approve ad and send email
        if ($complete == true) {
            $tx_wallet['user_id'] = $data['user_id'];
            $tx_wallet['tx_id'] = $tx_id;
            $tx_wallet['ref_id'] = 0;
            $tx_wallet['tx_desc'] = "Work Payment";
            $tx_wallet['tx_dir'] = "DR";
            $tx_wallet['region'] = $data['region'];
            $tx_wallet['amount'] = 0-($remainder-$tax);
            $tx_wallet['status'] = 0;
            $wallet->createWallet($tx_wallet);

            $serviceCharge = $this->getCharges($fee);

            $tx_pay['user_id'] = 0;
            $tx_pay['tx_type_id'] = $tx_id;
            $tx_pay['tx_type'] = "service_charge";
            $tx_pay['tx_dir'] = "CR";
            $tx_pay['card'] = 0;
            $tx_pay['region'] = $data['region'];
            $tx_pay['net_total'] = $serviceCharge;
            $tx_pay['tax_total'] = 0;
            $tx_pay['gross_total'] = $serviceCharge;
            $tx_pay['gateway_status'] = "Approved";
            $tx_pay['status'] = 2;
            $transactions->createTx($tx_pay);

            $user_data = $users->listOne($data['user_id']);
            $client = $user_data['last_name']." ".$user_data['other_names'];
            $subjectToClient = "New Request on MOBA";
            $contact = "MOBA <".replyMail.">";
            
            $fields = 'subject='.urlencode($subjectToClient).
                '&last_name='.urlencode($user_data['last_name']).
                '&other_names='.urlencode($user_data['other_names']).
                '&id='.urlencode($id);
            $mailUrl = URL."includes/views/emails/project_notification.php?".$fields;
            $messageToClient = $this->curl_file_get_contents($mailUrl);
            
            $mail['from'] = $contact;
            $mail['to'] = $client." <".$user_data['email'].">";
            $mail['subject'] = $subjectToClient;
            $mail['body'] = $messageToClient;
            
            global $alerts;
            $alerts->sendEmail($mail);

            $this->updateOneRow("tx_id", $tx_id, $id);
            $this->updateOneRow("status", "OPEN", $id);
            $return['status'] = "COMPLETE";
            $return['tx_id'] = $tx_id;
        } else {
            $user_data = $users->listOne($data['user_id']);
            
            $tag = "We could not approve payment for this request. Please make sure your MOBA wallet is funded with a minimum of ".$regionData['currency_symbol'].number_format($remainder, 2).". <a href='".URL."wallet'>Sigin in</a> to your MOBA Account to learn more";;

            $client = $user_data['last_name']." ".$user_data['other_names'];
            $subjectToClient = "Problem with Payment Aproval";
            $contact = "MOBA <".replyMail.">";
            
            $fields = 'subject='.urlencode($subjectToClient).
                '&last_name='.urlencode($user_data['last_name']).
                '&other_names='.urlencode($user_data['other_names']).
                '&email='.urlencode($user_data['email']).
                '&tag='.urlencode(htmlentities($tag));
            $mailUrl = URL."includes/views/emails/notification.php?".$fields;
            $messageToClient = $this->curl_file_get_contents($mailUrl);
            
            $mail['from'] = $contact;
            $mail['to'] = $client." <".$user_data['email'].">";
            $mail['subject'] = $subjectToClient;
            $mail['body'] = $messageToClient;
            
            global $alerts;
            $alerts->sendEmail($mail);

            $this->updateOne("transactions", "status", 1, $tx_id, "ref");
            $return['status'] = "FAILED";
            $return['tx_id'] = $tx_id;
        }
        return $return;
    }

    private function getCharges($amount) {
        global $options;
        return ($options->get("service_charge")/100)*$amount;
    }

    private function getFee($array) {
        global $request_negotiate;
        $data = $request_negotiate->getApproved($array);
        if ($data) {
            return $data['amount'];
        } else {
            return $this->listOne($array['post_id'])['default_fee'];
        }
    }

    public function  apiApprove($array) {
        $data = $this->listOne($array['post_id']);
        echo $this->getFee($array);

        print_r($data);
    }

    public function apiMegotiate($array, $action="post") {
        global $category;
        global $request_negotiate;
        if ($action == "post") {
            if ($array['user_id'] == $array['user_r_id']) {
                $return['status'] = "501";
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "You can not negotiate price with yourself. user_r_id must be the user ID of the advert owner if you are responding to an ad, or the user id of the user you are responding to if you own the ad";
            } else {
                $array['m_type'] = "negotiate_charges";
                $checkNegotiate['post_id'] = $array['post_id'];
                $checkNegotiate['user'] = $array['user_id'];
                $checkNegotiate['user_r'] = $array['user_r_id'];
                $requestData = $this->listOne($array['post_id']);
                $call_out_charge = $category->getSingle($requestData['category_id'], "call_out_charge");
                if (floatval($call_out_charge) <= floatval($array['fee'])) {
                    if ( $request_negotiate->checkCurrent($checkNegotiate) > 0) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "A negotiation request already sent. Please check your messages";
                    } else {
                        $add = $request_negotiate->negotiatePrize($array);

                        if ($add) {
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['additional_message'] = "Negotiation Request Sent";
                        } else {
                            $return['status'] = "500";
                            $return['message'] = "Internal Server Error";
                        }
                    }
                } else {
                    $return['status'] = "406";
                    $return['message'] = "Not Acceptable";
                    $return['additional_message'] = "The proposed fee is lower than the minimum charge for this job type.";
                }
            }
        } else if ($action == "check") {
            $checkNegotiate['post_id'] = $array['post_id'];
            $checkNegotiate['user'] = $array['user_id'];
            $checkNegotiate['user_r'] = $array['user_r_id'];
            if ( $request_negotiate->checkCurrent($checkNegotiate) > 0) {
                $return['status'] = "200";
                $return['message'] = "OK";
                $return['additional_message'] = "A negotiation request already sent. Please check your messages";
            } else {
                $return['status'] = "200";
                $return['message'] = "OK";
                $return['additional_message'] = "No negotiation request sent";
            }
        } else if ($action == "respond") {
            $get_data = $request_negotiate->listOne($array['neg_id']);
            if ($get_data) {
                if ($get_data['user_id'] != $array['user_id']) {
                    if ($array['reponse'] == "y") {
                        $data['status'] = 2;
                        $msg = "Approved";
                    } else{
                        $data['status'] = 1;
                        $msg = "Declined";
                    }
                    $data['ref'] = $array['neg_id'];
                    $data['message'] = $array['msg_id'];
                    $add = $request_negotiate->negotiateResponse($data);
                    if ($add) {
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['additional_message'] = "The negotiation request was ".$msg;
                    } else {
                        $return['status'] = "500";
                        $return['message'] = "Internal Server Error";
                    }
                } else {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "You cannot approve your own Negotiation request";
                }
            } else {
                $return['status'] = "404";
                $return['message'] = "Not Found";
                $return['additional_message'] = "Negotiation request with data with 'neg_id' ".$array['neg_id']." not found";
            }
        }

        return $return;
    }

    private function formatResult($data, $user=false, $type="list", $view=false) {
        global $messages;
        if ($data) {
            if ($type == "list") {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i] = $this->clear($data[$i]);
                }
            } else {
                if ((($data['user_id'] == $user) || ($data['client_id'] == $user)) || ($data['status'] == "ACTIVE")) {
                    $messages->markRead($user, $data['ref']);
                    $data = $this->clear($data, $user, $view);
                } else {
                    $data['error'] = true;
                    $data['error_msg'] = "This post is not available";
                }
            }
        }
        return $data;
    }

    private function clear($data, $owner=false, $view=false) {
        global $users;
        global $country;
        global $category;
        global $rating;
        global $messages;
        global $wallet;
        //get individual property
        $show = false;

        if ($owner == $data['user_id']) {
            if ($data['client_id'] == 0) {
                $user_id = $view;
            } else {
                $user_id = $data['client_id'];
            }
            $user_r_id = $data['user_id'];
            if ($owner != false) {
                $response = $messages->getResponse($data['ref'], $data['user_id']);
                for ($i = 0; $i < count($response); $i++) {
                    $user['id'] = $response[$i]['user_id'];
                    $user['name'] = $users->listOnValue($response[$i]['user_id'], "screen_name");
                    $user['rating']['score'] = round($rating->getRate($response[$i]['user_id']), 2);
                    $user['rating']['total'] = 5;
                    $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($response[$i]['user_id'])));
                    $response[$i]['user_id'] = $user;
                    unset($user);
                }
                $data['advert_data']['response'] = $response;
            }

            $regionData = $country->getLoc($data['region'], "ref");
            $f_data['amount_per_dat'] = $regionData['featured_ad'];
            $f_data['tax'] = $regionData['tax']/100;
            $f_data['currency'] = $regionData['currency'];
            $f_data['currency_symbol'] = $regionData['currency_symbol'];
            $data['is_featured'] = $f_data;
        }
        //get user ID and name
        $user['id'] = $data['user_id'];
        $user['name'] = $users->listOnValue($data['user_id'], "screen_name");
        $user['rating']['score'] = round($rating->getRate($data['user_id']), 2);
        $user['rating']['total'] = 5;
        $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($data['user_id'])));
        $data['user_id'] = $user;

        if ($data['client_id'] > 0) {

            $user['id'] = $data['client_id'];
            $user['name'] = $users->listOnValue($data['client_id'], "screen_name");
            $user['rating']['score'] = round($rating->getRate($data['client_id']), 2);
            $user['rating']['total'] = 5;
            $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($data['client_id'])));
            $data['client_id'] = $user;
        } else {
            $data['client_id'] = false;
        }
        $regionData = $country->getLoc($data['region'], "ref");
        $f_data['amount'] = $data['fee'];
        $f_data['tax'] = $regionData['tax']/100;
        $f_data['currency'] = $regionData['currency'];
        $f_data['currency_symbol'] = $regionData['currency_symbol'];
        unset($data['region']);


        $card = $wallet->listOne($data['card']);
        unset($card['user_id']);
        unset($card['gateway_token']);
        unset($card['is_default']);
        unset($card['status']);
        unset($card['create_time']);
        unset($card['modify_time']);
        unset($card['gateway_token']);
        
        $data['fee'] = $f_data;
        $data['card'] = $card;

        $category_id = $data['category_id'];
        $cat['id'] = $category_id;
        $cat['name'] = $category->getSingle($category_id);
        $data['category_id'] = $cat;
        return $data;
    }
    
    public function apiGetList($type, $page=1, $user=false, $ref=false) {
        global $options;
        if (intval($page) == 0) {
            $page = 1;
        }
        $current = intval($page)-1;
        if ($user == false) {
            $limit = $options->get("ad_per_page_mobile");
        } else {
            $limit = $options->get("result_per_page_mobile");
        }
        $start = $current*$limit;

        if (($type == "all") || ($type == "open") || ($type == "running") || ($type == "past") || ($type == "running") || ($type == "past") || ($type == "current")) {
            $result = $this->listAllData($user, $type, $start, $limit);

            $data['counts']['current_page'] = ($result['listCount'] > 0 ? $page : 0);
            $data['counts']['total_page'] = ceil($result['listCount']/$limit);
            $data['counts']['rows_on_current_page'] = count($result['list']);
            $data['counts']['max_rows_per_page'] = $limit;
            $data['counts']['total_rows'] = $result['listCount'];
            $data['data'] = $this->formatResult( $result['list'] );
        } else {
            $data = $this->formatResult( $this->listOne($type), $user, "single", $ref );

            if ($data == false) {
                $data['error'] = true;
                $data['error_msg'] = "Cannot find posting with matching criteria";
            }
        }
        if (isset($data['error'])) {
            $return['status'] = "404";
            $return['message'] = "Not Found";
            $return['additional_message'] = $data['error_msg'];
        } else {
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['data'] = $data;
        }
        return $return;
    }

    public function apiDelete($array) {
        $data = $this->listOne($array['ref']);
        if ($data) {
            if ($data['user_id'] == $array['user_id']) {
                if ($data['status'] == "OPEN") {
                    if ($this->removeDraft($array['ref'])) {
                        $return['status'] = "200";
                        $return['message'] = "OK";
                    } else {
                        $return['status'] = "500";
                        $return['message'] = "Internal Server Error";
                    }
                } else if ($data['status'] == "DELETED") {
                    $return['status'] = "404";
                    $return['message'] = "Not Found";
                    $return['additional_message'] = "Request not found";
                } else {
                    $return['status'] = "401";
                    $return['message'] = "Unauthorized";
                    $return['additional_message'] = "You are not allowed to delete this request at this stage. Cancel the request first";
                }
            } else {
                $return['status'] = "403";
                $return['message'] = "Forbidden";
                $return['additional_message'] = "You are not allowed to delete this request";
            }

        } else {
            $return['status'] = "404";
            $return['message'] = "Not Found";
            $return['additional_message'] = "Request not found";
        }
        return $return;
    }

    public function creatAPI($location, $array) {
        global $wallet;
        global $category;
        global $post;
        global $users;
        //check if card is valid
        $array['region'] = $location['ref'];
        if (isset($array['address']) && ($array['address'] != "")) {
            $addressData = $this->googleGeoLocation(false, false, $array['address']);
            $array['latitude'] = $addressData['latitude'];
            $array['longitude'] = $addressData['longitude'];
            $addressData['state_code'] = $addressData['province_code'];
            $addressData['state'] = $addressData['province'];
            $addressData['code'] = $addressData['country_code'];
        } else {
            $array['latitude'] = $addressData['latitude'] = $location['latitude'];
            $array['longitude'] = $addressData['longitude'] = $location['longitude'];
            $addressData['state_code'] = $location['state_code'];
            $addressData['state'] = $location['state'];
            $addressData['code'] = $location['code'];
            $addressData['country'] = $location['country'];
            $address = $location['address'];
        }
        $maps['latitude'] = $addressData['latitude'];
        $maps['code'] = $addressData['code'];
        $maps['longitude'] = $addressData['longitude'];
        $address = $array['address'];
        $check = $wallet->getDefault($array['user_id']);
        if ($users->listOnValue($array['user_id'], "user_type") != 1) {
            if ($check) {
                $call_out_charge = $category->getSingle($array['category_id'], "call_out_charge");
                if (floatval($call_out_charge) <= floatval($array['fee'])) {
                    if ($this->checkSize($array['media'], true) === false) {
                        unset($array['address']);
                        $returnedData = $this->create($array);
                        if ($returnedData['status'] == "ok") {
                            $post_id = $returnedData['id'];
                        
                            //$post_id = 1;
                            $result = $this->findRequest($addressData, $array['category_id']);
                            $array['address'] = $address;
                            $array['category'] = $category->formatResult($category->listOne($array['category_id']), true);
                            unset($array['media']);
                            unset($array['user_id']);
                            unset($array['category_id']);
                            unset($array['region']);
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['request']['ID'] = $post_id;
                            $return['request']['data'] = $array;
                            $return['counts']['current_page'] = 1;
                            $return['counts']['total_page'] = ceil($result['count']/20);
                            $return['counts']['rows_on_current_page'] = count($result['data']);
                            $return['counts']['max_rows_per_page'] = 29;
                            $return['counts']['total_rows'] = $result['count'];
                            $return['data'] = $post->formatResult( $result['data'], $maps);
                        } else if ($returnedData['status'] == "failed") {
                            $return['status'] = "406";
                            $return['message'] = "Not Acceptable";
                            $return['additional_message'] = $returnedData['msg'];
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "This request could not be completed at this time, please try again";
                        }
                    } else {
                        $return['status'] = "415";
                        $return['message'] = "Unsupported Media Type";
                        $return['additional_message'] = "One or more files uploaded is larger than 2MB";
                    }
                } else {
                    $return['status'] = "406";
                    $return['message'] = "Not Acceptable";
                    $return['additional_message'] = "The proposed fee is lower than the minimum charge for this job type.";
                }
            } else  {
                $return['status'] = "403";
                $return['message'] = "Forbidden";
                $return['additional_message'] = "You must have at least one payment card saved to make a request.";
            }
        } else  {
            $return['status'] = "403";
            $return['message'] = "Forbidden";
            $return['additional_message'] = "You cannot make a request as a service provider.";
        }
        return $return;
    }

    public function apiMessages($array, $action="list") {
        global $messages;
        global $users;
        global $rating;
        if ($action == "post") {
            $array['m_type'] = "text";
            if ((intval($array['user_r_id']) > 0) && ($array['user_r_id'] != $array['user_id'])) {
                $id = $messages->add($array);
                if ($id) {
                    $return['status'] = "200";
                    $return['message'] = "OK";
                } else {
                    $return['status'] = "500";
                    $return['message'] = "Internal Server Error";
                }
            } else {
                $return['status'] = "501";
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "Responder ID must be defined in POST as user_r_id and must not be the same as the logged in user";
            }

        } else {
            $data = $this->listOne($array['post_id']);
            if ($data) {
                if ($array['user_id'] != $data['user_id']) {
                    $user_r = $data['user_id'];
                    $user_id = $array['user_id'];
                } else {
                    $user_r = $array['user_r_id'];
                    $user_id = $data['user_id'];
                }
                
                if (($array['user_id'] == $data['user_id']) && (intval($user_r) < 1)) {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "Responder ID must be defined in URL after the ad. ID";
                } else {
                    if ($action == "list") {
                        $initialComment = $messages->getPage($array['post_id'], $user_r, $user_id);
                        for ($i = 0; $i < count($initialComment); $i++) {
                            $user['id'] = $initialComment[$i]['user_id'];
                            $user['name'] = $users->listOnValue($initialComment[$i]['user_id'], "screen_name");
                            $user['rating']['score'] = round($rating->getRate($initialComment[$i]['user_id']), 2);
                            $user['rating']['total'] = 5;
                            $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($initialComment[$i]['user_id'])));
                            $initialComment[$i]['user_id'] = $user;
                            unset($user);
                            $user['id'] = $initialComment[$i]['user_r_id'];
                            $user['name'] = $users->listOnValue($initialComment[$i]['user_r_id'], "screen_name");
                            $user['rating']['score'] = round($rating->getRate($initialComment[$i]['user_r_id']), 2);
                            $user['rating']['total'] = 5;
                            $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($initialComment[$i]['user_r_id'])));
                            $initialComment[$i]['user_r_id'] = $user;
                            if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                                $r_data = explode("_", $initialComment[$i]['m_type_data']);
                                $re_data['value'] = $r_data[0];
                                $re_data['request_id'] = $r_data[1];
                                $initialComment[$i]['m_type_data'] = $re_data;
                            }

                            unset($user);

                            unset($initialComment[$i]['post_id']);
                            unset($initialComment[$i]['status']);
                            unset($initialComment[$i]['modify_time']);
                        }
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['data'] = $initialComment;

                    } else if ($action == "new") {
                        $initialComment = $messages->getLast($array['post_id'], $user_id, $user_r);
                        $user['id'] = $initialComment['user_id'];
                        $user['name'] = $users->listOnValue($initialComment['user_id'], "screen_name");
                        $user['rating']['score'] = round($rating->getRate($initialComment['user_id']), 2);
                        $user['rating']['total'] = 5;
                        $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($initialComment['user_id'])));
                        $initialComment['user_id'] = $user;
                        unset($user);
                        $user['id'] = $initialComment['user_r_id'];
                        $user['name'] = $users->listOnValue($initialComment['user_r_id'], "screen_name");
                        $user['rating']['score'] = round($rating->getRate($initialComment['user_r_id']), 2);
                        $user['rating']['total'] = 5;
                        $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($initialComment['user_r_id'])));
                        $initialComment['user_r_id'] = $user;
                        if ($initialComment['m_type'] == "negotiate_charges" ) {
                            $r_data = explode("_", $initialComment['m_type_data']);
                            $re_data['value'] = $r_data[0];
                            $re_data['request_id'] = $r_data[1];
                            $initialComment['m_type_data'] = $re_data;
                        }

                        unset($user);

                        unset($initialComment['post_id']);
                        unset($initialComment['status']);
                        unset($initialComment['modify_time']);

                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['data'] = $initialComment;
                    }
                }
            } else {
                $return['status'] = "404";
                $return['message'] = "Not Found";
                $return['additional_message'] = "Invalid Ad or Posting";
            }
        }
        return $return;
    }

    private function getSortedMessageList($user, $start, $limit, $type="list") {
        $query = "SELECT `post_id` AS `ref`, `user_id`, `user_r_id` FROM `messages` WHERE (`user_id` = :user OR `user_r_id` = :user) GROUP BY `post_id` ORDER BY `modify_time` DESC";
        
        if ($type == "list") {
            $query .= " LIMIT ".$start.", ".$limit;;
        }

        $prepare[':user'] = $user;

        return $this->query($query, $prepare,  $type);
    }

    public function listProjectMessage($user, $start, $limit) {
        $return['list'] = $this->getSortedMessageList($user, $start, $limit);
        $return['listCount'] = $this->getSortedMessageList($user, false, false, "count");

        return $return;
    }

    private function formatMessage($data, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->cleanMessage($data[$i]);
            }
        } else {
            $data = $this->cleanMessage($data);
        }
        return $data;
    }

    private function cleanMessage($data) {
        global $users;
        global $rating;
        
        //get user ID and name
        $user['id'] = $data['user_id'];
        $user['name'] = $users->listOnValue($data['user_id'], "screen_name");
        $user['rating']['score'] = round($rating->getRate($data['user_id']), 2);
        $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($data['user_id'])));
        $user['rating']['total'] = 5;
        $data['user_id'] = $user;

        //get user ID and name
        $user['id'] = $data['user_r_id'];
        $user['name'] = $users->listOnValue($data['user_r_id'], "screen_name");
        $user['rating']['score'] = round($rating->getRate($data['user_r_id']), 2);
        $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($data['user_r_id'])));
        $user['rating']['total'] = 5;
        $data['user_r_id'] = $user;

        $data['is_new'] = true;
        return $data;
    }

    public function apiMessageList($user, $page) {
        global $options;
        if (intval($page) == 0) {
            $page = 1;
        }
        $current = intval($page)-1;
        $limit = $options->get("result_per_page_mobile");
        $start = $current*$limit;
        $result = $this->listProjectMessage($user, $start, $limit);
            
        $return['status'] = "200";
        $return['message'] = "OK";
        $return['counts']['current_page'] = $page;
        $return['counts']['total_page'] = ceil($result['listCount']/$limit);
        $return['counts']['rows_on_current_page'] = count($result['list']);
        $return['counts']['max_rows_per_page'] = $limit;
        $return['counts']['total_rows'] = $result['listCount'];
        $return['data'] = $this->formatMessage( $result['list'] );

        return $return;
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`request` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `user_id` INT NOT NULL, 
            `client_id` INT NOT NULL, 
            `category_id` INT NOT NULL, 
            `fee` DOUBLE NOT NULL, 
            `time` VARCHAR(50) NOT NULL,
            `description` VARCHAR(500) NOT NULL,
            `region` INT NOT NULL, 
            `card` INT NOT NULL, 
            `tx_id` INT NOT NULL,
            `latitude` DOUBLE NOT NULL,
            `longitude` DOUBLE NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'OPEN',
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`request`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`request`";

        $this->query($query);
    }
}
include_once("request_negotiate.php");
$request_negotiate  = new request_negotiate;
?>