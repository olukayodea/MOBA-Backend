<?php
    class bank_account extends database {
        /*  create users
        */
        public function create($array) {
            global $users;
            global $alerts;
            
            $get_count = count($this->getSortedList($array['user_id'], 'user_id'));
            if ($get_count == 0) {
                $array['is_default'] = 1;
            } else {
                $array['is_default'] = 0;
            }
            
            $replace = array();
            $replace[] = "last_name";
            $replace[] = "first_name";
            $replace[] = "transit_number";
            $replace[] = "account_number";
            if ($array['region'] == "US") {
                $replace[] = "account_code";
            } else {
                $replace[] = "financial_institution";
            }
            if ($array['ref'] == 0) {
                unset($array['ref']);
            }
            $create = $this->replace("bank_account", $array, $replace);
            
            if ($create) {
                //send email
                $tag = "you have added a new bank account to your account. <a href='".URL."bankAccounts'>Sigin in</a> to your MOBA Account to learn more";

                $user_data = $users->listOne($array['user_id']);
                $client = $user_data['last_name']." ".$user_data['other_names'];
                $subjectToClient = "Bank Account Update";
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
                return true;
            }
        }

        function getList($start=false, $limit=false, $order="last_name", $dir="ASC", $type="list") {
            return $this->lists("bank_account", $start, $limit, $order, $dir, false, $type);
        }

		function getSingle($name, $tag="last_name", $ref="ref") {
            return $this->getOneField("bank_account", $name, $ref, $tag);
		}

        function listOne($id, $tag='ref') {
            return $this->getOne("bank_account", $id, $tag);
        }

        function setDefault($id) {
            $getFormer = $this->getSingle("1", "ref", "is_default");
            $this->updateOne("bank_account", "is_default", 0, $getFormer, "ref");
            $this->updateOne("bank_account", "is_default", 1, $id, "ref");
            return true;
        }

        function remove($id, $user=false) {
            $data = $this->listOne($id);
            
            if (($user == false) || (($user == true) && ($user == $data['user_id']))) {
                if ($data['is_default'] == 0) {
                    $this->delete("bank_account", $id);
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

            $this->updateOne("bank_account", "status", $updateData, $id, "ref");
            return true;
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'last_name', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("bank_account", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function listAllUserData($user, $start, $limit) {
            $return['list'] = $this->getSortedList($user, "user_id", false, false, false, false, "last_name", "ASC", "AND", $start, $limit);
            $return['listCount'] = $this->getSortedList($user, "user_id", false, false, false, false, "last_name", "ASC", "AND", false, false, "count");

            return $return;
        }

        public function apiGetList($type, $user, $ref=false, $page=1) {
            global $options;
            if (intval($page) == 0) {
                $page = 1;
            }
            $current = intval($page)-1;
            
            $limit = $options->get("ad_per_page_mobile");
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
                $data = $this->getSortedList($user, "user_id", "is_default", 1, false, false, "last_name", "ASC", "AND", false, false, "getRow");
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

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`bank_account` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `region` VARCHAR(3) NOT NULL,
                `last_name` VARCHAR(50) NOT NULL,
                `first_name` VARCHAR(50) NOT NULL,
                `transit_number` VARCHAR(50) NOT NULL,
                `account_number` VARCHAR(50) NOT NULL,
                `account_code` VARCHAR(20) NULL,
                `financial_institution` VARCHAR(20) NULL,
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
            $query = "TRUNCATE `".dbname."`.`bank_account`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`bank_account`";

            $this->query($query);
        }
    }
?>