<?php
class request_negotiate extends database {
    /*  create users
    */
    public function negotiatePrize($array) {
        global $messages;
        $data['post_id'] = $array['post_id'];
        $data['user_id'] = $array['user_id'];
        $data['user_r_id'] = $array['user_r_id'];
        $data['amount'] = $array['negotiated_fee'];
        unset($array['negotiated_fee']);
        unset($array['negotiate']);
        $create = $this->create($data);
  
        if ($create) {
          $array['message']  = "[none]";
          $array['user_r_id']  = $array['user_r_id'];
          $array['user_id']  = $array['user_id'];
          $array['post_id']  = $array['post_id'];
          $array['m_type']  = $array['m_type'];
          $array['m_type_data']  = $data['amount']."_".$create;
          $messages->add($array);
  
          return true;
        } else {
          return false;
        }
      }
  
    public function negotiateResponse($array) {
        global $messages;
        global $users;
        $this->updateOneRow("status", $array['status'], $array['ref']);
        $messages->updateOneRow("message", "You have a new fee negotiation request.", $array['message']);
        $messages->updateOneRow("m_type", "text", $array['message']);
        $messages->updateOneRow("m_type_data", "", $array['message']);
        
        $messageData = $messages->listOne($array['message']);
        if ($array['status'] == 2) {
            $msg = "Your fee negotiation request was approved";
        } else {
            $msg = "Your fee negotiation request was declined";
        }

        $msgArray['message']  = $msg;
        $msgArray['user_r_id']  = $messageData['user_id'];
        $msgArray['user_id']  = $messageData['user_r_id'];
        $msgArray['post_id']  = $messageData['post_id'];
        $msgArray['m_type']  = "system";
        $messages->add($msgArray);

        //send email after

        $msg .= ". <a href='".$this->seo($messageData['post_id'], "view")."'>Click here</a> to continue review this request";

        $data = $users->listOne($messageData['user_id'], "ref");
        
        $client = $data['last_name']." ".$data['other_names'];
        $subjectToClient = "Negotiation Request Update";
        $contact = "Bereno <".replyMail.">";
        
        $fields = 'subject='.urlencode($subjectToClient).
            '&last_name='.urlencode($data['last_name']).
            '&other_names='.urlencode($data['other_names']).
            '&email='.urlencode($data['email']).
            '&tag='.urlencode($msg);
        $mailUrl = URL."includes/views/emails/notification.php?".$fields;
        $messageToClient = $this->curl_file_get_contents($mailUrl);
        
        $mail['from'] = $contact;
        $mail['to'] = $client." <".$data['email'].">";
        $mail['subject'] = $subjectToClient;
        $mail['body'] = $messageToClient;
        
        global $alerts;
        $alerts->sendEmail($mail);

        return true;
    }

    public function create($array) {
        $create = $this->insert("request_negotiate", $array);
        if ($create) {
            return $create;
        } else {
            return false;
        }
    }

    public function updateOneRow($tag, $value, $id) {
        return $this->updateOne("request_negotiate", $tag, $value, $id, "ref");
    }

    function getSingle($name, $tag="project_name", $ref="ref") {
        return $this->getOneField("request_negotiate", $name, $ref, $tag);
    }

    function listOne($id) {
        return $this->getOne("request_negotiate", $id, "ref");
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'post_id', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll("request_negotiate", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function checkCurrent($array) {
        $query = "SELECT * FROM `request_negotiate` WHERE `status` = 0 AND `post_id` = :post_id AND `user_id` = :user_id AND `user_r_id` = :user_r_id";
        $prepare[":post_id"] = $array['post_id'];
        $prepare[":user_id"] = $array['user'];
        $prepare[":user_r_id"] = $array['user_r'];

        return $this->run($query, $prepare, "count");
    }

    public function getApproved($array) {
        $query = "SELECT * FROM `request_negotiate` WHERE `status` = 2 AND `post_id` = :post_id AND (( `user_id` = :user_id AND `user_r_id` = :user_r_id ) OR (`user_id` = :user_r_id AND `user_r_id` = :user_id )) ORDER BY `ref` DESC LIMIT 1";
        $prepare[":post_id"] = $array['post_id'];
        $prepare[":user_id"] = $array['user_id'];
        $prepare[":user_r_id"] = $array['user_r_id'];

        return $this->run($query, $prepare, "getRow");
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`request_negotiate` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `post_id` INT NOT NULL, 
            `user_id` INT NOT NULL, 
            `user_r_id` INT NOT NULL, 
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
        $query = "TRUNCATE `".dbname."`.`request_negotiate`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`request_negotiate`";

        $this->query($query);
    }
}
?>