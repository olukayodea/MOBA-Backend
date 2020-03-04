<?php
    class payment_card extends database {
        /*  create users
        */
        public function create($array) {
            $add_card = $this->gateway_create_profile($array);

            if ($add_card['suuccess'] === true) {
                $get_count = count($this->getSortedList($array['user_id'], 'user_id'));
                $data['user_id'] = $array['user_id'];
                $data['pan'] = substr( $array['cardno'], -4);
                $data['expiry_year'] = $array['yy'];
                $data['expiry_month'] = $array['mm'];
                $data['card_name'] = $array['cc_first_name']." ".$array['cc_last_name'];
                $data['gateway_token'] = $add_card['response'];
                if (isset($add_card['data'])) {
                    $data['temp_data'] = $add_card['data'];
                } else {
                    $data['status'] = "PENDING";
                }
                $data['status'] = $add_card['status'];
                if ($get_count == 0) {
                    $data['is_default'] = 1;
                } else {
                    $data['is_default'] = 0;
                }
                $create = $this->insert("payment_card", $data);
                if ($create) {
                    if ($add_card['message'] == "complete") {
                        //send email
                        $this->sendMail($create);

                        $return = array('status' => 'OK', 'message' => 'complete');
                    } else {
                        if ($data['status'] == "PENDING-BILLING") {
                            $fields = "billingzip, billingcity, billingaddress, billingstate, billingcountry";
                        } else if ($data['status'] == "PENDING-PIN") {
                            $fields = "pin";
                        } else {
                            $fields = "otp_code";
                        }
                        $return = array('status' => 'OK', 'message' => strtolower( $add_card['message']), 'additional_message' => $add_card['additional_message'], 'fields' => $fields );
                    }
                    $return['card_id'] = $create;
                } else {
                    //$this->bambora_remove_profile($token);
                    //$return = array('status' => 'Error', 'message' => 'An error occured');
                }
            } else {
                $return = array('status' => 'Error', 'message' => strtolower( $add_card['message'] )." ".$add_card['details'][0]['message']);
            }

            return $return;
        }

        function sendMail($id) {
            global $users;
            global $alerts;
            global $notifications;
			$data = $this->listOne($id);
            //send email
            $tag = "you have added card **** **** **** ".$data['pan']." expiring ".$data['expiry_month']."/".$data['expiry_year']." to your account. <a href='".URL."paymentCards'>Sigin in</a> to your MOBA Account to learn more";

            $user_data = $users->listOne($data['user_id']);
            $client = $user_data['last_name']." ".$user_data['other_names'];
            $subjectToClient = "Payment Card Update";
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

            $data["to"] = $data['user_id'];
            $data["title"] = "System Notification";
            $data["body"] = "Payment Card Update";
            $data['data']['page_name'] = "home";
            $notifications->sendPush($data);
        }

        function getList($start=false, $limit=false, $order="card_name", $dir="ASC", $type="list") {
            return $this->lists("payment_card", $start, $limit, $order, $dir, false, $type);
        }

		function getSingle($name, $tag="card_name", $ref="ref") {
            return $this->getOneField("payment_card", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("payment_card", $id, "ref");
        }

        function setDefault($id) {
            $getFormer = $this->getSingle("1", "ref", "is_default");
            $this->updateOne("payment_card", "is_default", 0, $getFormer, "ref");
            $this->updateOne("payment_card", "is_default", 1, $id, "ref");
            return true;
        }

        function getDefault($id) {
            return $this->getSortedList($id, "user_id", "is_default", 1, false, false, "ref", "ASC", "AND", false, false, "getRow");
        }

        function remove($id, $user=false) {
            $data = $this->listOne($id);
            if (($user == false) || (($user == true) && ($user == $data['user_id']))) {
                if ($data['is_default'] == 0) {
                    if ($this->bambora_remove_profile($data['gateway_token'])) {
                        $this->delete("payment_card", $id);
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return "0000";
            }
        }

        function toggleStatus($id) {
            $data = $this->listOne($id);
            if ($data['status'] == "ACTIVE") {
                $updateData = "INACTIVE";
            } else if ($data['status'] == "INACTIVE") {
                $updateData = "ACTIVE";
            }

            $this->updateOne("payment_card", "status", $updateData, $id, "ref");
            return true;
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list", $extraConditions=false) {
            return $this->sortAll("payment_card", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type, $extraConditions);
        }

        private function gateway_create_profile($array, $saved=false) {
            global $users;

            $card_id = $array['card_id'];
            if ($saved === false) {
                $userdata = $users->listOne($array['user_id']);
                $data['PBFPubKey'] = fl_public_key;
                $data['cardno'] = str_replace(' ', '', $array['cardno']);
                $data['expirymonth'] = $array['mm'];
                $data['expiryyear'] = $array['yy'];
                $data['currency'] = 'NGN';
                $data['country'] = 'NG';
                $data['amount'] = '300';
                $data['txRef'] = "TP".rand(1, 999).time();

                $data["email"] = $userdata['email'];
                $data["firstname"] = $array['cc_first_name'];
                $data["lastname"] = $array['cc_last_name'];

            } else {
                $data = unserialize( $this->listOne($array['card_id'])['temp_data'] );

                unset($array['card_id']);
                unset($array['user_id']);

                $data = array_merge($data, $array);
                $data['txRef'] = "TP".rand(1, 999).time();
            }
            
            $key = $this->getKey(); 
                
            $dataReq = json_encode($data);
            $post_enc = $this->encrypt3Des( $dataReq, $key );

            $postdata = array(
                'PBFPubKey' => fl_public_key,
                'client' => $post_enc,
                'alg' => '3DES-24');

            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, "https://api.ravepay.co/flwv3-pug/getpaidx/api/charge");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata)); //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
            curl_setopt($ch, CURLOPT_TIMEOUT, 200);
            
            
            $headers = array('Content-Type: application/json');
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $request = curl_exec($ch);
            
            if ($request) {
                $result = json_decode($request, true);
                if ($result['status'] == "success") {
                    $return['suuccess'] = true;
                    if ($result['status'] == "success") {
                        if ($result['message'] == "AUTH_SUGGESTION") {
                            $return['suggested_auth'] = $result['data']['suggested_auth'];
                            if ($result['data']['suggested_auth'] == "NOAUTH_INTERNATIONAL") {
                                $return['additional_message'] = "Enter the billing address for **** **** **** ".substr( $data['cardno'], -4);;
                                $return['status'] = "PENDING-BILLING";
                            } else if ($result['data']['suggested_auth'] == "PIN") {
                                $return['additional_message'] = "Enter the card pin for **** **** **** ".substr( $data['cardno'], -4);;
                                $return['status'] = "PENDING-PIN";
                            }
                            $return['message'] = strtolower( "incomplete" );
                            $data['suggested_auth'] = $result['data']['suggested_auth'];
                            $return['data'] = serialize($data);
                        } else {
                            if ($result['data']['chargeResponseCode'] == "02") {
                                $fields = "otp_code";
                                $return = array('status' => 'OK', 'fields' => $fields );

                                $return['additional_message'] = $result['data']['chargeResponseMessage'];
                                $token = @$result['data']['flwRef'];
                                $status = "PENDING";
                                $return['message'] = "incomplete";

                                $this->updateOne("payment_card", "gateway_token", $token, $card_id, "ref");
                                $this->updateOne("payment_card", "status", $status, $card_id, "ref");
                            } else if ($result['data']['chargeResponseCode'] == "00") {
                                $token = @$result['data']['tx']['chargeToken']['embed_token'];
                                $flwRef = @$result['data']['tx']['flwRef'];

                                $return = array('status' => 'OK', 'message' => strtolower('Complete'));
                                $return['response'] = $token;

                                $return['additional_message'] = "New Card Saved";
                                $return['message'] = strtolower("complete");

                                $status = "ACTIVE";
                                $this->refund($flwRef);

                                $this->updateOne("payment_card", "gateway_token", $token, $card_id, "ref");
                                $this->updateOne("payment_card", "status", $status, $card_id, "ref");
                                $this->updateOne("payment_card", "temp_data", NULL, $card_id, "ref");
                            }
                        }
                    }
                } else {
                    $return['error'] = true;
                    $return['message'] = $result['message'];
                }
            }else{
                if(curl_error($ch)) {
                    $return['error'] = true;
                    $return['message'] = $ch;
                }
            }
            
            curl_close($ch);
            $return['card_id'] = $card_id;

            return $return;
        }

		function verifyPayment($array) {
            $data = $this->listOne($array['card_id']);

            if (isset($array['otp_code'])) {
                $postdata 	= array(
                                'PBFPubKey' => fl_public_key,
                                'transaction_reference' => $data['gateway_token'],
                                'otp' => $array['otp_code']
                            );
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, FL_validatecharge);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata)); //Post Fields
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
                curl_setopt($ch, CURLOPT_TIMEOUT, 200);

                $headers = array('Content-Type: application/json');
                
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
                $request = curl_exec($ch);
                
                curl_close($ch);
                if ($request) {
                    $result = json_decode($request, true);

                    $response['status'] = $result['status'];
                    if ($result['status'] == "success") {
                        $token = @$result['data']['tx']['chargeToken']['embed_token'];
                        $flwRef = @$result['data']['tx']['flwRef'];
                        $status = "ACTIVE";
                        
                        $this->updateOne("payment_card", "gateway_token", $token, $array['card_id'], "ref");
                        $this->updateOne("payment_card", "status", $status, $array['card_id'], "ref");
                        $this->updateOne("payment_card", "temp_data", NULL, $array['card_id'], "ref");

                        $this->sendMail($array['card_id']);

                        $return['suuccess'] = true;
                        
                        $response['status'] = "OK";
                        $response['message'] = strtolower( "complete" );
                        $this->refund($flwRef);
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Validation Failed";
                        $response['additional_message'] = $result['message'];
                        
                        $this->remove($array['card_id']);
                    }
                    return $response;
                }else{

                    $this->remove($array['card_id']);
                    return false;
                }
            } else {
                return $this->gateway_create_profile($array, true);
            }
		}

		function refund($trans_id) {
			$postdata 	= array(
							'seckey' => fl_secret_key,
							'ref' => $trans_id
						);
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, FL_refund);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata)); //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
			curl_setopt($ch, CURLOPT_TIMEOUT, 200);

			$headers = array('Content-Type: application/json');
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
			$request = curl_exec($ch);
			
			curl_close($ch);
			if ($request) {
				$result = json_decode($request, true);

				if ($result['status'] == "success") {
					return true;
				} else {
					return false;
				}
			}else{
				return false;
			}
		}

        function getKey() {
            $hashedkey = md5(fl_secret_key);
            $hashedkeylast12 = substr($hashedkey, -12);

            $seckeyadjusted = str_replace("FLWSECK-", "", fl_secret_key);
            $seckeyadjustedfirst12 = substr($seckeyadjusted, 0, 12);

            $encryptionkey = $seckeyadjustedfirst12.$hashedkeylast12;
            return $encryptionkey;

        }
          
        function encrypt3Des($data, $key) {
            $encData = openssl_encrypt($data, 'DES-EDE3', $key, OPENSSL_RAW_DATA);
            return base64_encode($encData);
        }

        public function bambora_remove_profile($id) {
            return true;
        }
        
        public function listAllUserData($user, $start, $limit) {
            $extra = " AND `status` NOT LIKE '%PENDING%'";
            $return['list'] = $this->getSortedList($user, "user_id", false, false, false, false, "ref", "DESC", "AND", $start, $limit, "list", $extra);
            $return['listCount'] = $this->getSortedList($user, "user_id", false, false, false, false, "ref", "DESC", "AND", false, false, "count", $extra);

            return $return;
        }

        public function apiGetList($type, $user, $ref=false, $page=1) {
            global $options;
            if (intval($page) == 0) {
                $page = 1;
            }
            $current = intval($page)-1;
            
            $limit = $options->get("result_per_page_mobile");
            $start = $current*$limit;
            if ($type == "getOne") {
                if ($ref) {
                    $data = $this->listOne($ref);
                    if ($user == $data['user_id']) {
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['data'] = $data;
                    } else {
                        $return['status'] = "403";
                        $return['message'] = "Forbidden";
                    }
                } else {
                    $return['status'] = "400";
                    $return['message'] = "Bad Request";
                    $return['additional_message'] = "Account ref missing in URL";
                }
            } else if ($type == "default") {
                $data = $this->getDefault($user);
                $return['status'] = "200";
                $return['message'] = "OK";
                $return['data'] = $data;
            } else if ($type == "list") {
                $result = $this->listAllUserData($user, $start, $limit);
                
                $return['status'] = "200";
                $return['message'] = "OK";
                $return['counts']['current_page'] = $page;
                $return['counts']['total_page'] = ceil($result['listCount']/$limit);
                $return['counts']['rows_on_current_page'] = count($result['list']);
                $return['counts']['max_rows_per_page'] = $limit;
                $return['counts']['total_rows'] = $result['listCount'];
                $return['data'] = $result['list'];
            }

            return $return;
        }

        public function processPay($array) {
            global $users;

            $data = $this->listOne($array['card']);
            $usserData = $users->listOne($data['user_id']);

			$postdata 	= array(
                'SECKEY' => fl_secret_key,
                'token' => $data['gateway_token'],
                'currency' => "NGN",
                'country' => "NG",
                'amount' => $array['gross_total'],
                'email' => $usserData['email'],
                'firstname' => $usserData['other_names'],
                'lastname' => $usserData['last_name'],
                'narration' => "MOBA Professional Services",
                'txRef' => rand(111111111, 999999999)."_".$array['tx_id']
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, FL_charge);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata)); //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
            curl_setopt($ch, CURLOPT_TIMEOUT, 200);

            $headers = array('Content-Type: application/json');

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
            $request = curl_exec($ch);

            curl_close($ch);
            if ($request) {
                $result = json_decode($request, true);

                if ($result['status'] == "success") {
                    $data['approved'] = 1;
                    $data['message'] = "Approved";
                } else {
                    $data['approved'] = 0;
                    $data['message'] = $result['data']['code'];
                }
            }else{
                return false;
            }

            return $data;
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`payment_card` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `pan` VARCHAR(4) NOT NULL, 
                `expiry_year` VARCHAR(2) NOT NULL, 
                `expiry_month` VARCHAR(2) NULL, 
                `card_name` VARCHAR(1000) NOT NULL, 
                `gateway_token` VARCHAR(1000) NULL,
                `temp_data` VARCHAR(1000) NULL,
                `is_default` INT NOT NULL, 
                `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`payment_card`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`payment_card`";

            $this->query($query);
        }
    }
?>