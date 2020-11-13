<?php
    class payment_card extends database {
        public function create($array) {
            $get_count = count($this->getSortedList($array['user_id'], 'user_id'));

            if ($get_count == 0) {
                $array['is_default'] = 1;
            } else {
                $array['is_default'] = 0;
            }

            $create = $this->insert("payment_card", $array);
            if ( $create ) {
                $this->sendMail($create);
                return $create;
            }
        }

        function sendMail($id) {
            global $users;
            global $alerts;
            global $notifications;
			$data = $this->listOne($id);
            //send email
            $tag = "you have added card ".$data['pan']." expiring ".$data['expiry_month']."/".$data['expiry_year']." to your account. <a href='".URL."paymentCards'>Sign in</a> to your MOBA Account to learn more";

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
            $getFormer = $this->getSingle($id, "ref", "is_default");
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
                    $this->delete("payment_card", $id);
                    return true;
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

        function verifyTx($reference) {

            $curl = curl_init();
        
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer sk_live_a2ed6378f889da4c4e80b2ebd86509d28755751e",
                "Cache-Control: no-cache",
            ),
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => dirname(__FILE__)."/cacert.pem"
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                return json_decode($response, true);
            }
        }

		function refund($trans_id, $amount) {
            $url = "https://api.paystack.co/refund";
            $postdata = [
              'transaction' => $trans_id,
              'amount' => $amount,
            ];
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
			curl_setopt($ch, CURLOPT_TIMEOUT, 200);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer sk_live_a2ed6378f889da4c4e80b2ebd86509d28755751e",
                "Cache-Control: no-cache",
            ));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
			$request = curl_exec($ch);
			
			curl_close($ch);
			if ($request) {
				$result = json_decode($request, true);

				if ($result['data']['status'] == "success") {
					return true;
				} else {
					return false;
				}
			}else{
				return false;
            }
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

            $url = "https://api.paystack.co/transaction/charge_authorization";
            $fields = [
                'authorization_code' => $data['gateway_token'],
                'email' => $usserData['email'],
                'amount' => 100*$array['gross_total']
            ];
            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();
            
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer sk_live_a2ed6378f889da4c4e80b2ebd86509d28755751e",
                "Cache-Control: no-cache",
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
            
            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
            
            //execute post
            $result = curl_exec($ch);
            echo $result;



            if ($result) {
                $result = json_decode($result, true);

                if ($result['data']['status'] == "success") {
                    $data['approved'] = 1;
                    $data['message'] = "Approved";
                } else {
                    $data['approved'] = 0;
                    $data['message'] = $result['message'];
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