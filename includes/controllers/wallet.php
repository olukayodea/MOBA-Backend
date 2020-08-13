<?php
    class wallet extends transactions {
        /*  create users
        */
        public function createWallet($array) {
            global $users;
            global $alerts;
            global $notifications;
            $create = $this->insert("wallet", $array);
            if ($create) {

                $pushNotice = "Some money has been";
                if ($array['tx_dir'] == "CR") {
                    $pushNotice .= " deposited into ";
                } else {
                    $pushNotice .= " withdrawn from ";
                }
                $pushNotice .= "your wallet account.";
                
                $tag = $pushNotice." <a href='".URL."wallet'>Sign in</a> to your MOBA Wallet to learn more";


                $user_data = $users->listOne($array['user_id']);
                $client = $user_data['last_name']." ".$user_data['other_names'];
                $subjectToClient = "Payment Update";
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


                $data["to"] = $array['user_id'];
                $data["title"] = "Payment Notification";
                $data["body"] = $pushNotice;
                $data['data']['page_name'] = "wallet";
                $data['data']['provider']['ref'] = $array['user_id'];
                $data['data']['provider']['screen_name'] = $users->listOnValue( $array['user_id'], "screen_name" );


                $notifications->sendPush($data);

                return $create;
            } else {
                return false;
            }
        }

        public function balance($ref, $region, $available=false) {
            if ($available == false) {
                $tag = " AND `status` > 0";
            } else {
                $tag = " AND `status` != 2";
            }

            $query = "SELECT SUM(`amount`) FROM `wallet` WHERE `user_id` = :ref AND region = :region".$tag;
            $prepare[":ref"] = $ref;
            $prepare[":region"] = $region;
            return $this->run($query, $prepare, "getCol");
        }

        public function availableRegion($ref) {
            $query = "SELECT `country`.`code`, `country`.`name`, `country`.`currency`, `wallet`.`region` FROM `wallet`, `country` WHERE `country`.`ref` = `wallet`.`region` AND `wallet`.`user_id` = :ref GROUP BY `wallet`.`region` ORDER BY `wallet`.`region`";
            $prepare[":ref"] = $ref;
            return $this->run($query, $prepare, "list");
        }

        function getListTWallet($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
            return $this->lists("wallet", $start, $limit, $order, $dir, false, $type);
        }

		function getSingleWallet($name, $tag, $ref="ref") {
            return $this->getOneField("wallet", $name, $ref, $tag);
		}
        
        public function updateOneRow($tag, $value, $id, $ref="ref") {
            return $this->updateOne("wallet", $tag, $value, $id, $ref);
        }

        function listOneWallet($id, $tag="ref") {
            return $this->getOne("wallet", $id, $tag);
        }

        function getSortedListWallet($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("wallet", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function listAllWalletData($user, $region, $start, $limit) {            
            $return['list'] = $this->getSortedListWallet($user, "user_id", "region", $region, false, false, "ref", "DESC", "AND", $start, $limit);
            $return['listCount'] = $this->getSortedListWallet($user, "user_id", "region", $region, false, false, "ref", "DESC", "AND", false, false, "count");

            return $return;
        }
        
        private function formatResult($data, $single=false) {
            if ($single == false) {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i] = $this->clean($data[$i]);
                }
            } else {
                $data = $this->clean($data);
            }
            return $data;
        }

        private function clean($data) {
            global $country;
            global $users;
            global $rating;

            unset($data['tx_id']);
            unset($data['ref_id']);
            
            //get user ID and name
            $user['id'] = $data['user_id'];
            $user['name'] = $users->listOnValue($data['user_id'], "screen_name");
            $user['rating']['score'] = round($rating->getRate($data['user_id']), 2);
            $user['rating']['total'] = 5;
            $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($data['user_id'])));
            $data['user_id'] = $user;

            
            if ($data['status'] == 0) {
                $data['status'] = "Pending";
            } else if ($data['status'] == 1) {
                if ($data['tx_dir'] == "CR") {
                    $data['status'] = "Available";
                } else if ($data['tx_dir'] == "DR") {
                    $data['status'] = "Completed";
                }
            } else if ($data['status'] == 2) {
                $data['status'] = "Processing";
            }

            $data['region'] = $country->listOne($data['region'], "ref");
            unset($data['region']['dial_code']);
            unset($data['region']['si_unit']);
            unset($data['region']['is_default']);
            unset($data['region']['featured_ad']);
            unset($data['region']['tax']);
            unset($data['region']['status']);
            unset($data['region']['create_time']);
            unset($data['region']['modify_time']);
            unset($data['region']['ref']);

            $money['text'] = $data['region']['currency_symbol'].number_format($data['amount'], 2);
            $money['value'] = $data['amount'];
            $data['amount'] = $money;
            
            $data['create_time'] = strtotime($data['create_time']);
            $data['modify_time'] = strtotime($data['modify_time']);
            return $data;
        }

        public function apiGetWalletList($type, $region, $user, $ref=false, $page=1) {
            global $options;
            global $country;
            if (intval($page) == 0) {
                $page = 1;
            }
            $current = intval($page)-1;
            
            $limit = $options->get("result_per_page_mobile");
            $start = $current*$limit;
            if ($type == "getOne") {
                if ($ref) {
                    $data = $this->listOneWallet($ref);
                    if ($user == $data['user_id']) {
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['data'] = $this->formatResult($data, true);
                    } else {
                        $return['status'] = "403";
                        $return['message'] = "Forbidden";
                    }
                } else {
                    $return['status'] = "400";
                    $return['message'] = "Bad Request";
                    $return['additional_message'] = "Account ref missing in URL";
                }
            } else if ($type == "balance") {
                
                $data['region'] = $country->listOne($region, "ref");
                unset($data['region']['ref']);
                unset($data['region']['dial_code']);
                unset($data['region']['si_unit']);
                unset($data['region']['is_default']);
                unset($data['region']['featured_ad']);
                unset($data['region']['tax']);
                unset($data['region']['status']);
                unset($data['region']['create_time']);
                unset($data['region']['modify_time']);

                $money['text'] = $data['region']['currency_symbol'].number_format($this->balance($user, $region, true), 2);
                $money['value'] = number_format( $this->balance( $user, $region, true ) );
                $res['current'] = $money;
                $money['text'] = $data['region']['currency_symbol'].number_format($this->balance($user, $region), 2);
                $money['value'] = number_format( $this->balance( $user, $region ) );
                $res['available'] = $money;

                $return['status'] = "200";
                $return['message'] = "OK";
                $return['data'] = $data;
                $return['data']['balance'] = $res;
            } else if ($type == "list") {
                $result = $this->listAllWalletData($user, $region, $start, $limit);
                $return['status'] = "200";
                $return['message'] = "OK";
                $return['counts']['current_page'] = $page;
                $return['counts']['total_page'] = ceil($result['listCount']/$limit);
                $return['counts']['rows_on_current_page'] = count($result['list']);
                $return['counts']['max_rows_per_page'] = $limit;
                $return['counts']['total_rows'] = $result['listCount'];
                $return['data'] = $this->formatResult( $result['list'] );
            }
            return $return;
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`wallet` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `tx_id` INT NOT NULL, 
                `ref_id` INT NOT NULL, 
                `tx_desc` VARCHAR(50) NOT NULL, 
                `tx_dir` VARCHAR(3) NOT NULL DEFAULT 'CR',
                `region` INT NOT NULL, 
                `amount` DOUBLE NOT NULL, 
                `status` INT NOT NULL,
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`wallet`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`wallet`";

            $this->query($query);
        }
    }
?>