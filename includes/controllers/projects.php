<?php
    class projects extends database {
        /*  create users
        */
        public function create($array) {
            $replace = array();
            $replace[] = "category_id";
            $replace[] = "project_name";
            $replace[] = "project_dec";
            $replace[] = "tag";
            $replace[] = "allow_remote";
            $replace[] = "address";
            $replace[] = "city";
            $replace[] = "postal_code";
            $replace[] = "state";
            $replace[] = "country";
            $replace[] = "region";
            $replace[] = "lat";
            $replace[] = "lng";
            $replace[] = "billing_type";
            $replace[] = "default_fee";

            $create = $this->replace("projects", $array, $replace);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

        public function remove($id) {
            $remove = $this->delete("projects", $id, "ref");
            if ($remove){
                global $media;
                $media->remove($id);
                return true;
            } else {
                return false;
            }

        }

        public function getowner($id, $status) {
            $data = $this->listOne($id);

            if ($data) {
                if ((($data['user_id'] == $_SESSION['users']['ref']) || ($data['client_id'] == $_SESSION['users']['ref']))  && ($data['status'] == "NEW")) {
                    header("location: ".URL.'confirmHire?ref='.$id);
                } else if (($status == "active") && ($data['status'] != "ACTIVE")) {
                    if ((($data['user_id'] == $_SESSION['users']['ref']) || ($data['client_id'] == $_SESSION['users']['ref']))  && ($data['status'] == "ON-GOING")) {
                        header("location: ".$this->seo($id, "profile"));
                    } else {
                        header("location: ".URL."ads?error=".urldecode("This post is not available"));
                    }
                } else if (($status == "profile") && ($data['status'] == "ACTIVE")) {
                    header("location: ".$this->seo($id, "view"));
                }
            } else {
                header("location: ".URL."ads?error=".urldecode("This post is not available"));
            }
        }

        private function getTransaction($id) {
            global $wallet;
            $data = $this->listOne($id);

            $user_transaction = $wallet->getSortedListTrans($data['ref'], "tx_type_id", "tx_type", "project", "gateway_status", "Approved");

            for ($i = 0; $i < count($user_transaction); $i++) {
                $user_wallet_tx = $wallet->getSortedListWallet($user_transaction[$i]['ref'], "tx_id", "tx_desc", "Work Payment", "status", "0", "ref", "DESC", "AND", false, false, "getRow");

                if ($user_wallet_tx) {
                    $this->updateOne("wallet", "status", 1, $user_wallet_tx['ref'], "ref");
                }
            }

            return true;
        }

        public function approve($id, $action, $user_type=false, $admin=false) {
            global $messages;
            global $users;
            global $options;
            global $alerts;
            $data = $this->listOne($id);

            echo $user_type;

            if ($user_type == true) {
                if ($user_type === true) {
                    $user = $_SESSION['users']['ref'];
                } else {
                    $user = $user_type;
                }
            }
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

                        $tag = "you have marked this task as done. Payment has been sent. <a href='".URL."ads\archive'>Sigin in</a> to your MOBA Account to learn more";

                        $user_data = $users->listOne($data['user_id']);
                        $client = $user_data['last_name']." ".$user_data['other_names'];
                        $subjectToClient = "[COMPLETE]: ".$data['project_name'];
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

                        $tag = "This task has been approve and the payment is now available in your wallet. <a href='".URL."ads\past'>Sigin in</a> to your MOBA Account to learn more";

                        $user_data = $users->listOne($data['client_id']);
                        $client = $user_data['last_name']." ".$user_data['other_names'];
                        $subjectToClient = "[COMPLETE]: ".$data['project_name'];
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

                        //count the number of completed task and approve for both users
                        $this->promoteUser($data['user_id']);
                        $this->promoteUser($data['client_id']);
                        return true;
                    } else {
                        return false;
                    }
                } else if ($action == "request_approve") {
                    if ($this->updateOneRow("review_status", 1, $data['ref'])) {
                        $this->updateOneRow("review_status_time", (time()+(60*60*24*$options->get("max_days_approve"))), $data['ref']);
                        
                        $tag = "Your attention is required in <strong>".$data['projec_name']."</strong>. This task as been marked as comleted and is awaiting your review. If no action is taken by you in ".$options->get("max_days_approve")." ".$this->addS("day", $options->get("max_days_approve")).", this task will be marked as completed and all outstanding payments will be released. <a href='".URL."ads/on-going'>Sigin in</a> to your MOBA Account to learn more";;

                        $user_data = $users->listOne($data['user_id']);
                        $client = $user_data['last_name']." ".$user_data['other_names'];
                        $subjectToClient = "[ACTION REQUIRED]: ".$data['project_name'];
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
                        $msgArray['project_id']  = $data['ref'];
                        $msgArray['m_type']  = "system";
                        $messages->add($msgArray);

                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        private function promoteUser($user) {
            global $users;
            global $options;
            $data = $users->listOne($user);

            $query = "SELECT COUNT(`ref`) FROM `projects` WHERE (`user_id` = :user OR `client_id` = :user ) AND `status` = 'COMPLETED'";

            $prepare[":user"] = $user;    

            if (($this->query($query, $prepare, "getCol") >= $options->get("minimum_post")) && ($data['badge'] == 0)) {
                $users->modifyUser("badge", 1, $user, "ref");
            }
        }

        public function authorizeJob() {
            global $users;
            global $alerts;
            $query = "SELECT * FROM `projects` WHERE `status` = 'ON-GOING' AND review_status = 1 AND review_status_time < ".time()." LIMIT 5";

            $list = $this->run($query, false, "list");

            for ($i = 0; $i < count($list); $i++) {
                $data = $this->listOne($list[$i]['ref']);

                if ($this->getTransaction($list[$i]['ref'])) {
                    $this->updateOneRow("review_status", 0, $data['ref']);
                    $this->updateOneRow("review_status_time", NULL, $data['ref']);
                    $this->updateOneRow("status", "COMPLETED", $data['ref']);

                    $tag = "this task has been automatically marked as done. Payment has been sent. <a href='".URL."ads'>Sigin in</a> to your MOBA Account to learn more";;

                    $user_data = $users->listOne($data['user_id']);
                    $client = $user_data['last_name']." ".$user_data['other_names'];
                    $subjectToClient = "[COMPLETE]: ".$data['project_name'];
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

                    $tag = "This task has been automatically approve and the payment is now available in your wallet. <a href='".URL."ads'>Sigin in</a> to your MOBA Account to learn more";

                    $user_data = $users->listOne($data['client_id']);
                    $client = $user_data['last_name']." ".$user_data['other_names'];
                    $subjectToClient = "[COMPLETE]: ".$data['project_name'];
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

                    //count the number of completed task and approve for both users
                    $this->promoteUser($data['user_id']);
                    $this->promoteUser($data['client_id']);
                    return true;
                } else {
                    return false;
                }
            }
        }

        public function visitors($ref) {
            $query = "UPDATE projects SET page_visit = (page_visit+1) WHERE ref = :ref";
            $prepare['ref'] = $ref;
            $this->query($query, $prepare);

            return $this->numberPrintFormat( $this->getSingle($ref, "page_visit", "ref") );
        }

        public function getCode() {
            return $this->confirmUnique($this->createUnique());
        }

		function createUnique() {
			$num = $this->createRandomPassword(5).rand(100, 999);
			return $num;
		}
		
		function confirmUnique($key) {
			if ($this->checkExixst("projects", "project_code", $key, "project_code") == 0) {
				return $key;
			} else {
				return $this->confirmUnique($this->createUnique());
			}
        }
        
        function getList($start=false, $limit=false, $order="project_name", $dir="ASC", $where=false, $type="list") {
            return $this->list("projects", $start, $limit, $order, $dir, $where, $type);
        }

		function getSingle($name, $tag="project_name", $ref="ref") {
            return $this->getOneField("projects", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("projects", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'project_name', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("projects", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function updateOneRow($tag, $value, $id) {
            return $this->updateOne("projects", $tag, $value, $id, "ref");
        }

        public function getFee($array) {
            global $projects_negotiate;
            $data = $projects_negotiate->getApproved($array);
            if ($data) {
                return $data['amount'];
            } else {
                return $this->listOne($array['project'])['default_fee'];
            }
        }

        public function getFeeTotal($array) {
            global $projects_data;
            $data = $this->listOne($array['id'], "ref");
             //get negotiated balance
             $num = 1;
             if ($data['billing_type'] != "per_job") {
                 $d_array['project'] = $array['id'];
                 $d_array['user'] = $array['wallet_user'];
                 $d_array['user_r'] = $array['wallet_collector'];
                 $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
                 $raw_get_data = unserialize($get_data['data_field']);
 
                 if ($raw_get_data['data_type'] == "hours") {
                    $num = $raw_get_data['content'];
                     $end_date = time()+(60*60*$num);
                 } else if ($raw_get_data['data_type'] == "milestone") {
                     $num = count($raw_get_data['content']);
                     $total_time = 0;
                     for ($i = 0; $i < count($raw_get_data['content']); $i++) {
                         $total_time = $total_time + ($raw_get_data['content'][$i]['duration'] * $this->getDate($raw_get_data['content'][$i]['duration_lenght']));
                     }
                     $end_date = time()+$total_time;
                 }
             } else {
                $end_date = "";
             }
             $respons['fee'] = $num*floatval($this->getFee(array("project" => $array['id'], "user" => $array['wallet_user'], "user_r" => $array['wallet_collector'])));
             $respons['end_date'] = $end_date;
             $respons['count'] = $num;

             return $respons;
        }

        private function getCharges($user_id, $amount) {
            global $users;
            global $options;
            $badge = $users->listOnValue($user_id, "badge");

            if ($badge == 0) {
                $val = $options->get("service_charge");
            } else {
                $val = $options->get("service_charge_premium");
            }

            $fee = ($val/100)*$amount;

            return $fee;
        }

        function getMapHome($longitude, $latitude) {
            global $country;
            global $media;
            $min_lat = $latitude - 0.9;
            $max_lat = $latitude + 0.9;

            $min_long = $longitude - 0.9;
            $max_long = $longitude + 0.9;

            if (isset($_SESSION['filter'])) {
                $filter = "`project_type` = '".$_SESSION['filter']."' AND ";
            } else {
                $filter = "";
            }

            $query = "SELECT `ref`, `lat`, `lng`, `category_id`, `project_code`, `project_name`, `project_dec`, `project_type`, `address`, `default_fee`, `country` FROM `projects` WHERE ".$filter."`status` = 'ACTIVE' AND `lat` BETWEEN ".$min_lat." AND ".$max_lat." AND `lng` BETWEEN ".$min_long." AND ".$max_long." LIMIT 100";

            $data = $this->run($query, false, "list");
            
            $array = array();
            $array['type'] = "FeatureCollection";

            for ($i = 0; $i < count($data); $i++) {
                $array['features'][$i]['geometry']['type'] = "Point";
                $array['features'][$i]['geometry']['coordinates'][0] = floatval($data[$i]['lng']);
                $array['features'][$i]['geometry']['coordinates'][1] = floatval($data[$i]['lat']);
                $array['features'][$i]['type'] = "Feature";
                if ($data{$i}['project_type'] != "vendor") {
                    $array['features'][$i]['properties']['marker'] = "green";
                } else {
                    $array['features'][$i]['properties']['marker'] = "red";
                }
                $array['features'][$i]['properties']['url'] = $this->seo($data[$i]['ref'], "view");
                $array['features'][$i]['properties']['name'] = $data[$i]['project_name'];
                $array['features'][$i]['properties']['description'] = $this->truncate($data[$i]['project_dec']);
                $array['features'][$i]['properties']['address'] = $data[$i]['address'];
                $array['features'][$i]['properties']['fee'] = $country->getCountryData( $data[$i]['country'] )." ".number_format($data[$i]['default_fee'], 2);
                $array['features'][$i]['properties']['cover'] = $media->getCover($data[$i]['ref']);
            }

            return $array;
        }

        function searchMapHome($location, $val) {
            global $search;
            return $search->jobSearch($location, $val);
        }

        function setFeatured($id, $days) {
            $daysTime = time()+(60*60*24*intval($days));
            $this->updateOne("projects", "is_featured", 1, $id, "ref");
            $this->updateOne("projects", "is_featured_time", $daysTime, $id, "ref");
        }

        function checkFeaturedCron() {
            $query = "UPDATE `projects` SET `is_featured` = 0 WHERE `is_featured` = 1 AND `is_featured_time` < ".time();
            $this->run($query);
        }

        function modifyStatus($id, $status) {
            if ($this->updateOne("projects", "status", $status, $id, "ref")) {
                global $users;
                $data = $this->listOne($id);
                $array = $users->listOne($data['user_id']);
                $client = $array['last_name']." ".$array['other_names'];
                $subjectToClient = "[".$status."]: ".$data['project_name'];
                $contact = "MOBA <".replyMail.">";
                
                $fields = 'subject='.urlencode($subjectToClient).
                    '&last_name='.urlencode($array['last_name']).
                    '&other_names='.urlencode($array['other_names']).
                    '&id='.urlencode($id);
                $mailUrl = URL."includes/views/emails/project_notification.php?".$fields;
                $messageToClient = $this->curl_file_get_contents($mailUrl);
                
                $mail['from'] = $contact;
                $mail['to'] = $client." <".$array['email'].">";
                $mail['subject'] = $subjectToClient;
                $mail['body'] = $messageToClient;
                
                global $alerts;
                $alerts->sendEmail($mail);

                return true;
            } else {
                return false;
            }
        }

        public function authorizeProject($array) {
            global $wallet;
            global $transactions;
            global $users;
            global $country;
            global $messages;
            $payment_card = new payment_card;
            //get ad details
            $data = $this->listOne($array['id'], "ref");   
            
            $regionData = $country->getLoc($array['region']);
            if ($data['project_type'] == "vendor") {
                $wallet_user = $client_id = $array['responder'];
                $wallet_collector = $data['user_id'];
            } else {
                $wallet_user = $data['user_id'];
                $wallet_collector = $client_id = $array['responder'];
            }
           
            //get wallet balnce
            $wallet_balance = floatval($wallet->balance($wallet_user, $data['region']));
            $feeDataArray = array("id"=>$array['id'], "wallet_user"=>$wallet_user, "wallet_collector"=>$wallet_collector);
            $feeData = $this->getFeeTotal($feeDataArray);
            $fee = $feeData['fee'];
            $end_date = $feeData['end_date'];

            $rmove = 0-$fee;
            $remainder = $fee-$wallet_balance;
            $complete = false;
            if ($wallet_balance < $fee) {
                //get lists of all cards
                $list = $payment_card->getSortedList($wallet_user, "user_id");

                $tx_pay['user_id'] = $wallet_user;
                $tx_pay['tx_type_id'] = $array['id'];
                $tx_pay['tx_type'] = "project";
                $tx_pay['tx_dir'] = "DR";
                $tx_pay['card'] = 0;
                $tx_pay['region'] = $data['region'];
                $tx_pay['net_total'] = $remainder;
                $tx_pay['tax_total'] = 9;
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
                        $tx_wallet['user_id'] = $wallet_user;
                        $tx_wallet['tx_id'] = $tx_id;
                        $tx_wallet['ref_id'] = 0;
                        $tx_wallet['tx_desc'] = "CR transaction in wallet";
                        $tx_wallet['tx_dir'] = "CR";
                        $tx_wallet['region'] = $data['region'];
                        $tx_wallet['amount'] = $remainder;
                        $tx_wallet['status'] = 1;
                        $wallet->createWallet($tx_wallet);
                        $complete = true;
                        break;
                    }
                }
            } else {
                //post transaction to wallet and credit the 
                $tx_pay['user_id'] = $wallet_user;
                $tx_pay['tx_type_id'] = $array['id'];
                $tx_pay['tx_type'] = "project";
                $tx_pay['tx_dir'] = "DR";
                $tx_pay['card'] = 0;
                $tx_pay['region'] = $data['region'];
                $tx_pay['net_total'] = $fee;
                $tx_pay['tax_total'] = 9;
                $tx_pay['gross_total'] = $fee;
                $tx_pay['gateway_status'] = "Approved";
                $tx_pay['status'] = 2;
                $tx_id = $transactions->createTx($tx_pay);
                $complete = true;
            }
            //approve ad and send email
            if ($complete == true) {
                $tx_wallet['user_id'] = $wallet_user;
                $tx_wallet['tx_id'] = $tx_id;
                $tx_wallet['ref_id'] = $wallet_collector;
                $tx_wallet['tx_desc'] = "Work Payment";
                $tx_wallet['tx_dir'] = "DR";
                $tx_wallet['region'] = $data['region'];
                $tx_wallet['amount'] = $rmove;
                $tx_wallet['status'] = 1;
                $wallet->createWallet($tx_wallet);

                $serviceCharge = $this->getCharges($wallet_collector, $fee);
                $tx_pay['user_id'] = $wallet_collector;
                $tx_pay['tx_type_id'] = $array['id'];
                $tx_pay['tx_type'] = "project";
                $tx_pay['tx_dir'] = "CR";
                $tx_pay['card'] = 0;
                $tx_pay['region'] = $data['region'];
                $tx_pay['net_total'] = ($fee-$serviceCharge);
                $tx_pay['tax_total'] = 9;
                $tx_pay['gross_total'] = ($fee-$serviceCharge);
                $tx_pay['gateway_status'] = "Approved";
                $tx_pay['status'] = 2;
                $tx_id = $transactions->createTx($tx_pay);

                $tx_pay['user_id'] = 0;
                $tx_pay['tx_type_id'] = $tx_id;
                $tx_pay['tx_type'] = "service_charge";
                $tx_pay['tx_dir'] = "CR";
                $tx_pay['card'] = 0;
                $tx_pay['region'] = $data['region'];
                $tx_pay['net_total'] = $serviceCharge;
                $tx_pay['tax_total'] = 9;
                $tx_pay['gross_total'] = $serviceCharge;
                $tx_pay['gateway_status'] = "Approved";
                $tx_pay['status'] = 2;
                $s_c_tx_id = $transactions->createTx($tx_pay);

                $tx_wallet['user_id'] = $wallet_collector;
                $tx_wallet['tx_id'] = $s_c_tx_id;
                $tx_wallet['ref_id'] = $wallet_user;
                $tx_wallet['tx_desc'] = "Work Payment Charges";
                $tx_wallet['tx_dir'] = "DR";
                $tx_wallet['region'] = $data['region'];
                $tx_wallet['amount'] = (0-$serviceCharge);
                $tx_wallet['status'] = 1;
                $wallet->createWallet($tx_wallet);
                
                $tx_wallet['user_id'] = $wallet_collector;
                $tx_wallet['tx_id'] = $tx_id;
                $tx_wallet['ref_id'] = $wallet_user;
                $tx_wallet['tx_desc'] = "Work Payment";
                $tx_wallet['tx_dir'] = "CR";
                $tx_wallet['region'] = $data['region'];
                $tx_wallet['amount'] = $fee;
                $tx_wallet['status'] = 0;
                $wallet->createWallet($tx_wallet);

                $user_data = $users->listOne($data['user_id']);
                $client = $user_data['last_name']." ".$user_data['other_names'];
                $subjectToClient = "[RUNNING]: ".$data['project_name'];
                $contact = "MOBA <".replyMail.">";
                
                $fields = 'subject='.urlencode($subjectToClient).
                    '&last_name='.urlencode($user_data['last_name']).
                    '&other_names='.urlencode($user_data['other_names']).
                    '&id='.urlencode($array['id']);
                $mailUrl = URL."includes/views/emails/project_notification.php?".$fields;
                $messageToClient = $this->curl_file_get_contents($mailUrl);
                
                $mail['from'] = $contact;
                $mail['to'] = $client." <".$user_data['email'].">";
                $mail['subject'] = $subjectToClient;
                $mail['body'] = $messageToClient;
                
                global $alerts;
                $alerts->sendEmail($mail);

                $this->updateOneRow("status", "ON-GOING", $array['id']);
                $this->updateOneRow("start_date", time(), $array['id']);
                $this->updateOneRow("end_date", $end_date, $array['id']);
                $this->updateOneRow("client_id", $client_id, $array['id']);
                $msg = "This listinng has been authorized, you can now start";
                $msgArray['message']  = $msg;
                $msgArray['user_r_id']  = $wallet_collector;
                $msgArray['user_id']  = $wallet_user;
                $msgArray['project_id']  = $array['id'];
                $msgArray['m_type']  = "system";
                $messages->add($msgArray);
                $return['status'] = "COMPLETE";
            } else {
                $user_data = $users->listOne($data['user_id']);
                
                $tag = "We could not approve payment for the ".$data['project_name']." ad. Please make sure your MOBA wallet is funded with a minimum of ".$regionData['currency_symbol'].number_format($remainder, 2).". <a href='".URL."wallet'>Sigin in</a> to your MOBA Account to learn more";;

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

                $return['status'] = "FAILED";
                $return['ref'] = $array['id'];
                $this->updateOne("transactions", "status", 1, $tx_id, "ref");
            }
            return $return;
        }

        public function getDate($text) {
            if ($text == "Minute") {
                return 60;
            } else if ($text == "Hour") {
                return 60*60;
            } else if ($text == "Day") {
                return 60*60*24;
            } else if ($text == "Week") {
                return 60*60*24*7;
            } else if ($text == "Month") {
                return 60*60*24*31;
            }
        }

        public function recentlyPostedData($longitude, $latitude, $start, $limit) {
            $min_lat = $latitude - search_radius;
            $max_lat = $latitude + search_radius;

            $min_long = $longitude - search_radius;
            $max_long = $longitude + search_radius;
            
            if (isset($_SESSION['filter'])) {
                $filter = " AND `project_type` = '".$_SESSION['filter']."'";
            } else {
                $filter = "";
            }
            
            //query for result
            $query = "SELECT * FROM `projects` WHERE `status` = 'ACTIVE'".$filter." AND `lat` BETWEEN ".$min_lat." AND ".$max_lat." AND `lng` BETWEEN ".$min_long." AND ".$max_long." ORDER BY `ref` DESC LIMIT ".$start.",".$limit;
            $returm['list'] = $this->run($query, false, "list");
            
            //query for pagination count
            $query = "SELECT * FROM `projects` WHERE `status` = 'ACTIVE'".$filter." AND `lat` BETWEEN ".$min_lat." AND ".$max_lat." AND `lng` BETWEEN ".$min_long." AND ".$max_long;
            $returm['dataCount'] = $this->run($query, false, "count");

            return $returm;
        }

        public function aroundMeData($longitude, $latitude, $start, $limit) {
            $min_lat = $latitude - search_radius_me;
            $max_lat = $latitude + search_radius_me;

            $min_long = $longitude - search_radius_me;
            $max_long = $longitude + search_radius_me;
            
            if (isset($_SESSION['filter'])) {
                $filter = " AND `project_type` = '".$_SESSION['filter']."'";
            } else {
                $filter = "";
            }
            
            //query for result
            $query = "SELECT * FROM `projects` WHERE `status` = 'ACTIVE'".$filter." AND `lat` BETWEEN ".$min_lat." AND ".$max_lat." AND `lng` BETWEEN ".$min_long." AND ".$max_long." LIMIT ".$start.", ".$limit; 

            $returm['list'] = $this->run($query, false, "list");
            
            //query for pagination count
            $query = "SELECT * FROM `projects` WHERE `status` = 'ACTIVE'".$filter." AND `lat` BETWEEN ".$min_lat." AND ".$max_lat." AND `lng` BETWEEN ".$min_long." AND ".$max_long; 

            $returm['dataCount'] = $this->run($query, false, "count");

            return $returm;
        }
        
        public function promotedData($id, $longitude, $latitude) {
            $min_lat = $latitude - search_radius;
            $max_lat = $latitude + search_radius;

            $min_long = $longitude - search_radius;
            $max_long = $longitude + search_radius;

            
            if (isset($_SESSION['filter'])) {
                $filter = " AND `project_type` = '".$_SESSION['filter']."'";
            } else {
                $filter = "";
            }
            
            if ($id != false) {
                $filter .= " AND (`category_id` LIKE '".$id.",%' OR `category_id` LIKE '".$id."' OR `category_id` LIKE '%,".$id."' OR `category_id` LIKE '%,".$id.",%')";
            }
            
            //query for result
            $query = "SELECT * FROM `projects` WHERE `status` = 'ACTIVE'".$filter." AND `is_featured` = 1 AND `is_featured_time` > ".time()." AND `lat` BETWEEN ".$min_lat." AND ".$max_lat." AND `lng` BETWEEN ".$min_long." AND ".$max_long." ORDER BY RAND() LIMIT 18";

            return $this->run($query, false, "list");
        }

        public function listAllWalletData($ref, $view, $start, $limit) {
            global $project_save;

            if ($view == "on-going") {
                $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'ON-GOING' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")");
                $return['listCount'] = $this->getList(false, false, "ref", "DESC", "`status` = 'ON-GOING' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")", "count");
                $return['tag'] = "All On-going Jobs";
              } else if ($view == "conversation") {
                $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'ACTIVE' AND `user_id` != ".$ref." AND `ref` IN (SELECT `project_id` FROM `messages` WHERE `user_id` = ".$ref." OR `user_r_id` = ".$ref." )");
                $return['listCount'] = $this->getList(false, false, "ref", "DESC", "`status` = 'ACTIVE' AND `user_id` != ".$ref." AND `ref` IN (SELECT `project_id` FROM `messages` WHERE `user_id` = ".$ref." OR `user_r_id` = ".$ref." )", "count");
                $return['tag'] = "Current Interests.";
              } else if ($view == "running") {            
                $return['list'] = $this->getList($start, $limit, "ref", "DESC", "`status` = 'ON-GOING' AND ((`user_id` = ".$ref." AND `project_type` = 'vendor') OR (`client_id` = ".$ref." AND `project_type` = 'client'))");      
                $return['listCount'] = $this->getList(false, false, "ref", "DESC", "`status` = 'ON-GOING' AND ((`user_id` = ".$ref." AND `project_type` = 'vendor') OR (`client_id` = ".$ref." AND `project_type` = 'client'))", "count");
                $return['tag'] = "All Active Tasks";
              } else if ($view == "past") {
                $return['list'] = $this->getSortedList("COMPLETED", "status", "client_id", $ref, false, false,"modify_time", "DESC", "AND", $start, $limit);
                $return['listCount'] = $this->getSortedList("COMPLETED", "status", "client_id", $ref, false, false,"modify_time", "DESC", "AND", false, false, "count");
                $return['tag'] = "All Completed Ads";
              } else if ($view == "archive") {
                $return['list'] = $this->getSortedList("COMPLETED", "status", "user_id", $ref, false, false,"modify_time", "DESC", "AND", $start, $limit);
                $return['listCount'] = $this->getSortedList("COMPLETED", "status", "user_id", $ref, false, false,"modify_time", "DESC", "AND", false, false, "count");
                $return['tag'] = "All Archived Ads";
              } else if ($view == "draft") {
                $return['list'] = $this->getSortedList("NEW", "status", "user_id", $ref, false, false,"ref", "ASC", "AND", $start, $limit);
                $return['listCount'] = $this->getSortedList("NEW", "status", "user_id", $ref, false, false,"ref", "ASC", "AND", false, false, "count");
                $return['tag'] = "All Ads in Draft";
              } else if ($view == "all") {
                $return['list'] = $this->getList($start, $limit, "ref", "DESC", " `user_id` = ".$ref." OR `client_id` = ".$ref."");
                $return['listCount'] = $this->getList(false, false, "ref", "DESC", " `user_id` = ".$ref." OR `client_id` = ".$ref."", "count");
                $return['tag'] = "All Ads";
              } else if ($view == "saved") {
                $return['list'] = $project_save->getSortedList($ref, "user_id", false, false, false, false,"ref", "ASC", "AND", $start, $limit);
                $return['listCount'] = $project_save->getSortedList($ref, "user_id", false, false, false, false,"ref", "ASC", "AND", false, false, "count");
                $return['tag'] = "All Saved Ads";
              } else {
                $return['list'] = $this->getSortedList("ACTIVE", "status", "user_id", $ref, false, false,"ref", "ASC", "AND", $start, $limit);
                $return['listCount'] = $this->getSortedList("ACTIVE", "status", "user_id", $ref, false, false,"ref", "ASC", "AND", false, false, "count");
                $return['tag'] = "All Active Ads";
              }

            return $return;
        }
        
        private function formatResult($data, $user=false, $type="list", $view=false) {
            global $messages;
            if ($data) {
                if ($type == "list") {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i] = $this->clean($data[$i]);
                    }
                } else {
                    if ((($data['user_id'] == $user) || ($data['client_id'] == $user)) || ($data['status'] == "ACTIVE")) {
                        $messages->markRead($user, $data['ref']);
                        $data = $this->clean($data, $user, $view);
                    } else {
                        $data['error'] = true;
                        $data['error_msg'] = "This post is not available";
                    }
                }
            }
            return $data;
        }

        private function clean($data, $owner=false, $view=false) {
            global $users;
            global $country;
            global $category;
            global $rating;
            global $rating_comment;
            global $messages;
            global $rating_question;
            global $projects_data;
            global $wallet;
            global $media;
            //get individual property
            $show = false;

            //get media gallery
            
            $getAlbum = $media->getAlbum($data['ref']);
            $data['media']['cover'] = $media->getCover($data['ref']);
            for ($i = 0; $i < count($getAlbum); $i++) {
                $data['media']['album'][$i] = URL.$getAlbum[$i]['media_url'];
            }
            //get the person who funds the wallet
            if ($view == false) {
                $view = $data['user_id'];
                
                $responderRef = $owner;
            } else {
                $show = true;
                $responderRef = $view;
            }
            
            if ($data['project_type'] == "vendor") {
                $wallet_user = $responderRef;
                $wallet_collector = $data['user_id'];
            } else {
                $wallet_user = $data['user_id'];
                $wallet_collector = $responderRef;
            }

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
                        $response[$i]['user_id'] = $user;
                        unset($user);
                    }
                    $data['advert_data']['response'] = $response;
                }
                if ($data['is_featured'] == 1) {
                    $f_data['status'] = "Yes";
                    $f_data['expires'] = $data['is_featured_time'];
                } else {
                    $f_data['status'] = "no";
                }
                $regionData = $country->getLoc($data['region'], "ref");
                $f_data['amount_per_dat'] = $regionData['featured_ad'];
                $f_data['tax'] = $regionData['tax']/100;
                $f_data['currency'] = $regionData['currency'];
                $f_data['currency_symbol'] = $regionData['currency_symbol'];
                $data['is_featured'] = $f_data;
            } else {
                $user_id = $data['user_id'];
                
                if ($data['client_id'] == 0) {
                    $user_r_id = $owner;
                } else {
                    $user_r_id = $data['client_id'];
                }
                
                unset($data['is_featured']);
            }
            if ($owner != false){
                if (($owner != $data['user_id']) || ($show == true)) {
                    $data['page_count'] = $this->visitors($data['ref']);
                    $initialComment = $messages->getPage($data['ref'], $owner, $view);
                    
                    for ($i = 0; $i < count($initialComment); $i++) {
                        $user['id'] = $initialComment[$i]['user_id'];
                        $user['name'] = $users->listOnValue($initialComment[$i]['user_id'], "screen_name");
                        $user['rating']['score'] = round($rating->getRate($initialComment[$i]['user_id']), 2);
                        $user['rating']['total'] = 5;
                        $initialComment[$i]['user_id'] = $user;
                        unset($user);
                        $user['id'] = $initialComment[$i]['user_r_id'];
                        $user['name'] = $users->listOnValue($initialComment[$i]['user_r_id'], "screen_name");
                        $user['rating']['score'] = round($rating->getRate($initialComment[$i]['user_r_id']), 2);
                        $user['rating']['total'] = 5;
                        $initialComment[$i]['user_r_id'] = $user;
                        if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                            $r_data = explode("_", $initialComment[$i]['m_type_data']);
                            $re_data['value'] = $r_data[0];
                            $re_data['request_id'] = $r_data[1];
                        }
                        $initialComment[$i]['m_type_data'] = $re_data;

                        unset($user);

                        unset($initialComment[$i]['project_id']);
                        unset($initialComment[$i]['status']);
                        unset($initialComment[$i]['modify_time']);
                    }
                    $data['advert_data']['messages'] = $initialComment;
                    if (($user_id == $owner) || ($user_r_id == $owner)) {
                        if ($data['billing_type'] != "per_job") {
                            $d_array['project'] = $data['ref'];
                            $d_array['user'] = $user_id;
                            $d_array['user_r'] = $user_r_id;
                            $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
                            $raw_get_data = unserialize($get_data['data_field']);
                            
                            if ($raw_get_data['data_type'] == "hours") {
                                $data['advert_data']['hours']['hour_request_id'] = $get_data['ref'];
                                $data['advert_data']['hours']['completed']['value'] = $raw_get_data['complete'];
                                $data['advert_data']['hours']['completed']['text'] = $raw_get_data['complete']." ".$this->addS("Hour", $raw_get_data['complete']);
                                $data['advert_data']['hours']['remaining']['value'] = $raw_get_data['content'];
                                $data['advert_data']['hours']['remaining']['text'] = $raw_get_data['content']." ".$this->addS("Hour", $raw_get_data['content']);
                                $data['advert_data']['hours']['total']['value'] = $raw_get_data['content']+$raw_get_data['complete'];
                                $data['advert_data']['hours']['total']['text'] = ($raw_get_data['complete']+$raw_get_data['content'])." ".$this->addS("Hour", ($raw_get_data['content']+$raw_get_data['complete']));
                            }
                            if ($raw_get_data['data_type'] == "milestone") {
                                $data['advert_data']['milestone'] = $raw_get_data['content'];
                                $data['advert_data']['milestone']['milestone_request_id'] = $get_data['ref'];
                            }
                        }
                        if ($data['status'] == "COMPLETED") {
                            $checkRate = $rating->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "project_id", $data['ref']);
                            for ($i = 0; $i < count($checkRate); $i++) {
                                unset($checkRate[$i]['ref']);
                                unset($checkRate[$i]['modify_time']);
                                unset($checkRate[$i]['comments']);
                                unset($checkRate[$i]['project_id']);

                                $user['id'] = $checkRate[$i]['user_id'];
                                $user['name'] = $users->listOnValue($checkRate[$i]['user_id'], "screen_name");
                                $checkRate[$i]['user_id'] = $user;
                                unset($user);
                                $user['id'] = $checkRate[$i]['reviewed_by'];
                                $user['name'] = $users->listOnValue($checkRate[$i]['reviewed_by'], "screen_name");
                                $checkRate[$i]['reviewed_by'] = $user;
                                unset($user);
                                $checkRate[$i]['question_id'] = $rating_question->getSingle($checkRate[$i]['question_id']);
                            }
                            $checkComment = $rating_comment->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "project_id", $data['ref'], "ref", "ASC", "AND", false, false, "getRow");
                            unset($checkComment['ref']);
                            unset($checkComment['modify_time']);
                            unset($checkComment['project_id']);

                            $user['id'] = $checkComment['user_id'];
                            $user['name'] = $users->listOnValue($checkComment['user_id'], "screen_name");
                            $checkComment['user_id'] = $user;
                            unset($user);
                            $user['id'] = $checkComment['reviewed_by'];
                            $user['name'] = $users->listOnValue($checkComment['reviewed_by'], "screen_name");
                            $checkComment['reviewed_by'] = $user;
                            unset($user);

                            $data['advert_data']['rating'] = $checkRate;
                            $data['advert_data']['comment'] = $checkComment;
                        }
                    }
                }
            }

            if ($data['allow_remote'] == 1) {
                $data['allow_remote'] = "Yes";
            } else {
                $data['allow_remote'] = "Yes";
            }
            //get fee 

            if ($this->getFee( array("project" => $data['ref'], "user" => $user_id, "user_r" => $user_r_id ) ) != $data['default_fee'] ) {
                $feedata['default'] = $data['default_fee'];
                if ($owner != false) {
                    $feedata['current'] = $this->getFee( array("project" => $data['ref'], "user" => $user_id, "user_r" => $user_r_id ) );
                }
            } else {
                $feedata['default'] = $data['default_fee'];
                if ($owner != false) {
                    $feedata['current'] = $data['default_fee'];
                }
            }
            $feedata['currency'] = $country->getCountryData( $data['country'] );
            if ($owner != false) {
                $wallet_balance = floatval($wallet->balance($wallet_user, $data['region']));
                $feeData = $this->getFeeTotal( array("id"=>$data['ref'], "wallet_user"=>$wallet_user, "wallet_collector"=>$wallet_collector) );
                $fee = $feeData['fee'];

                if (($wallet_user == $owner) && ($data['status'] == "ACTIVE")) {
                    $feedata['wallet']['before'] = round($wallet_balance, 2);
                    $feedata['wallet']['charges'] = $fee;
                    $feedata['wallet']['after'] = round(($wallet_balance-$fee), 2);
                    if ($fee > $wallet_balance) {
                        $feedata['wallet']['comment'] = "Not enough balance to process transaction";
                    }
                }
            }

            $data['default_fee'] = $feedata;
            //get user ID and name
            $user['id'] = $data['user_id'];
            $user['name'] = $users->listOnValue($data['user_id'], "screen_name");
            $user['rating']['score'] = round($rating->getRate($data['user_id']), 2);
            $user['rating']['total'] = 5;
            $data['user_id'] = $user;

            //get all category ID and Name and save in usable array
            $cat = explode(",", $data['category_id']);
            for ($c = 0; $c < count($cat); $c++) {
                $array[$c]['id'] = $cat[$c];
                $array[$c]['name'] = $category->getSingle($cat[$c]);
            }
            $data['category_id'] = $array;

            unset($data['start_date']);
            unset($data['end_date']);
            unset($data['payment_status']);
            unset($data['review_status']);
            unset($data['review_status_time']);
            unset($data['client_rate']);
            unset($data['user_rate']);
            unset($data['client_id']);
            unset($data['is_featured_time']);
            unset($data['region']);
            unset($data['total']);
            return $data;
        }

        public function apiGetList($location, $type, $page=1, $user=false, $ref=false) {
            global $options;
            global $search;
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

            if ($type == "featured") {
                $result = $this->formatResult( $this->promotedData(false, $location['longitude'], $location['latitude']) );
                
                $data['counts']['current_page'] = 1;
                $data['counts']['total_page'] = 1;
                $data['counts']['rows_on_current_page'] = count($result);
                $data['counts']['max_rows_per_page'] = count($result);
                $data['counts']['total_rows'] = count($result);
                $data['data'] = $result;
            } else if ($type == "map") {
                $data['data'] = $this->getMapHome($location['longitude'], $location['latitude']);
            } else if ($type == "category") {
                $result = $search->catSearchData($location, $ref, $start, $limit);
                $data['counts']['current_page'] = $page;
                $data['counts']['total_page'] = ceil($result['count']/$limit);
                $data['counts']['rows_on_current_page'] = count($result['data']);
                $data['counts']['max_rows_per_page'] = $limit;
                $data['counts']['total_rows'] = $result['count'];
                $data['data'] = $this->formatResult( $result['data'] );
            } else if ($type == "search") {
                $result = $search->jobSearchData($location, $ref, $start, $limit);
                $data['counts']['current_page'] = $page;
                $data['counts']['total_page'] = ceil($result['count']/$limit);
                $data['counts']['rows_on_current_page'] = count($result['data']);
                $data['counts']['max_rows_per_page'] = $limit;
                $data['counts']['total_rows'] = $result['count'];
                $data['data'] = $this->formatResult( $result['data'] );
            } else if ($type == "keywordsearch") {
                $result = $search->keywordSearchData($location, $ref, $start, $limit);
                $data['counts']['current_page'] = $page;
                $data['counts']['total_page'] = ceil($result['count']/$limit);
                $data['counts']['rows_on_current_page'] = count($result['data']);
                $data['counts']['max_rows_per_page'] = $limit;
                $data['counts']['total_rows'] = $result['count'];
                $data['data'] = $this->formatResult( $result['data'] );
            } else if ($type == "aroundme") {
                $result = $this->aroundMeData($location['longitude'], $location['latitude'], $start, $limit);

                $data['counts']['current_page'] = $page;
                $data['counts']['total_page'] = ceil($result['dataCount']/$limit);
                $data['counts']['rows_on_current_page'] = count($result['list']);
                $data['counts']['max_rows_per_page'] = $limit;
                $data['counts']['total_rows'] = $result['dataCount'];
                $data['data'] = $this->formatResult( $result['list'] );
            } else if ($type == "recent") {
                $result = $this->recentlyPostedData($location['longitude'], $location['latitude'], $start, $limit);

                $data['counts']['current_page'] = $page;
                $data['counts']['total_page'] = ceil($result['dataCount']/$limit);
                $data['counts']['rows_on_current_page'] = count($result['list']);
                $data['counts']['max_rows_per_page'] = $limit;
                $data['counts']['total_rows'] = $result['dataCount'];
                $data['data'] = $this->formatResult( $result['list'] );
            } else if (($type == "all") || ($type == "active") || ($type == "conversation") || ($type == "on-going") || ($type == "running") || ($type == "past") || ($type == "archive") || ($type == "draft") || ($type == "saved")) {
                $result = $this->listAllWalletData($user, $type, $start, $limit);

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

        public function apiMegotiate($array, $action="post") {
            global $projects_negotiate;
            global $userPostedAds;
            if ($action == "post") {
                if ($array['user_id'] == $array['user_r_id']) {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "You can not negotiate price with yourself. user_r_id must be the user ID of the advert owner if you are responding to an ad, or the user id of the user you are responding to if you own the ad";
                } else {
                    $array['m_type'] = "negotiate_charges";
                    $checkNegotiate['project'] = $array['project_id'];
                    $checkNegotiate['user'] = $array['user_id'];
                    $checkNegotiate['user_r'] = $array['user_r_id'];
                    if ( $projects_negotiate->checkCurrent($checkNegotiate) > 0) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "A negotiation request already sent. Please check your messages";
                    } else {
                        $add = $userPostedAds->negotiatePrize($array);

                        if ($add) {
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['additional_message'] = "Negotiation Request Sent";
                        } else {
                            $return['status'] = "500";
                            $return['message'] = "Internal Server Error";
                        }
                    }
                }
            } else if ($action == "check") {
                $checkNegotiate['project'] = $array['project_id'];
                $checkNegotiate['user'] = $array['user_id'];
                $checkNegotiate['user_r'] = $array['user_r_id'];
                if ( $projects_negotiate->checkCurrent($checkNegotiate) > 0) {
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['additional_message'] = "A negotiation request already sent. Please check your messages";
                } else {
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['additional_message'] = "No negotiation request sent";
                }
            } else if ($action == "respond") {
                $get_data = $projects_negotiate->listOne($array['neg_id']);
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
                        $add = $userPostedAds->negotiateResponse($data);
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

        public function apiFeatured($array, $action="post") {
            global $country;
            global $userPostedAds;
            $data = $this->listOne($array['project_id']);
            if ($data) {
                $regionData = $country->getLoc($data['region'], "ref");
                if ($action == "check") {
                    if ($data['is_featured'] == 1) {
                        $f_data['status'] = "Yes";
                        $f_data['expires'] = $data['is_featured_time'];
                    } else {
                        $f_data['status'] = "no";
                    }
                    $f_data['amount_per_dat'] = $regionData['featured_ad'];
                    $f_data['tax'] = $regionData['tax']/100;
                    $f_data['currency'] = $regionData['currency'];
                    $f_data['currency_symbol'] = $regionData['currency_symbol'];
                
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['is_featured'] = $f_data;
                } else {
                    $featured = $array['days'];
                    $featured_ad = $regionData['featured_ad'];
                    $net_total = $featured*$featured_ad;
                    $tax = $regionData['tax'];
                    $tax_total = ($tax/100)*$net_total;
                    $gross_total = $net_total+$tax_total;
                    
                    $array['tx_type'] = "featured_ad";
                    $array['tx_type_id'] = $array['project_id'];
                    $num_days = $array['days'];
                    $array['net_total'] = $net_total;
                    $array['tax_total'] = $tax_total;
                    $array['gross_total'] = $gross_total;
                    $array['region'] = $regionData['ref'];
                    unset($array['project_id']);
                    unset($array['days']);

                    $post = $userPostedAds->postTransaction($array);
                    if ($post['code'] == 1) {
                        $this->setFeatured($array['tx_type_id'], $num_days);
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['additional_message'] = $post['message'];
                    } else {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "Payment Error: ".$post['message'];
                    }
                }  
            } else {
                $return['status'] = "404";
                $return['message'] = "Not Found";
                $return['additional_message'] = "Invalid Ad or Posting";
            }

            return $return;
        }

        public function apiHours($array, $action="post") {
            global $userPostedAds;
            global $projects_data;
            $data = $this->listOne($array['project_id']);
            if ($data) {
                if ($array['user_id'] != $data['user_id']) {
                    $user_r = $data['user_id'];
                    $user_id = $array['user_id'];
                } else {
                    $user_r = $array['user_r_id'];
                    $user_id = $data['user_id'];
                }

                $check = $userPostedAds->checkMilestone(array("project" => $array["project_id"], "user" => $user_id, "user_r" => $user_r ), "count", 1);
                if ($action == "approve") {
                    
                    if ($data['user_id'] == $array['user_id']) {
                        $user_id = $data['user_id'];
                        $user_r_id = $data['client_id'];
                    } else {
                        $user_id = $data['client_id'];
                        $user_r_id = $data['user_id'];
                    }
                    
                    if ($data['project_type'] == "client") {
                        $approveer = $data['user_id'];
                    } else {
                        $approveer = $data['client_id'];
                    }
                    $d_array['project'] = $data['ref'];
                    $d_array['user'] = $user_id;
                    $d_array['user_r'] = $user_r_id;
                    $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
                    $raw_get_data = unserialize($get_data['data_field']);

                    if ($array['user_id'] == $approveer) {
                        if ($raw_get_data['content'] > 0) { 
                            $array['log'] = "log_hours";
                            $array['project_data_id'] = $array['hour_request_id'];
                            unset($array['hour_request_id']);
                            unset($array['user_id']);
                            $add = $projects_data->updateDate($array);
                            if ($add) {
                                $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
                                $raw_get_data = unserialize($get_data['data_field']);
                                $return['status'] = "200";
                                $return['message'] = "OK";
                                $return['hours']['complete'] = $raw_get_data['complete'];
                                $return['hours']['remaining'] = $raw_get_data['content'];
                            } else {
                                $return['status'] = "500";
                                $return['message'] = "Internal Server Error";
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "You have no more hours to authorize.";
                        }
                    } else {
                        //cannot approve
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "You can not autjorize this time duration.";
                    }
                } else if ($action == "post") {
                    if (($array['user_id'] == $data['user_id']) && (intval($user_r) < 1)) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "Responder ID must be defined in POST data as user_r_id";
                    } else {
                        if ($userPostedAds->checkMilestone(array("project" => $array['project_id'], "user" => $user_id, "user_r" => $user_r ), "count", 1) == 1) {
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['additional_message'] = "A duration has been proposed and approved";
                        } else if ($userPostedAds->checkMilestone(array("project" => $array['project_id'], "user" => $user_id, "user_r" => $user_r  )) == 0) { 
                            if ($array['user_id'] != $data['user_id']) {
                                
                                $array['type'] = "hours";
                                $array['project'] = $array['project_id'];
                                $array['hour_count'] = $array['hours'];
                                $array['user_r_id'] = $data['user_id'];
                                unset($array['project_id']);
                                unset($array['hours']);
                                $add = $userPostedAds->processHours($array);
                                if ($add) {
                                    $return['status'] = "200";
                                    $return['message'] = "OK";
                                    $return['additional_message'] = "Duration defined, waiting for approval";
                                } else {
                                    $return['status'] = "500";
                                    $return['message'] = "Internal Server Error";
                                }
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "You can not propose a duration in hours for this ad since you are the owner. once a duration has been proposed, you will be able to review and approve the proposal here.";
                            }
                        } else {
                            if ($projects_data->findMe(array("project" => $array['project_id'], "user" => $array['user_id'] )) == 0) {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "You have a duration proposal to approve.";
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "Your sent duration proposal is waiting for approval.";
                            }
                        }
                    } 
                } else if ($action == "check") {
                    if (($array['user_id'] == $data['user_id']) && (intval($user_r) < 1)) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "Responder ID must be defined in URL after project ID";
                    } else {
                        $array["project"] = $array["project_id"];
                        $array["user"] = $user_id;
                        $array["user_r"] = $user_r;
                        $milstoneData = $projects_data->checkCurrent($array, "getRow");
                        $rawData = unserialize($milstoneData['data_field']);
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['hour_request_id'] = $milstoneData['ref'];
                        $return[$rawData['data_type']] = $rawData['content'];
                        $return['can_approve'] = ($milstoneData['user_r_id'] == $array['user_id']? "Yes":"No");
                        $return['is_approved'] = ($check == 0? "No" : "Yes");
                    }
                } else if ($action == "respond") {
                    $get_data = $projects_data->listOne($array['hour_request_id']);

                    if ($get_data) {
                        if ($check == 0) {
                            if ($array['reponse'] == "y") {
                                $res['project'] = $get_data['project_id'];
                                $res['ref'] = $array['hour_request_id'];
                                $add = $userPostedAds->approveMilestone($res, "duration");
                                
                                if ($add) {
                                    $return['status'] = "200";
                                    $return['message'] = "OK";
                                    $return['additional_message'] = "Duration Proposal Approved";
                                } else {
                                    $return['status'] = "501";
                                    $return['message'] = "Not Implemented";
                                    $return['additional_message'] = "There was an error while approving the Duration proposal";
                                }
                            } else if ($array['reponse'] == "n") {
                                if (!isset($array['comment'])) {
                                    $return['status'] = "501";
                                    $return['message'] = "Not Implemented";
                                    $return['additional_message'] = "You must add a comment for a rejection";
                                } else {
                                    $res['project'] = $get_data['project_id'];
                                    $res['ref'] = $array['hour_request_id'];
                                    $res['comment'] = $array['comment'];
                                    $add = $userPostedAds->rejectMilestone($res, "duration");
                                    
                                    if ($add) {
                                        $return['status'] = "200";
                                        $return['message'] = "OK";
                                        $return['additional_message'] = "Duration Proposal Rejected";
                                    } else {
                                        $return['status'] = "500";
                                        $return['message'] = "Internal Server Error";
                                    }
                                }
                            }
                        }  else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = " A duration has been proposed and approved";
                        }
                    } else {
                        $return['status'] = "404";
                        $return['message'] = "Not Found";
                        $return['additional_message'] = "Hour data with 'hour_request_id' ".$array['hour_request_id']." not found";
                    }

                }
            } else {
                $return['status'] = "404";
                $return['message'] = "Not Found";
                $return['additional_message'] = "Invalid Ad or Posting";
            }
            return $return;
        }

        public function apiMilestone($array, $action="post") {
            global $userPostedAds;
            global $projects_data;
            $data = $this->listOne($array['project_id']);
            if ($data) {
                if ($array['user_id'] != $data['user_id']) {
                    $user_r = $data['user_id'];
                    $user_id = $array['user_id'];
                } else {
                    $user_r = $array['user_r_id'];
                    $user_id = $data['user_id'];
                }

                $check = $userPostedAds->checkMilestone(array("project" => $array["project_id"], "user" => $user_id, "user_r" => $user_r ), "count", 1);
                if (($action == "approve") || ($action == "request")) {
                    if ($data['user_id'] == $array['user_id']) {
                        $user_id = $data['user_id'];
                        $user_r_id = $data['client_id'];
                    } else {
                        $user_id = $data['client_id'];
                        $user_r_id = $data['user_id'];
                    }
                    
                    if ($data['project_type'] == "client") {
                        $approveer = $data['user_id'];
                        $requester = $data['client_id'];
                    } else {
                        $approveer = $data['client_id'];
                        $requester = $data['user_id'];
                    }
                    $d_array['project'] = $data['ref'];
                    $d_array['user'] = $user_id;
                    $d_array['user_r'] = $user_r_id;
                    $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
                    $raw_get_data = unserialize($get_data['data_field']);

                    if ($action == "approve") {
                        $array['log'] = "log_mil";
                        $msg = "Approve ";
                    } else if ($action == "request") {
                        $array['log'] = "log_mil_review";
                        $msg = "request for approval for ";
                    }

                    if ((($array['user_id'] == $approveer) && ($action == "approve")) || ($array['user_id'] == $requester) && ($action == "request")) {
                        $array['milestone_data_id'] = $array['milestone_index'];
                        $array['project_data_id'] = $array['milestone_request_id'];
                        unset($array['milestone_request_id']);
                        unset($array['milestone_index']);
                        unset($array['user_id']);
                        $add = $projects_data->updateDate($array);
                        if ($add) {
                            $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
                            $raw_get_data = unserialize($get_data['data_field']);
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['milestone'] = $raw_get_data['content'];
                        } else {
                            $return['status'] = "500";
                            $return['message'] = "Internal Server Error";
                        }
                    } else {
                        //cannot approve
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "You can not ".$msg." this milestone.";
                    }
                } else if ($action == "post") {
                    if (($array['user_id'] == $data['user_id']) && (intval($user_r) < 1)) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "Responder ID must be defined in POST data as user_r_id";
                    } else {
                        if ($userPostedAds->checkMilestone(array("project" => $array['project_id'], "user" => $user_id, "user_r" => $user_r ), "count", 1) == 1) {
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['additional_message'] = "A milestone has been proposed and approved";
                        } else if ($userPostedAds->checkMilestone(array("project" => $array['project_id'], "user" => $user_id, "user_r" => $user_r  )) == 0) { 
                            if ($array['user_id'] != $data['user_id']) {
                                $array['data']['content'] = $array['content'];
                                $array['data']['data_type'] = "milestone";
                                $array['project'] = $array['project_id'];
                                unset($array['user_r_id']);
                                unset($array['user_id']);
                                $array['user_r_id'] = $user_r;
                                $array['user_id'] = $user_id;
                                unset($array['project_id']);
                                unset($array['content']);
                                $add = $userPostedAds->processMilestone($array);
                                if ($add) {
                                    $return['status'] = "200";
                                    $return['message'] = "OK";
                                    $return['additional_message'] = "Milestone defined, waiting for approval";
                                } else {
                                    $return['status'] = "500";
                                    $return['message'] = "Internal Server Error";
                                }
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "You can not propose a Milestone for this ad since you are the owner. once a Milestone has been proposed, you will be able to review and approve the proposal here.";
                            }
                        } else {
                            if ($projects_data->findMe(array("project" => $array['project_id'], "user" => $array['user_id'] )) == 0) {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "You have a milestone proposal to approve.";
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "Your sent milestone proposal is waiting for approval.";
                            }
                        }
                    } 
                } else if ($action == "check") {
                    if (($array['user_id'] == $data['user_id']) && (intval($user_r) < 1)) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "Responder ID must be defined in URL after project ID";
                    } else {
                        $array["project"] = $array["project_id"];
                        $array["user"] = $user_id;
                        $array["user_r"] = $user_r;
                        $milstoneData = $projects_data->checkCurrent($array, "getRow");
                        $rawData = unserialize($milstoneData['data_field']);
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['milestone_request_id'] = $milstoneData['ref'];
                        $return[$rawData['data_type']] = $rawData['content'];
                        $return['can_approve'] = ($milstoneData['user_r_id'] == $array['user_id']? "Yes":"No");
                        $return['is_approved'] = ($check == 0? "No" : "Yes");
                    }
                } else if ($action == "respond") {
                    $get_data = $projects_data->listOne($array['milestone_request_id']);

                    if ($get_data) {
                        if ($check == 0) {
                            if ($array['reponse'] == "y") {
                                $res['project'] = $get_data['project_id'];
                                $res['ref'] = $array['milestone_request_id'];
                                $add = $userPostedAds->approveMilestone($res);
                                
                                if ($add) {
                                    $return['status'] = "200";
                                    $return['message'] = "OK";
                                    $return['additional_message'] = "Milestone Proposal Approved";
                                } else {
                                    $return['status'] = "500";
                                    $return['message'] = "Internal Server Error";
                                }
                            } else if ($array['reponse'] == "n") {
                                if (!isset($array['comment'])) {
                                    $return['status'] = "501";
                                    $return['message'] = "Not Implemented";
                                    $return['additional_message'] = "You must add a comment for a rejection";
                                } else {
                                    $res['project'] = $get_data['project_id'];
                                    $res['ref'] = $array['hour_request_id'];
                                    $res['comment'] = $array['comment'];
                                    $add = $userPostedAds->rejectMilestone($res);
                                    
                                    if ($add) {
                                        $return['status'] = "200";
                                        $return['message'] = "OK";
                                        $return['additional_message'] = "Milestone Proposal Rejected";
                                    } else {
                                        $return['status'] = "500";
                                        $return['message'] = "Internal Server Error";
                                    }
                                }
                            }
                        }  else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = " A Milestone has been proposed and approved";
                        }
                    } else {
                        $return['status'] = "404";
                        $return['message'] = "Not Found";
                        $return['additional_message'] = "Hour data with 'hour_request_id' ".$array['hour_request_id']." not found";
                    }

                }
            } else {
                $return['status'] = "404";
                $return['message'] = "Not Found";
                $return['additional_message'] = "Invalid Ad or Posting";
            }
            return $return;
        }

        public function apiDelete($array) {
            global $userPostedAds;
            $data = $this->listOne($array['ref']);
            if ($data) {
                if ($array['user_id'] == $data['user_id']) {
                    if (($data['status'] == "ACTIVE") || ($data['status'] == "NEW")) {
                        $rem = $userPostedAds->removeDraft($array['ref']);

                        if ($rem) {
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['additional_message'] = "Draft ad removed successfully";
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "Draft ad not removed successfully";
                        }
                    }
                } else {
                    $return['status'] = "403";
                    $return['message'] = "Forbidden";
                }
                
            } else {
                $return['status'] = "404";
                $return['message'] = "Not Found";
                $return['additional_message'] = "Invalid Ad or Posting";
            }

            return $return;
        }

        public function apiCounter($ref) {
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['count'] = $this->visitors($ref);

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
                $data = $this->listOne($array['project_id']);
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
                            $initialComment = $messages->getPage($array['project_id'], $user_r, $user_id);
                            for ($i = 0; $i < count($initialComment); $i++) {
                                $user['id'] = $initialComment[$i]['user_id'];
                                $user['name'] = $users->listOnValue($initialComment[$i]['user_id'], "screen_name");
                                $user['rating']['score'] = round($rating->getRate($initialComment[$i]['user_id']), 2);
                                $user['rating']['total'] = 5;
                                $initialComment[$i]['user_id'] = $user;
                                unset($user);
                                $user['id'] = $initialComment[$i]['user_r_id'];
                                $user['name'] = $users->listOnValue($initialComment[$i]['user_r_id'], "screen_name");
                                $user['rating']['score'] = round($rating->getRate($initialComment[$i]['user_r_id']), 2);
                                $user['rating']['total'] = 5;
                                $initialComment[$i]['user_r_id'] = $user;
                                if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                                    $r_data = explode("_", $initialComment[$i]['m_type_data']);
                                    $re_data['value'] = $r_data[0];
                                    $re_data['request_id'] = $r_data[1];
                                }
                                $initialComment[$i]['m_type_data'] = $re_data;

                                unset($user);

                                unset($initialComment[$i]['project_id']);
                                unset($initialComment[$i]['status']);
                                unset($initialComment[$i]['modify_time']);
                            }
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['data'] = $initialComment;

                        } else if ($action == "new") {
                            $initialComment = $messages->getLast($array['project_id'], $user_id, $user_r);
                            $user['id'] = $initialComment['user_id'];
                            $user['name'] = $users->listOnValue($initialComment['user_id'], "screen_name");
                            $user['rating']['score'] = round($rating->getRate($initialComment['user_id']), 2);
                            $user['rating']['total'] = 5;
                            $initialComment['user_id'] = $user;
                            unset($user);
                            $user['id'] = $initialComment['user_r_id'];
                            $user['name'] = $users->listOnValue($initialComment['user_r_id'], "screen_name");
                            $user['rating']['score'] = round($rating->getRate($initialComment['user_r_id']), 2);
                            $user['rating']['total'] = 5;
                            $initialComment['user_r_id'] = $user;
                            if ($initialComment['m_type'] == "negotiate_charges" ) {
                                $r_data = explode("_", $initialComment['m_type_data']);
                                $re_data['value'] = $r_data[0];
                                $re_data['request_id'] = $r_data[1];
                            }
                            $initialComment['m_type_data'] = $re_data;

                            unset($user);

                            unset($initialComment['project_id']);
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

        public function apiAPI($array) {
            global $country;
            global $messages;
            global $users;
            global $userPostedAds;
            global $wallet;
            $approve = false;
            $data = $this->listOne($array['project_id']);
            if ($data) {
                $ref = $data['ref'];
                
                if ($array['user_id'] != $data['user_id']) {
                    $user_r = $respondsRef = $data['user_id'];
                    $user_id = $responderRef = $array['user_id'];
                } else {
                    $user_r = $responderRef = $respondsRef = $array['user_r_id'];
                    $user_id = $data['user_id'];
                }
                
                $initialComment = $messages->getPage($array['project_id'], $user_r, $user_id);

                if (($array['user_id'] == $data['user_id']) && (intval($user_r) < 1)) {
                    $return['status'] = "501";
                    $return['message'] = "Not Implemented";
                    $return['additional_message'] = "Responder ID must be defined in URL after the ad. ID";
                } else {
                    if ($data['project_type'] == "vendor") {
                        $wallet_user = $responderRef;
                        $wallet_collector = $data['user_id'];
                    } else {
                        $wallet_user = $data['user_id'];
                        $wallet_collector = $responderRef;
                    }
                    
                    $wallet_balance = floatval($wallet->balance($wallet_user, $data['region']));
                    $feeData = $this->getFeeTotal( array("id"=>$ref, "wallet_user"=>$wallet_user, "wallet_collector"=>$wallet_collector) );
                    $fee = $feeData['fee'];

                    if ($data['billing_type'] == "per_job") {
                        $approve = true;
                    }
                    if ($userPostedAds->checkMilestone(array("project" => $ref, "user" => $user_id, "user_r" => $user_r ), "count", 1) == 1) {
                        $approve = true;
                    }
                    if ($userPostedAds->checkMilestone(array("project" => $ref, "user" => $user_id, "user_r" => $user_r ), "count", 1) == 1) {
                        $approve = true;
                    }

                    if (($approve == true) && ($data['status'] == "ACTIVE")) {
                        if ($wallet_balance < $fee) {
                            //not enough money
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            if ($wallet_user == $user_id) {
                                $return['additional_message'] = "You do not have enough money in your wallet. You must add a minimum of ".$country->getCountryData( $data['country'] )." ".number_format(abs($wallet_balance-$fee), 2)." to your wallet balance to be able to authorize this ad";
                            } else {
                                $return['additional_message'] = "This ad. can not be approved until ".$users->listOnValue($respondsRef, "screen_name")."'s wallet is funded. We have notified ".$users->listOnValue($respondsRef, "screen_name");
                            }
                        } else if (($wallet_balance >= $fee) && ( ($user_id == $data['user_id']))) {
                            if (($userPostedAds->checkNegotiate(array("project" => $ref, "user" => $user_id, "user_r" => $user_r )) == 0) && ($userPostedAds->checkNegotiate(array("project" => $ref, "user" => $user_r, "user_r" => $user_id)) == 0) && (count($initialComment) > 0) ) {
                                //activate
                                $approveArray['id'] = $data['ref'];
                                $approveArray['responder'] = $responderRef;
                                $run = $this->authorizeProject($approveArray);

                                if ($run['status'] == "COMPLETE") {
                                    $return['status'] = "200";
                                    $return['message'] = "OK";
                                } else {
                                    $return['status'] = "500";
                                    $return['message'] = "Internal Server Error";
                                }
                            } else {
                                $return['status'] = "501";
                                $return['message'] = "Not Implemented";
                                $return['additional_message'] = "You can not activate this ad. at this time";
                            }
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "You can not activate this ad. at this time. One or more conditions has not been met";
                        }
                    }
                }
                
            } else {
                $return['status'] = "404";
                $return['message'] = "Not Found";
                $return['additional_message'] = "Invalid Ad or Posting";
            }
            return $return;
        }

        public function apiComplete($array, $action="complete") {
            global $projects_data;
            $data = $this->listOne($array['project_id']);
            if ($data) {
                $ref = $array['project_id'];
                $user_id = $data['user_id'];
                $user_r_id = $data['client_id'];

                if ($data['project_type'] == "client") {
                    $approveer = $data['user_id'];
                    $not_approver = $data['client_id'];
                } else {
                    $approveer = $data['client_id'];
                    $not_approver = $data['user_id'];
                }

                if ($data['billing_type'] != "per_job") {
                $d_array['project'] = $ref;
                $d_array['user'] = $user_id;
                $d_array['user_r'] = $user_r_id;
                $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
                $raw_get_data = unserialize($get_data['data_field']);
                }
                $goAhead = false;
                if ($action == "complete") {
                    if (($not_approver == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "You can not perform this action, you can only request that the ownser cofirms the job";
                    } else if (($approveer == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                        if (($raw_get_data['data_type'] == "hours") || ($raw_get_data['data_type'] == "milestone")) {
                            if ($raw_get_data['status'] == "Complete") {
                                $goAhead = true;
                            }
                        } else {
                            $goAhead = true;
                        }

                        if ($goAhead == true){
                            $add = $this->approve($ref, "approve", $approveer);
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
                            $return['additional_message'] = "You can not approve this ad yet as all criterias for approval has not been met";
                        }
                    } else {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "You can not allowed to perform this action";
                    }
                } else if ($action == "request") {
                    if (($approveer == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "You can not perform this request, you can only approve the Job";
                    } else if (($not_approver == $array['user_id']) && ($data['status'] != "COMPLETED")) {
                        if (($raw_get_data['data_type'] == "hours") || ($raw_get_data['data_type'] == "milestone")) {
                            if ($raw_get_data['status'] == "Complete") {
                                $goAhead = true;
                            }
                        } else {
                            $goAhead = true;
                        }

                        if ($goAhead == true){
                            $add = $this->approve($ref, "request_approve", $not_approver);
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
                            $return['additional_message'] = "You can not request that this ad be approved yet as all criterias for approval has not been met";
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
            $data = $this->listOne($array['project_id']);

            if ($data) {
                $allow = false;
                if ($data['user_id'] == $array['user_id']) {
                    $tagline = "user_id";
                    $user_id = $data['client_id'];
                    $user_r_id = $data['user_id'];
                    if ($data['project_type'] == "client") {
                        $rateType = "vendors";
                    } else {
                        $rateType = "clients";
                    }
                    $allow = true;
                } else {
                    $tagline = "client_id";
                    $user_id = $data['user_id'];
                    $user_r_id = $data['client_id'];
                    if ($data['project_type'] == "client") {
                        $rateType = "clients";
                    } else {
                        $rateType = "vendors";
                    }
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
                            $checkRate = $rating->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "project_id", $array['project_id']);
                            $checkComment = $rating_comment->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "project_id", $array['project_id'], "ref", "ASC", "AND", false, false, "getRow");
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
                            $checkRate = $rating->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "project_id", $array['project_id']);
                            if (count($checkRate) == 0) {
                                for ($i = 0; $i < count($array['rating']); $i++) {
                                    $list['rating'][$array['rating'][$i]['question_id']] = $array['rating'][$i]['score'];
                                }
                                $list['user_id'] = $user_id;
                                $list['reviewed_by'] = $user_r_id;
                                $list['project_id'] = $array['project_id'];
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

        public function postApi($array, $action="post") {
            global $country;
            global $media;
            global $userProject;
            if (($array['project_type'] != "client") && ($array['project_type'] != "vendor")) {
                $return['status'] = "501";
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "Invalid project_type, accepted strings client, vendor";
            } else if (($array['billing_type'] != "per_hour") && ($array['project_type'] != "per_mil") && ($array['project_type'] != "per_job")) {
                $return['status'] = "501";
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "Invalid billing_type, accepted strings per_hour, per_mil, per_job";
            } else if ((intval($array['allow_remote']) != 0) && (intval($array['allow_remote']) != 1)) {
                $return['status'] = "501";
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "Invalid allow_remote, accepted integers 0, 1";
            } else {
                if ($action == "post") {
                    $array['category_id'] = implode(",", $array['category_id']);

                    $addressData = $this->googleGeoLocation(false, false, $array['address']);
                    $array['city'] = $addressData['city'];
                    $array['state'] = $addressData['province_code'];
                    $array['postal_code'] = $addressData['postal_code'];
                    $array['country'] = $addressData['country'];
                    $array['lat'] = $addressData['latitude'];
                    $array['lng'] = $addressData['longitude'];
                    $array['region'] = $country->getLoc($addressData['country_code'])['ref'];

                    if ($array['ref'] == 0) {
                        unset($array['ref']);
                        $array['project_code'] = $this->getCode();
                        
                        if ($array['project_type'] == "vendor") {
                            $payment_status = 0;
                        } else {
                            $payment_status = 1;
                        }
                        
                        $array['payment_status'] = $payment_status;
                    }
                    $image = $array['image'];
                    unset($array['image']);
                    if (((!isset($array['card']) || intval($array['card']) < 1)) && ($array['payment_status'] != "2") && ($array['project_type'] == "client")) {
                        $return['status'] = "501";
                        $return['message'] = "Not Implemented";
                        $return['additional_message'] = "We need to confirm your Credit Cards details to continue";
                    } else {
                        unset($array['card']);
                        $add = $this->create($array);
                        if ($add) {
                            $mediaArray = array();
                            $mediaArray['user_id'] = $array['user_id'];
                            $mediaArray['project_id'] = $add;
                            if (count($image) > 0) {
                                $media->create($image, $mediaArray, true);
                            }
                            $userProject->approveProject($add);
                            $return['status'] = "200";
                            $return['message'] = "OK";
                            $return['project_id'] = $add;
                        } else {
                            $return['status'] = "501";
                            $return['message'] = "Not Implemented";
                            $return['additional_message'] = "We could not list this ad, please try again ";
                        }
                    }
                }
            }

            return $return;
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`projects` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `client_id` INT NOT NULL, 
                `category_id` VARCHAR(1000) NOT NULL, 
                `project_code` VARCHAR(20) NOT NULL, 
                `project_name` VARCHAR(50) NOT NULL, 
                `project_dec` VARCHAR(1000) NOT NULL, 
                `project_type` VARCHAR(50) NULL, 
                `tag` TEXT NOT NULL, 
                `allow_remote` INT NOT NULL, 
                `address` VARCHAR(1000) NULL, 
                `city` VARCHAR(50) NULL, 
                `state` VARCHAR(50) NULL, 
                `postal_code` VARCHAR(50) NULL, 
                `country` VARCHAR(50) NULL, 
                `region` INT NOT NULL, 
                `lat` DOUBLE NULL, 
                `lng` DOUBLE NULL, 
                `billing_type` VARCHAR(50) NULL, 
                `default_fee` DOUBLE NULL, 
                `page_visit` INT NOT NULL, 
                `is_featured` INT NOT NULL, 
                `is_featured_time` VARCHAR(50) NOT NULL, 
                `start_date` VARCHAR(50) NULL, 
                `end_date` VARCHAR(50) NULL, 
                `status` varchar(20) NOT NULL DEFAULT 'NEW',
                `payment_status` INT NOT NULL,
                `review_status` INT NOT NULL,
                `client_rate` INT NOT NULL,
                `user_rate` INT NOT NULL,
                `review_status_time` VARCHAR(50) NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`),
                FULLTEXT KEY (`address`),
                FULLTEXT KEY (`tag`),
                FULLTEXT KEY (`project_dec`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`projects`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`projects`";

            $this->query($query);
        }
    }
?>