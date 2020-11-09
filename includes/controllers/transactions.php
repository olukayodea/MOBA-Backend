<?php
    class transactions extends payment_card {
        /*  create users
        */
        public function createTx($array) {
            $create = $this->insert("transactions", $array);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

        function translate($text, $id=false) {
            global $projects;
            if ($text == "featured_ad") {
                return "Featured Ad";
            } else if ($text == "project") {

                return $projects->getSingle($id);
            } else {
                return $text;
            }
        }

        function txid($id) {
            return 100000+$id;
        }

        function url($text, $id) {
            if ($text == "featured_ad") {
                return '<a href="'.$this->seo($id, "view").'">'.$this->translate($text).'</a>';
            } else if ($text == "project") {
                return '<a href="'.$this->seo($id, "view").'">'.$this->translate($text, $id).'</a>';
            } else {
                return $this->translate($text);
            }
        }

        function getListTrans($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
            return $this->lists("transactions", $start, $limit, $order, $dir, false, $type);
        }

		function getSingleTrans($name, $tag, $ref="ref") {
            return $this->getOneField("transactions", $name, $ref, $tag);
		}

        function listOneTrans($id) {
            return $this->getOne("transactions", $id, "ref");
        }

        function getSortedListTrans($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("transactions", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function listAllTransData($user, $start, $limit) {
            $return['list'] = $this->getSortedListTrans($user, "user_id", false, false, false, false, "ref", "DESC", "AND", $start, $limit);
            $return['listCount'] = $this->getSortedListTrans($user, "user_id", false, false, false, false, "ref", "DESC", "AND", false, false, "count");

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
            global $users;
            global $country;
            global $bank_account;
            global $rating;
            //get fee 
            $data['region'] = $country->listOne($data['region'], "ref");
            unset($data['region']['dial_code']);
            unset($data['region']['si_unit']);
            unset($data['region']['is_default']);
            unset($data['region']['featured_ad']);
            unset($data['region']['tax']);
            unset($data['region']['status']);
            unset($data['region']['create_time']);
            unset($data['region']['modify_time']);
            $money['text'] = $data['region']['currency_symbol'].number_format($data['net_total'], 2);
            $money['value'] = $data['net_total'];
            $data['net_total'] = $money;
            $money['text']  = $data['region']['currency_symbol'].number_format($data['tax_total'], 2);
            $money['value'] = $data['tax_total'];
            $data['tax_total'] = $money;
            $money['text']  = $data['region']['currency_symbol'].number_format($data['gross_total'], 2);
            $money['value'] = $data['gross_total'];
            $data['gross_total'] = $money;

            //get card details
            $data['card'] = $this->listOne($data['card']);
            unset($data['card']['user_id']);
            unset($data['card']['gateway_token']);
            unset($data['card']['is_default']);
            unset($data['card']['status']);
            unset($data['card']['create_time']);
            unset($data['card']['modify_time']);
            $data['card']['pan'] = "".$data['card']['pan'];

            //get account details
            $data['account'] = $bank_account->listOne($data['account']);
            unset($data['account']['user_id']);
            unset($data['account']['region']);
            unset($data['account']['is_default']);
            unset($data['account']['status']);
            unset($data['account']['create_time']);
            unset($data['account']['modify_time']);
            //get user ID and name
            $user['id'] = $data['user_id'];
            $user['name'] = $users->listOnValue($data['user_id'], "screen_name");
            $user['rating']['score'] = round($rating->getRate($data['user_id']), 2);
            $user['rating']['total'] = 5;
            $user['rating']['remark'] = $rating->textRate(intval($rating->getRate($data['user_id'])));
            $data['user_id'] = $user;
            if ($data['status'] == 0) {
                $data['status'] = "NEW";
            } else if ($data['status'] == 1) {
                $data['status'] = "FAILED";
            } else if ($data['status'] == 2) {
                $data['status'] = "COMPLETED";
            }
            $data['tx_id'] = $this->txid( $data['ref'] );
            //gateway data
            $data['gateway_data'] = unserialize($data['gateway_data']);
            
            unset($data['region']['ref']);
            $data['create_time'] = strtotime($data['create_time']);
            $data['modify_time'] = strtotime($data['modify_time']);
            return $data;
        }

        public function apiGetTransList($type, $user, $ref=false, $page=1) {
            global $options;
            if (intval($page) == 0) {
                $page = 1;
            }
            $current = intval($page)-1;
            
            $limit = $options->get("result_per_page_mobile");
            $start = $current*$limit;
            if ($type == "getOne") {
                if ($ref) {
                    $data = $this->listOneTrans($ref);
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
            } else if ($type == "list") {
                $result = $this->listAllTransData($user, $start, $limit);
                
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
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`transactions` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `tx_type_id` INT NOT NULL, 
                `tx_type` VARCHAR(50) NOT NULL, 
                `tx_dir` VARCHAR(3) NOT NULL DEFAULT 'CR',
                `card` INT NOT NULL, 
                `account` INT NOT NULL, 
                `region` INT NOT NULL, 
                `net_total` DOUBLE NOT NULL, 
                `tax_total` DOUBLE NOT NULL, 
                `gross_total` DOUBLE NOT NULL, 
                `gateway_data` TEXT NULL, 
                `gateway_status` VARCHAR(50) NOT NULL DEFAULT 'NEW', 
                `status` INT NOT NULL,
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