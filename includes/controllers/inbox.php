<?php
  class inbox extends database {
      
    public function add($array) {
        $toArray = array_unique( explode( ",", $array['to_list'] ) );
        $array['status'] = "SENT";
        foreach ($toArray as $id) {
            $array['to_id'] = $id;
            $this->insert("inbox", $array);
        }
        $array['to_id'] = 0;
        $this->insert("inbox", $array);

        return true;
    }

    public function sendMail() {
    }

    public function manage($array) {
        if ($array['inboxAction'] == "deleteMail") {
            for ($i=0; $i < count($array['ref']); $i++) {
                $this->remove($array['ref'][$i]);
            }
        } else if ($array['inboxAction'] == "readMail") {
            for ($i=0; $i < count($array['ref']); $i++) {
                $this->updateOneRow("read", 1, $array['ref'][$i]);
            }
        } else if ($array['inboxAction'] == "unreadMail") {
            for ($i=0; $i < count($array['ref']); $i++) {
                $this->updateOneRow("read", 0, $array['ref'][$i]);
            }
        }

        return true;
    }

    public function remove($id) {
        return $this->delete("inbox", $id);
    }

    public function counters($ref) {
        global $notifications;
        $return['new'] = $this->getSortedList($ref, "to_id", "status", "SENT", "read", 0, "ref", "ASC", "AND", false, false, "count");
        $return['inbox'] = $this->getSortedList($ref, "to_id", "status", "SENT", false, false, "ref", "ASC", "AND", false, false, "count");
        $return['sent'] =  $this->getSortedList($ref, "from_id", "status", "SENT", "to_id", 0, "ref", "DESC", "AND", false, false,"count");
        $return['notification'] =  $notifications->getSortedList($ref, "user_id", "status", "0", false, false, "ref", "DESC", "AND", false, false,"count");
        return $return;
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false, $type="list") {
        return $this->sortAll("inbox", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
    }

    function listOne($id, $tag="ref") {
        return $this->getOne("inbox", $id, $tag);
    }

    public function updateOneRow($tag, $value, $id) {
        return $this->updateOne("inbox", $tag, $value, $id, "ref");
    }

    public function markReadOne($ref) {
        //mark notification as read
        $this->updateOneRow("read", 1, $ref);
        $this->updateOneRow("mail", 1, $ref);
    }
    
    public function listAllInboxData($type, $user, $start, $limit) {
        if ($type == "inbox") {
            $return['list'] = $this->getSortedList($user, "to_id", "status", "SENT", false, false, "ref", "DESC", "AND", $start, $limit);
            $return['listCount'] = $this->getSortedList($user, "to_id", "status", "SENT", false, false, "ref", "DESC", "AND", false, false, "count");
        } else if ($type == "sent") {
            $return['list'] = $this->getSortedList($user, "from_id", "status", "SENT", "to_id", 0);
            $return['listCount'] =  $this->getSortedList($user, "from_id", "status", "SENT", "to_id", 0, "ref", "DESC", "AND", false, false,"count");
        }
        return $return;
    }
        
    private function formatResult($data, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->clean($data[$i], $single);
            }
        } else {
            $data = $this->clean($data, $single);
        }
        return $data;
    }

    private function clean($data, $single) {
        global $users;
        unset($data['mail']);
        unset($data['to_id']);
        unset($data['status']);
        if ($data['read'] == 1) {
            $data['read'] = "true";
        } else {
            $data['read'] = "new";
        }
        if ($single == false) {
            unset($data['message']);
        }
    
        //get user ID and name
        $user['id'] = $data['from_id'];
        $user['name'] = $users->listOnValue($data['from_id'], "screen_name");
        $data['from_id'] = $user;
        $to = explode(',', $data['to_list']);
        for ($i = 0; $i < count($to); $i++) {      
            $user['id'] = $data['to_list'];
            $user['name'] = $users->listOnValue($data['to_list'], "screen_name");
            $toRes[$i] = $user;
        }
        $data['to_list'] = $toRes;
        $data['sent'] = $data['create_time'];
        $data['updated'] = $data['modify_time'];
        unset($data['create_time']);
        unset($data['modify_time']);
        return $data;
    }


    public function apiGetList($type, $user, $ref=false, $page=1) {
        global $options;
        if (intval($page) == 0) {
            $page = 1;
        }
        $current = intval($page)-1;
        
        $limit = $options->get("ad_per_page_mobile");
        $start = $current*$limit;

        if (($type == "inbox") || ($type == "sent")) {
            $result = $this->listAllInboxData($type, $user, $start, $limit);
                    
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts']['current_page'] = $page;
            $return['counts']['total_page'] = ceil($result['listCount']/$limit);
            $return['counts']['rows_on_current_page'] = count($result['list']);
            $return['counts']['max_rows_per_page'] = $limit;
            $return['counts']['total_rows'] = $result['listCount'];
            $return['data'] = $this->formatResult( $result['list'] );
        } else if ($type == "count") {
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['counts'] = $this->counters($user);
        } else if ($type == "getOne") {
            if ($ref) {
                $data = $this->listOne($ref);
                if (($user == $data['from_id']) || ($user == $data['to_id'])) {
                    $return['status'] = "200";
                    $return['message'] = "OK";
                    $return['data'] = $this->formatResult($data, true);
                    if ($user == $data['to_id']) {
                        $this->markReadOne($ref);
                    }
                } else {
                    $return['status'] = "403";
                    $return['message'] = "Forbidden";
                }
            } else {
                $return['status'] = "400";
                $return['message'] = "Bad Request";
                $return['additional_message'] = "Account ref missing in URL";
            }
        }
        return $return;
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`inbox` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `from_id` INT NOT NULL, 
            `to_id` INT NOT NULL, 
            `to_list` VARCHAR(5000) NOT NULL, 
            `previous_id` INT NOT NULL, 
            `subject` VARCHAR(255) NOT NULL, 
            `message` TEXT NOT NULL, 
            `read` INT NOT NULL, 
            `mail` INT NOT NULL, 
            `status` varchar(20) NOT NULL DEFAULT 'NEW',
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`inbox`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`inbox`";

        $this->query($query);
    }
  }
?>