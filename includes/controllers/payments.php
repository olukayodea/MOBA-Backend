<?php
    class payments extends database {
        /*  create users
        */
        public function create($array) {
            return $this->insert("payments", $array);
        }

        function getList($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
            return $this->lists("payments", $start, $limit, $order, $dir, false, $type);
        }

		function getSingle($name, $tag="user_profile_id", $ref="ref") {
            return $this->getOneField("payments", $name, $ref, $tag);
        }
        
        public function updateOneRow($tag, $value, $id, $ref="ref") {
            return $this->updateOne("payments", $tag, $value, $id, $ref);
        }

        function listOne($id) {
            return $this->getOne("payments", $id, "ref");
        }

        function remove($id) {
            $this->delete("payments", $id);
            return true;
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false) {
            return $this->sortAll("payments", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
        }

        public function processPayment($credit=false) {
            global $bank_account;
            global $transactions;
            global $users;
            global $banks;
            $batch = $this->createRandomPassword(9).rand(10, 99).time();
            if ($credit == true) {
                $list = $this->getSortedList(0, "status", "user_profile_type", "CC");
            } else {
                $list = $this->getSortedList(0, "status", "user_profile_type", "BA");
            }
            echo "<pre>";
            $data = array();
            for ($i = 0; $i < count($list); $i++) {
                $sub[] = $list[$i]['tx_type'];
                $sub[] = $list[$i]['tx_type_code'];

                if ($list[$i]['tx_type'] == "E") {
                    $account_data = $bank_account->listOne($list[$i]['user_profile_id']);
                    $sub[] = $banks->getSingle( $account_data['financial_institution'], "financial_institution");
                    $sub[] = $account_data['transit_number'];
                    $sub[] = $account_data['account_number'];
                } else if ($list[$i]['tx_type'] == "A") {
                    $account_data = $bank_account->listOne($list[$i]['user_profile_id']);
                    $sub[] = $account_data['transit_number'];
                    $sub[] = $account_data['account_number'];
                    $sub[] = $account_data['account_code'];
                } else if ($list[$i]['tx_type'] == "C") {
                    $sub[] = "";
                    $sub[] = "";
                    $sub[] = "";
                }
                $sub[] = $list[$i]['amount']*100;
                $sub[] = $list[$i]['tx_id'];
                $sub[] = $users->listOnValue( $list[$i]['user_id'], "last_name" )." ".$users->listOnValue( $list[$i]['user_id'], "other_names" );
                if ($list[$i]['tx_type'] == "C") {
                    $account_data = $transactions->listOne($list[$i]['user_profile_id']);
                    $sub[] = $users->listOnValue( $list[$i]['user_id'], "email" );
                    $sub[] = "0";
                    $sub[] = "MOBA Payout";
                    $sub[] = $account_data['gateway_token'];
                } else {
                    $sub[] = "";
                    $sub[] = "MOBA Payout";
                    $sub[] = "";
                    $sub[] = "";
                }
                $data[] = $sub;
                unset($sub);

                $this->updateOneRow("batch_id", $batch, $list[$i]['ref']);
            }

            if (count($list) > 0) {
                $process = $this->push($data, $batch);

                if ($process['code'] == 1) {
                    $this->updateOneRow("status", 1, $batch, "batch_id");
                    $this->updateOneRow("batch_id_gateway", $process['batch_id'], $batch, "batch_id");
                }
                
                $getProcessed = $this->getSortedList($batch, "batch_id");

                for ($i = 0; $i < count($getProcessed); $i++) {
                    $this->updateOne("transactions", "status", 2, $getProcessed[$i]['tx_id']);
                    $this->updateOne("transactions", "status", 2, $getProcessed[$i]['tx_id']);
                    $this->updateOne("transactions", "gateway_status", "Approved", $getProcessed[$i]['tx_id']);
                    $this->updateOne("transactions", "gateway_data", json_encode($process), $getProcessed[$i]['tx_id']);
                    
                    $tx_data = $transactions->listOneTrans($getProcessed[$i]['tx_id']);                    
                    
                    $this->updateOne("wallet", "status", 1, $tx_data['tx_type_id']);
                }
            }
        }

        private function push($data, $batch) {
            $line = "";
            for ($i = 0; $i < count($data); $i++) {
                $line .= (string) trim(implode(",", $data[$i]))."\r\n";
            }
            $URL = "https://api.na.bambora.com/v1/batchpayments";

            $boundry = "WebKitFormBoundary".$this->createRandomPassword(16);         

            $postData = "--".$boundry."\r\n";
            $postData .= "Content-Disposition: form-data; name=\"criteria\"\r\n";
            $postData .= "Content-Type: application/json\r\n\r\n";
            $postData .= "{\"process_row\":1}\r\n\r\n";
            $postData .= "--".$boundry."\r\n";
            $postData .= "Content-Disposition: form-data; name=\"testdata\"; filename=\"".$batch.".csv\"\r\n";
            $postData .= "Content-Type: text/plain\r\n\r\n";
            $postData .= $line."--".$boundry."--\r\n'";

			$headers[] = "Authorization: Passcode ".gateway_passcode;
            $headers[] = "content-type: multipart/form-data; boundary=".$boundry;
            $headers[] = "filetype: STD";
            
			$ch = curl_init($URL);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
            curl_setopt($ch, CURLOPT_POST, 1);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $output = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($output, true);
            return $data;
        }
        
        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`payments` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `tx_id` INT NOT NULL, 
                `user_id` INT NOT NULL, 
                `user_profile_id` INT NOT NULL, 
                `user_profile_type` VARCHAR(2) NOT NULL, 
                `amount` DOUBLE NOT NULL, 
                `send_time` VARCHAR(50) NULL, 
                `batch_id` VARCHAR(1000) NOT NULL, 
                `batch_id_gateway` VARCHAR(1000) NULL,
                `region` INT NOT NULL, 
                `status` INT NOT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`payments`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`payments`";

            $this->query($query);
        }
    }
?>