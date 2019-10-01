<?php
    class payment_card extends database {
        /*  create users
        */
        public function create($array) {
            global $users;
            global $alerts;
            $token = $this->bambora_get_token($array);
            $array['token'] = $token;
            $add_card = $this->bambora_create_profile($array);

            if (($add_card['code'] == 1) && ($add_card['message'] == "Operation Successful")) {
                $get_count = count($this->getSortedList($array['user_id'], 'user_id'));
                $data['user_id'] = $array['user_id'];
                $data['pan'] = substr( $array['cardno'], -4);
                $data['expiry_year'] = $array['yy'];
                $data['expiry_month'] = $array['mm'];
                $data['card_name'] = $array['cc_first_name']." ".$array['cc_last_name'];
                $data['gateway_token'] = $add_card['customer_code'];
                if ($get_count == 0) {
                    $data['is_default'] = 1;
                } else {
                    $data['is_default'] = 0;
                }
                
                $create = $this->insert("payment_card", $data);
                if ($create) {
                    //send email
                    $tag = "you have added card **** **** **** ".$data['pan']." expiring ".$data['expiry_month']."/".$data['expiry_year']." to your account. <a href='".URL."paymentCards'>Sigin in</a> to your MOBA Account to learn more";

                    $user_data = $users->listOne($array['user_id']);
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
                    $return = array('status' => 'OK', 'message' => 'Complete');
                } else {
                    $this->bambora_remove_profile($token);
                    $return = array('status' => 'Error', 'message' => 'An error occured');
                }
            } else {
                $return = array('status' => 'Error', 'message' => $add_card['message']." ".$add_card['details'][0]['message']);
            }

            return $return;
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

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("payment_card", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        private function bambora_get_token($array) {
            return "demo token";
        }

        private function bambora_create_profile($array) {
            $return['code'] = 1;
            $return['message'] = "Operation Successful";
            $return['customer_code'] = "Demo Code";

            return $return;
        }

        public function bambora_remove_profile($id) {
            $URL = "https://api.na.bambora.com/v1/profiles/".$id;
			
			$headers[] = "Authorization: Passcode ".gateway_passcode;
            $headers[] = "Content-Type: application/json";
			
			$ch = curl_init($URL);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($output, true);
            return $data;
        }
        
        public function listAllUserData($user, $start, $limit) {
            $return['list'] = $this->getSortedList($user, "user_id", false, false, false, false, "ref", "DESC", "AND", $start, $limit);
            $return['listCount'] = $this->getSortedList($user, "user_id", false, false, false, false, "ref", "DESC", "AND", false, false, "count");

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
                $data = $this->getSortedList($user, "user_id", "is_default", 1, false, false, "ref", "ASC", "AND", false, false, "getRow");
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

        public function bambora_pay($array) {
            $cardDetails = $this->listOne($array['card']);
            $payment['amount'] = $array['gross_total'];
            $payment['payment_method'] = "payment_profile";
            $payment['payment_profile']['complete'] = "true";
            $payment['payment_profile']['customer_code'] = $cardDetails['gateway_token'];
            $payment['payment_profile']['card_id'] = ucwords(strtolower($cardDetails['card_name']));

            $URL = "https://api.na.bambora.com/v1/payments";

			$xml_data = json_encode($payment);
			
			$headers[] = "Authorization: Passcode ".gateway_passcode;
            $headers[] = "Content-Type: application/json";
			
			$ch = curl_init($URL);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
            $output = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($output, true);
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