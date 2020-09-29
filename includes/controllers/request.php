<?php
class request extends database {
    private $user_id;
    public function create($array) {
        global $media;
        if (isset($array['web'])) {
            $web = true;
            unset($array['web']);
            $error = ' <a href="'.URL.'"paymentCards/create">Click here to add a new payment card</a> ';
        } else {
            $web = false;
            $error = ' Add a new payment card, ';
        }
        if (isset($array['media'])) {
            $mediaFiles = $array['media'];
            unset($array['media']);
        }

        $location['state'] = $array['state'];
        $location['state_code'] = $array['state'];
        $location['country'] = $array['country'];
        $location['code'] = $array['country_code'];
        $location['latitude'] = $array['latitude'];
        $location['longitude'] = $array['longitude'];
        
        unset($array['state_code']);
        unset($array['state']);
        unset($array['country']);
        unset($array['country_code']);

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
                //send request out
                $this->broadcast($array['category_id'], $create, $location);
                return array("status" => "ok", "id" => $create);
            } else if ($paymentData['status'] == "FAILED") {
                $this->clean($create, $paymentData['tx_id']);
                if ($paymentData['type'] == "card") {
                    return array("status" => "failed", "msg" => "You must have at least one payment card saved to make a request.".$error."then come back to create the request again");
                }

                return array("status" => "failed", "msg" => "your payment card could not be processed and you do not have enough balance in your wallet to initiate this request");
            }

        } else {
            return false;
        }
    }

    private function broadcast($category_id, $request, $location) {
        global $category;
        global $notifications;
        global $users;
        $dataUser = $category->totalUsers($category_id, $location);

        $requestData = $this->listOne($request);

        for ($i = 0; $i < count($dataUser); $i++) {
            $array['event'] = "handle_request";
            $array['event_id'] = $request;
            $array['user_id'] = $dataUser[$i]['user_id'];
            $array['message'] = $request;

            $name = $users->listOnValue($requestData['user_id'], "screen_name");

            $array['message'] = "New work Request from ".$name;
            $array['email'] = $name." needs a ".$category->getSingle($category_id)." at ".$requestData['address'].". Click <a href='".$this->seo($request, "request", $requestData['user_id'])."'>here</a> to notify ".$name." of your interest";

            if ($notifications->create($array) ) {
                $requestDataField['ref'] = $requestData['ref'];
                $requestDataField['charge'] = $requestData['fee'];
                $requestDataField['duration'] = "";
                $requestDataField['categoryName'] = $category->getSingle( $requestData['category_id'] );
                $requestDataField['location'] = $requestData['address'];

                $msg = $array['message'];
                $data["to"] = $dataUser[$i]['user_id'];
                $data["title"] = "Request Notification";
                $data["body"] = $msg;
                $data['data']['page_name'] = "handle_request";
                $data['data']['request'] = $requestDataField;
                $notifications->sendPush($data);

                //send email after
                $msg .= ". <a href='".URL."?page_name=handle_request&ID=".$requestData['post_id']."&request=".json_encode($requestDataField)."&main_request'>Click here</a> to continue review this request";

                $dataEmail = $users->listOne($dataUser[$i]['user_id'], "ref");
                
                $client = $dataEmail['last_name']." ".$dataEmail['other_names'];
                $subjectToClient = "Request Notification";
                $contact = "MOBA <".replyMail.">";
                
                $fields = 'subject='.urlencode($subjectToClient).
                    '&last_name='.urlencode($dataEmail['last_name']).
                    '&other_names='.urlencode($dataEmail['other_names']).
                    '&email='.urlencode($dataEmail['email']).
                    '&tag='.urlencode($msg);
                $mailUrl = URL."includes/views/emails/notification.php?".$fields;
                $messageToClient = $this->curl_file_get_contents($mailUrl);
                
                $mail['from'] = $contact;
                $mail['to'] = $client." <".$dataEmail['email'].">";
                $mail['subject'] = $subjectToClient;
                $mail['body'] = $messageToClient;
                
                global $alerts;
                $alerts->sendEmail($mail);
            }
        }
    }

    private function clean($id, $tx_id) {
        global $media;
        $this->delete("request", $id);
        $this->delete("transactions", $tx_id);

        $media->remove($id);
    }

    function remove($id) {
        global $media;
        global $transactions;

        $tx = $transactions->getSortedListTrans($id, "tx_type_id", "tx_type", "Request", false, false, "ref", "DESC", "AND", false, false, "getRow");

        $this->delete("transactions", $tx['ref']);
        $this->delete("wallet", $tx['ref'], "tx_id");

        $this->updateOne("request", "status", "DELETED", $id, "ref");
        $media->remove($id);

        return true;
    }

    function getSingle($name, $tag, $ref="ref") {
        return $this->getOneField("request", $name, $ref, $tag);
    }

    function listOne($id) {
        return $this->getOne("request", $id, "ref");
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
        return $this->sortAll("request", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
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

    public function taskCompleted($id, $type="client_id") {
        return $this->getSortedList($id, $type, "status", "COMPLETED", false, false, "modify_time", "DESC", "AND", false, false, "count");
    }

    public function listAllData($ref, $view, $start, $limit, $location) {
        if ($view == "open") {
            $return['list'] = $this->getList($start, $limit, "modify_time", "DESC", "`status` = 'OPEN' AND `user_id` = ".$ref);
            $return['listCount'] = intval($this->getList(false, false, "modify_time", "DESC", "`status` = 'OPEN' AND `user_id` = ".$ref, "count"));
            $return['tag'] = "All Open Request";
        } else if ($view == "running") {
            $return['list'] = $this->getList($start, $limit, "modify_time", "DESC", "`status` = 'ACTIVE' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")");
            $return['listCount'] = intval($this->getList(false, false, "modify_time", "DESC", "`status` = 'ACTIVE' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")", "count"));
            $return['tag'] = "All Active Request";
        } else if ($view == "current") {
            $return['list'] = $this->getList($start, $limit, "modify_time", "DESC", "`status` = 'OPEN' AND `user_id` != ".$ref." AND `ref` IN (SELECT `post_id` FROM `messages` WHERE `user_id` = ".$ref." OR `user_r_id` = ".$ref." )");
            $return['listCount'] = intval($this->getList(false, false, "modify_time", "DESC", "`status` = 'OPEN' AND `user_id` != ".$ref." AND `ref` IN (SELECT `post_id` FROM `messages` WHERE `user_id` = ".$ref." OR `user_r_id` = ".$ref." )", "count"));
            $return['tag'] = "Current Interests.";
        } else if ($view == "available") {
            $return = $this->getActiveRequest($ref, $location['longitude'], $location['latitude'], $location['country'], $start, $limit);
            $return['tag'] = "All Currently Available Jobs";
        } else if ($view == "past") {
            $return['list'] = $this->getList($start, $limit, "modify_time", "DESC", "`status` = 'COMPLETED' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")");
            $return['listCount'] = intval($this->getList(false, false, "modify_time", "DESC", "`status` = 'COMPLETED' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")", "count"));
            $return['tag'] = "All Completed Request";
        } else {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", " (`user_id` = ".$ref." OR `client_id` = ".$ref.") AND `status` != 'DELETED'");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", " (`user_id` = ".$ref." OR `client_id` = ".$ref.") AND `status` != 'DELETED'", "count"));
            $return['tag'] = "All Requests";
        }

        return $return;
    }

    public function listAllAdminData($view, $start, $limit) {
        if ($view == "open") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'OPEN'");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", "`status` = 'OPEN'", "count"));
            $return['tag'] = "All Open Request";
        } else if ($view == "running") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'ACTIVE'");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", "`status` = 'ACTIVE'", "count"));
            $return['tag'] = "All Active Request";
        } else if ($view == "past") {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'COMPLETED'");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", "`status` = 'COMPLETED'", "count"));
            $return['tag'] = "All Completed Request";
        } else {
            $return['list'] = $this->getList($start, $limit, "ref", "DESC", " `status` != 'DELETED'");
            $return['listCount'] = intval($this->getList(false, false, "ref", "DESC", " `status` != 'DELETED'", "count"));
            $return['tag'] = "All Requests";
        }

        return $return;
    }

    public function getActiveRequest($ref, $longitude, $latitude, $country, $start, $limit) {        
        $sql = "SELECT `request`.`ref`,`request`.`user_id`,`request`.`client_id`,`request`.`category_id`,`request`.`fee`,`request`.`address`,`request`.`region`,`request`.`card`,`request`.`tx_id`,`request`.`latitude`,`request`.`longitude`,`request`.`data`,`request`.`status`,`request`.`start_date`,`request`.`end_date`,`request`.`review_status`,`request`.`client_rate`,`request`.`review_status_time`,`request`.`user_rate`,`notifications`.`create_time`,`notifications`.`modify_time`, SQRT(((`request`.`latitude` - ".$latitude.")*(`request`.`latitude` - ".$latitude.")) + ((`request`.`longitude` - ".$longitude.")*(`request`.`longitude` - ".$longitude."))) AS `total` FROM `request` INNER JOIN `notifications` ON request.ref = `notifications`.event_id AND request.`status` = 'OPEN' AND notifications.`event` = 'handle_request' AND `notifications`.`user_id` = ".$ref." ORDER BY `total` ASC";

        $return['list'] = $this->query($sql." LIMIT ".$start.",".$limit, false, "list");
        $return['listCount'] = $this->query($sql, false, "count");

        return $return;
    }

    public function removeDraft($id) {
        global $request_accept;
        global $notifications;
        $rem = $this->remove($id);

        if ($rem) {
            //remove notifications
            $notifications->removeRequest($id);
            //remove request
            $request_accept->remove($id, "post_id");
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
            $check = $wallet->getDefault($data['user_id']);
            if ($check) {
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
                $pay_gateway['tx_id'] = $tx_id;
                //try all the cards until one goes
                for ($j = 0; $j < count($list); $j++) {
                    $pay_gateway['card'] = $list[$j]['ref'];
                    //get balance from gateway
                    $makePayment = $transactions->processPay($pay_gateway);
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
                return array("status" => "FAILED", "tx_id" => 0, "type" => "card");
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
            $tx_wallet['amount'] = 0-($fee+$tax);
            $tx_wallet['status'] = 0;
            $wallet->createWallet($tx_wallet);


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
            
            $tag = "We could not approve payment for this request. Please make sure your MOBA wallet is funded with a minimum of ".$regionData['currency_symbol'].number_format($remainder, 2).". <a href='".URL."wallet'>Sign in</a> to your MOBA Account to learn more";;

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

    public function getFee($array) {
        global $request_negotiate;
        $data = $request_negotiate->getApproved($array);
        if ($data) {
            return $data['amount'];
        } else {
            return $this->listOne($array['post_id'])['fee'];
        }
    }

    private function getTransaction($id) {
        global $wallet;
        $data = $this->listOne($id);

        //get the wallet entry for the post
        $user_transaction = $wallet->getSortedListWallet($data['ref'], "ref_id", "tx_desc", "Work Payment", "status", "0", "ref", "DESC", "AND", false, false, "getRow");

        if ($this->updateOne("wallet", "status", 1, $user_transaction['ref'], "ref")) {
            $tx_wallet['user_id'] = $data['client_id'];
            $tx_wallet['tx_id'] = $data['tx_id'];
            $tx_wallet['ref_id'] = $data['ref'];
            $tx_wallet['tx_desc'] = "Settlement for Work";
            $tx_wallet['tx_dir'] = "CR";
            $tx_wallet['region'] = $data['region'];
            $tx_wallet['amount'] = abs($user_transaction['amount']);
            $tx_wallet['status'] = 1;
            $wallet->createWallet($tx_wallet);

            $fee = $this->getFee(array("post_id"=>$data['ref'], "user"=>$data['user_id'], "user_r"=>$data['client_id']));

            $serviceCharge = $this->getCharges($fee);

            $tx_pay['user_id'] = 0;
            $tx_pay['tx_type_id'] = $data['tx_id'];
            $tx_pay['tx_type'] = "service_charge";
            $tx_pay['tx_dir'] = "CR";
            $tx_pay['card'] = 0;
            $tx_pay['region'] = $data['region'];
            $tx_pay['net_total'] = $serviceCharge;
            $tx_pay['tax_total'] = 0;
            $tx_pay['gross_total'] = $serviceCharge;
            $tx_pay['gateway_status'] = "Approved";
            $tx_pay['status'] = 2;
            $wallet->createTx($tx_pay);

            $tx_wallet['user_id'] = $data['client_id'];
            $tx_wallet['tx_id'] = $data['tx_id'];
            $tx_wallet['ref_id'] = 0;
            $tx_wallet['tx_desc'] = "Work Payment Service Charge";
            $tx_wallet['tx_dir'] = "DR";
            $tx_wallet['region'] = $data['region'];
            $tx_wallet['amount'] = 0-($serviceCharge);
            $tx_wallet['status'] = 1;
            $wallet->createWallet($tx_wallet);
            return true;
        } else {
            return false;
        }
    }

    public function approve($id, $action, $user, $admin=false) {
        global $messages;
        global $users;
        global $options;
        global $alerts;
        global $notifications;
        $data = $this->listOne($id);

        if ($data['user_id'] == $user) {
            $user_id = $data['user_id'];
            $user_r_id = $data['client_id'];
        } else if ($data['client_id'] == $user) {
            $user_id = $data['client_id'];
            $user_r_id = $data['user_id'];
        } else if ($admin == true) {
            $user_id = $data['user_id'];
            $user_r_id = $data['client_id'];
        }

        if ((isset($user_id)) || ($admin == true)) {
            if ($action == "approve") {
                if ($this->getTransaction($id)) {
                    $this->updateOneRow("review_status", 0, $data['ref']);
                    $this->updateOneRow("review_status_time", NULL, $data['ref']);
                    $this->updateOneRow("status", "COMPLETED", $data['ref']);
                    $this->updateOneRow("end_date", time(), $data['ref']);

                    $tag = "you have marked this task as done. Payment has been sent. <a href='".URL."ads\archive'>Sign in</a> to your MOBA Account to learn more";

                    $user_data = $users->listOne($data['user_id']);
                    $client = $user_data['last_name']." ".$user_data['other_names'];
                    $subjectToClient = "Job with ".$users->listOnValue($data['client_id'], "screen_name")." Completed";
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
                    
                    $alerts->sendEmail($mail);

                    $msg = "Job with ".$users->listOnValue($data['user_id'], "screen_name")." Completed";
                    $msgArray['message']  = $msg;
                    $msgArray['user_r_id']  = $data['client_id'];
                    $msgArray['user_id']  = $data['user_id'];
                    $msgArray['post_id']  = $data['ref'];
                    $msgArray['m_type']  = "system";
                    $messages->add($msgArray);

                    $tag = "This task has been approve and the payment is now available in your wallet. <a href='".URL."ads\past'>Sign in</a> to your MOBA Account to learn more";

                    $user_data = $users->listOne($data['client_id']);
                    $client = $user_data['last_name']." ".$user_data['other_names'];
                    $subjectToClient = "Job with ".$users->listOnValue($data['user_id'], "screen_name")." Completed";
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
                    
                    $alerts->sendEmail($mail);

                    $msg = "Job with ".$users->listOnValue($data['user_id'], "screen_name")." Completed";

                    $msgArray['message']  = $msg;
                    $msgArray['user_r_id']  = $data['user_id'];
                    $msgArray['user_id']  = $data['client_id'];
                    $msgArray['post_id']  = $data['ref'];
                    $msgArray['m_type']  = "system";
                    $messages->add($msgArray);

                    return true;
                } else {
                    return false;
                }
            } else if ($action == "request_approve") {
                if ($this->updateOneRow("review_status", 1, $data['ref'])) {
                    $this->updateOneRow("review_status_time", (time()+(60*60*24*$options->get("max_days_approve"))), $data['ref']);
                    
                    $tag = "Your attention is required in. This task as been marked as completed and is awaiting your review. If no action is taken by you in ".$options->get("max_days_approve")." ".$this->addS("day", $options->get("max_days_approve")).", this task will be marked as completed and all outstanding payments will be released. <a href='".URL."ads/on-going'>Sign in</a> to your MOBA Account to learn more";;

                    $user_data = $users->listOne($data['user_id']);
                    $client = $user_data['last_name']." ".$user_data['other_names'];
                    $subjectToClient = "[ACTION REQUIRED]: Task Awaiting Review";
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

                    $msg = "Your attention is required, i have marked this task as completed and it is awaiting your review";
                    $msgArray['message']  = $msg;
                    $msgArray['user_r_id']  = $user_r_id;
                    $msgArray['user_id']  = $user_id;
                    $msgArray['post_id']  = $data['ref'];
                    $msgArray['m_type']  = "system";
                    $messages->add($msgArray);

                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public function apiComplete($array, $action="complete") {
        $data = $this->listOne($array['post_id']);
        if ($data) {
            $ref = $array['post_id'];

            if ($action == "complete") {
                if (($data['client_id'] == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "You can not perform this action, you can only request that the ownser cofirms the job";
                } else if (($data['user_id'] == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                    $add = $this->approve($ref, "approve", $data['user_id']);
                    if ($add) {
                        $return['status'] = "200";
                        $return['message'] = "OK";
                    } else {
                        $return['status'] = "500";
                        $return['message'] = "Internal Server Error";
                    }
                } else {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "You can not allowed to perform this action";
                }
            } else if ($action == "alert") {
                if (($data['user_id'] == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "You can not perform this request, you can only approve the Job";
                } else if (($data['client_id'] == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                        $add = $this->approve($ref, "request_approve", $data['client_id']);
                        $add = true;
                        if ($add) {
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['additional_message'] = "Approval request sent";
                        } else {
                            $return['status'] = "500";
                            $return['message'] = "Internal Server Error";
                        }
                } else {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "You can not allowed to perform this action";
                }
            }
        } else {
            $return['status'] = "404";
            $return['message'] = "Not Found";
            $return['additional_message'] = "Invalid Ad or Posting";
        }
        return $return;
    }

    public function apiReview($array, $action) {
        global $rating;
        global $rating_question;
        global $rating_comment;
        $array['post_id'] = $array['project_id'];
        $data = $this->listOne($array['post_id']);

        if ($data) {
            $allow = false;
            if ($data['user_id'] == $array['user_id']) {
                $tagline = "user_id";
                $user_id = $data['client_id'];
                $user_r_id = $data['user_id'];
                $rateType = "vendors";
                $allow = true;
            } else {
                $tagline = "client_id";
                $user_id = $data['user_id'];
                $user_r_id = $data['client_id'];
                $rateType = "clients";
                $allow = true;
            }
            if ($allow) {
                if ($data['status'] == "COMPLETED") {
                    if ($action == "list") {
                        $rateQuestion = $rating_question->getSortedList($rateType, "question_type");
                        for ($i = 0; $i < count($rateQuestion); $i++) {
                            unset($rateQuestion[$i]['question_type']);
                            unset($rateQuestion[$i]['status']);
                            unset($rateQuestion[$i]['create_time']);
                            unset($rateQuestion[$i]['modify_time']);
                        }
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['data'] = $rateQuestion;
                    } else  if ($action == "get") {
                        $checkRate = $rating->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "post_id", $array['post_id']);
                        $checkComment = $rating_comment->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "post_id", $array['post_id'], "ref", "ASC", "AND", false, false, "getRow");
                        if (count($checkRate) > 0) {
                            for ($i = 0; $i < count($checkRate); $i++) {
                                $returnData[$i]['question_ref'] = $checkRate[$i]['question_id'];
                                $returnData[$i]['question'] = $rating_question->getSingle( $checkRate[$i]['question_id'] );
                                $returnData[$i]['rate_score'] = $checkRate[$i]['review'];
                                $returnData[$i]['total_score'] = 5;
                            }
                            $returnData['comment'] = $checkComment['comment'];
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['data'] = $returnData;
                        } else {
                            $return['status'] = "404";
                            $return['message'] = "Not Found";
                            $return['additional_message'] = "No ratings and review for this Post yet";
                        }
                    }  else  if ($action == "post") {
                        $checkRate = $rating->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "post_id", $array['post_id']);
                        if (count($checkRate) == 0) {
                            for ($i = 0; $i < count($array['rating']); $i++) {
                                $list['rating'][$array['rating'][$i]['question_id']] = $array['rating'][$i]['score'];
                            }
                            $list['user_id'] = $user_id;
                            $list['reviewed_by'] = $user_r_id;
                            $list['post_id'] = $array['post_id'];
                            $list['type'] = $tagline;
                            $list['comment'] = $array['comment'];

                            $add = $rating->addRate($list);
                            if ($add) {
                                $return['status'] = "200";
                                $return['message'] = "OK";
                            } else {
                                $return['status'] = "500";
                                $return['message'] = "Internal Server Error";
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "This post has been rated by you previously";
                        }
                    }
                } else {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "You can not rate or view reviews for this Ad or Post at this time ";
                }
            } else {
                $return['status'] = "501";
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "This user does not have access to this project";
            }
        } else {
            $return['status'] = "404";
            $return['message'] = "Not Found";
            $return['additional_message'] = "Invalid Ad or Posting";
        }
        return $return;
    }

    public function  apiApprove($array) {
        global $wallet;
        global $country;
        global $notifications;
        global $users;
        global $messages;
        global $request_accept;

        $data = $this->listOne($array['post_id']);
        $fee = $this->getFee($array);

        $regionData = $country->getLoc($data['region'], "ref");

        $trans = $wallet->getSortedListWallet($data['tx_id'], "tx_id", "tx_desc", "Work Payment", false, false, "ref", "DESC", "AND", false, false, "getRow");

        $complete = false;

        // get tax
        $regionData = $country->getLoc($data['region'], "ref");
        $tax = $fee*($regionData['tax']/100);

        $totalFee = $fee+$tax;

        if ($totalFee != abs($trans['amount'])) {
            $remainder = $fee - abs($trans['amount']);

            if ($remainder > 0) {
                $tax = $data['fee']*($regionData['tax']/100);
                $fee = $remainder;
                $list = $wallet->getSortedList($data['user_id'], "user_id");
                $tx_pay['user_id'] = $data['user_id'];
                $tx_pay['tx_type_id'] = $array['post_id'];
                $tx_pay['tx_type'] = "request";
                $tx_pay['tx_dir'] = "DR";
                $tx_pay['card'] = 0;
                $tx_pay['region'] = $data['region'];
                $tx_pay['net_total'] = $fee;
                $tx_pay['tax_total'] = $tax;
                $tx_pay['gross_total'] = $fee+$tax;
                $tx_id = $wallet->createTx($tx_pay);

                $pay_gateway['gross_total'] = $remainder;
                $pay_gateway['tx_id'] = $tx_id;
                //try all the cards until one goes
                for ($j = 0; $j < count($list); $j++) {
                    $pay_gateway['card'] = $list[$j]['ref'];
                    //get balance from gateway
                    $makePayment = $wallet->processPay($pay_gateway);
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
                        $tx_wallet['amount'] = 0-$fee;
                        $tx_wallet['status'] = 1;
                        $wallet->createWallet($tx_wallet);
                        $this->updateOneRow("card", $pay_gateway['card'], $data['ref']);
                        $complete = true;
                        break;
                    }
                }

                if ($complete == true) {
                    $newAmt = 0-$fee;

                    $wallet->updateOneRow("amount", $newAmt, $trans['ref']);
                    $wallet->updateOneRow("ref_id", $data['ref'], $trans['ref']);
                    $wallet->updateOneRow("ref_id", $data['ref'], $trans['ref']);
                }

            } else if ($remainder < 0) {
                $newAmt = 0-$fee;
                $wallet->updateOneRow("amount", $newAmt, $trans['ref']);
                $wallet->updateOneRow("ref_id", $data['ref'], $trans['ref']);

                $tx_pay['user_id'] = $data['user_id'];
                $tx_pay['tx_type_id'] = $array['post_id'];
                $tx_pay['tx_type'] = "request_refund";
                $tx_pay['tx_dir'] = "CR";
                $tx_pay['card'] = 0;
                $tx_pay['status'] = 2;
                $tx_pay['region'] = $data['region'];
                $tx_pay['net_total'] = abs( $remainder );
                $tx_pay['tax_total'] = 0;
                $tx_pay['gross_total'] = abs( $remainder );
                $tx_id = $wallet->createTx($tx_pay);


                $tx_wallet['user_id'] = $data['user_id'];
                $tx_wallet['tx_id'] = $tx_id;
                $tx_wallet['ref_id'] = 0;
                $tx_wallet['tx_desc'] = "CR transaction in wallet";
                $tx_wallet['tx_dir'] = "CR";
                $tx_wallet['region'] = $data['region'];
                $tx_wallet['amount'] = abs( $remainder );
                $tx_wallet['status'] = 1;
                $wallet->createWallet($tx_wallet);

                $complete = true;
            }
        } else {
            $wallet->updateOneRow("ref_id", $data['ref'], $trans['ref']);
            $complete = true;
        }

        $complete = true;

        if ($complete === true) {
            $this->updateOne("request", "start_date", time(), $data['ref'], "ref");
            $this->updateOne("request", "status", "ACTIVE", $data['ref'], "ref");
            $this->updateOne("request", "fee", $this->getFee($array), $data['ref'], "ref");
            $this->updateOne("request", "client_id", $array['user_r_id'], $data['ref'], "ref");


            //remove notifications
            $notifications->removeRequest($data['ref']);
            //remove request
            $request_accept->remove($data['ref'], "post_id");

            $user_data = $users->listOne($data['client_id']);

            $tag = $user_data['screen_name']." has approve your task wit them at ".$data['address']." <a href='".URL."ads/on-going'>Sign in</a> to your MOBA Account to learn more";;

            $client = $user_data['last_name']." ".$user_data['other_names'];
            $subjectToClient = "[ACTION REQUIRED]: Job Approved and Assigned To You";
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

            $msg = "You have been assigned a task";
            $msgArray['message']  = $msg;
            $msgArray['user_id']  = $array['user_id'];
            $msgArray['user_r_id']  = $array['user_r_id'];
            $msgArray['post_id']  = $data['ref'];
            $msgArray['m_type']  = "system";
            $messages->add($msgArray);

            $return['status'] = "200";
            $return['message'] = "OK";
            $return['additional_message'] = "Approved";
            $return['request']['ID'] = $data['ref'];
        } else {
            $return['status'] = "501";
            $return['message'] = "Not Implemented";
            $return['additional_message'] = "Your wallet balance is not enough to  authorize this transaction. Please confirm your wallet and payment cards";
        }

        return $return;
    }

    public function apiMegotiate($array, $action="post") {
        global $category;
        global $request_negotiate;
        $array['fee'] = $array['amount'] =  $array['negotiated_fee'];
        if ($action == "post") {
            unset($array['fee']);
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
                    if ($array['response'] == "y") {
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

    public function apiRespond($array) {
        global $request_accept;
        
        $get_data = $this->listOne($array['request']);

        if (($get_data) && ($get_data['status'] == "OPEN")) {
            if ($get_data['user_id'] != $array['user_r_id']) {
                $add['user_r_id'] = $array['user_r_id'];
                $add['request'] = $array['request'];
                
                if ($array['response'] == "y") {
                    $msg = "accepted";
                    $request_accept->requestResponse($add);
                } else{
                    $msg = "rejected";
                    $request_accept->reject($add);
                }
                
                if ($add) {
                    $return['status'] = 200;
                    $return['message'] = "OK";
                    $return['additional_message'] = "The request was ".$msg;
                } else {
                    $return['status'] = 500;
                    $return['message'] = "Internal Server Error";
                }
            } else {
                $return['status'] = 501;
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "You cannot approve your own request";
            }
        } else {
            $return['status'] = 404;
            $return['message'] = "Not Found";
            $return['additional_message'] = "request with id ".$array['neg_id']." not open";
        }

        return $return;
    }

    private function formatResult($data, $user=false, $type="list", $view=false, $location=false) {
        global $messages;
        if ($data) {
            if ($type == "list") {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i] = $this->clear($data[$i], false, $view, $location);
                }
            } else {
                if ((($data['user_id'] == $user) || ($data['client_id'] == $user)) || ($data['status'] == "ACTIVE")) {
                    $messages->markRead($user, $data['ref']);
                    $data = $this->clear($data, $user, $view, $location);
                } else {
                    $data['error'] = true;
                    $data['error_msg'] = "This post is not available";
                }
            }
        }
        return $data;
    }

    private function clear($data, $owner=false, $view=false, $location) {
        global $users;
        global $country;
        global $category;
        global $rating;
        global $messages;
        global $wallet;
        global $post;
        //get individual property
        $show = false;

        $data['review_status'] = intval($data['review_status']);
        $data['client_rate'] = intval($data['client_rate']);
        $data['user_rate'] = intval($data['user_rate']);

        if ($owner == $data['user_id']) {
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

        if (($view === false) && ($data['status'] == "OPEN")) {
            $addressData['latitude'] = $data['latitude'];
            $addressData['longitude'] = $data['longitude'];
            $addressData['state_code'] = $location['state_code'];
            $addressData['state'] = $location['state'];
            $addressData['code'] = $location['code'];
            $addressData['country'] = $location['country'];

            $maps['latitude'] = $addressData['latitude'];
            $maps['code'] = $addressData['code'];
            $maps['longitude'] = $addressData['longitude'];
            
            $result = $this->findRequest($addressData, $data['category_id']);
            $data['service_provider']['counts']['current_page'] = 1;
            $data['service_provider']['counts']['total_page'] = ceil($result['count']/20);
            $data['service_provider']['counts']['rows_on_current_page'] = count($result['data']);
            $data['service_provider']['counts']['max_rows_per_page'] = 29;
            $data['service_provider']['counts']['total_rows'] = $result['count'];

            $data['service_provider']['data'] = $post->formatResults( $result['data'], $maps);
        }
        
        unset($data['region']);


        $card = $wallet->listOne($data['card']);
        unset($card['user_id']);
        unset($card['gateway_token']);
        unset($card['is_default']);
        unset($card['status']);
        unset($card['create_time']);
        unset($card['modify_time']);
        unset($card['gateway_token']);
        unset($card['gateway_token']);
        unset($card['gateway_token']);
        
        $data['fee'] = $f_data;
        $data['card'] = $card;

        $data['data'] = unserialize($data['data']);

        $category_id = $data['category_id'];
        $cat['id'] = $category_id;
        $cat['name'] = $category->getSingle($category_id);
        $data['category_id'] = $cat;

        $data['create_time'] = strtotime($data['create_time']);
        $data['modify_time'] = strtotime($data['modify_time']);
        return $data;
    }
    
    public function apiGetList($type, $page=1, $user=false, $ref=false, $location) {
        global $options;
        global $request_accept;
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
        if ($type == "waiting") {
            $result = $request_accept->getResponse($ref);

            $data['counts']['current_page'] = 1;
            $data['counts']['total_page'] = 1;
            $data['counts']['rows_on_current_page'] = count($result);
            $data['counts']['max_rows_per_page'] = 20;
            $data['counts']['total_rows'] = count($result);
            $data['data'] = $request_accept->formatResult($result);
        } else if (($type == "all") || ($type == "open") || ($type == "running") || ($type == "past") || ($type == "running") || ($type == "past") || ($type == "current") || ($type == "available")) {
            $result = $this->listAllData($user, $type, $start, $limit, $location);

            $view = false;
            if ($type == "available") {
                $view = "available";
            }

            $data['counts']['current_page'] = ($result['listCount'] > 0 ? $page : 0);
            $data['counts']['total_page'] = ceil($result['listCount']/$limit);
            $data['counts']['rows_on_current_page'] = count($result['list']);
            $data['counts']['max_rows_per_page'] = $limit;
            $data['counts']['total_rows'] = $result['listCount'];
            $data['data'] = $this->formatResult( $result['list'], false, "list", $view, $location );
        } else {
            $data = $this->formatResult( $this->listOne($type), $user, "single", $ref, $location );

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

        $array['data'] = serialize($array['data']);
        if (isset($array['address']) && ($array['address'] != "")) {
            $addressData = $this->googleGeoLocation(false, false, $array['address']);
            $array['latitude'] = $addressData['latitude'];
            $array['longitude'] = $addressData['longitude'];
            $array['state_code'] = $addressData['state_code'] = $addressData['province_code'];
            $array['state'] = $addressData['state'] = $addressData['province'];
            $array['country_code'] = $addressData['code'] = $addressData['country_code'];
            $array['country'] = $addressData['country'];
        } else {
            $array['latitude'] = $addressData['latitude'] = $location['latitude'];
            $array['longitude'] = $addressData['longitude'] = $location['longitude'];
            $array['state_code'] = $addressData['state_code'] = $location['state_code'];
            $array['state'] = $addressData['state'] = $location['state'];
            $array['country_code'] = $addressData['code'] = $location['code'];
            $array['country'] = $addressData['country'] = $location['country'];
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
                            $return['data'] = $post->formatResults( $result['data'], $maps);
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
        global $notifications;
        if ($action == "post") {
            $array['m_type'] = "text";
            if ((intval($array['user_r_id']) > 0) && ($array['user_r_id'] != $array['user_id'])) {
                $id = $messages->add($array);
                if ($id) {
                    $return['status'] = "200";
                    $return['message'] = "OK";

                    $data["to"] = $array['user_r_id'];
                    $data["title"] = "Message Notification";
                    $data["body"] = "New message from ".$users->listOnValue($array['user_id'], "screen_name");
                    $data['data']['page_name'] = "messages";
                    $data['data']['provider']['ref'] = $array['user_r_id'];
                    $data['data']['provider']['screen_name'] = $users->listOnValue( $array['user_r_id'], "screen_name" );
                    $data['data']['postId'] = $array['post_id'];

                    $notifications->sendPush($data);
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
                    $return['status'] = 501;
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "Responder ID must be defined in URL after the ad. ID";
                } else {
                    if ($action == "list") {
                        $initialComment = $messages->getPage($array['post_id'], $user_r, $user_id);

                        for ($i = 0; $i < count($initialComment); $i++) {
                            $user['id'] = intval( $initialComment[$i]['user_id'] );
                            if ($array['user_id'] == $initialComment[$i]['user_id']) {
                                $user['current_user'] = true;
                            } else {
                                $user['current_user'] = false;
                            }
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

                            $initialComment[$i]['create_time'] = strtotime($initialComment[$i]['create_time']);
                            unset($initialComment[$i]['post_id']);
                            unset($initialComment[$i]['status']);
                            unset($initialComment[$i]['modify_time']);
                        }
                        $return['status'] = 200;
                        $return['message'] = "OK";
                        $return['post_id'] = $array['post_id'];
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

                        $return['status'] = 200;
                        $return['message'] = "OK";
                        $return['post_id'] = $array['post_id'];
                        $return['data'] = $initialComment;
                    }
                }
            } else {
                $return['status'] = 404;
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
        if ($this->user_id == $data['user_id']) {
            $user_id = $data['user_id'];
            $user_r_id = $data['user_r_id'];
        } else {
            $user_id = $data['user_r_id'];
            $user_r_id = $data['user_id'];
        }
        $user['id'] = intval( $user_id );
        $user['name'] = $users->listOnValue($user_id, "screen_name");
        $user['rating']['score'] = round($rating->getRate($user_id), 2);
        $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($user_id)));
        $user['rating']['total'] = 5;
        $data['user_id'] = $user;

        //get user ID and name
        $user['id'] = $user_r_id;
        $user['name'] = $users->listOnValue($user_r_id, "screen_name");
        $user['rating']['score'] = round($rating->getRate($user_r_id), 2);
        $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($user_r_id)));
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
        $this->user_id = $user;
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
            `address` VARCHAR(500) NOT NULL,
            `description` VARCHAR(500) NOT NULL,
            `region` INT NOT NULL, 
            `card` INT NOT NULL, 
            `tx_id` INT NOT NULL,
            `latitude` DOUBLE NOT NULL,
            `longitude` DOUBLE NOT NULL,
            `start_date` VARCHAR(50) NOT NULL,
            `end_date` VARCHAR(50) NOT NULL,
            `review_status` INT NOT NULL,
            `client_rate` INT NOT NULL,
            `user_rate` INT NOT NULL,
            `review_status_time` VARCHAR(50) NULL, 
            `data` TEXT NULL,
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
include_once("request_accept.php");
$request_negotiate  = new request_negotiate;
$request_accept  = new request_accept;
?>