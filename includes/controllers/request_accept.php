<?php
class request_accept extends database {
    /*  create request_accept
    */
  
    public function requestResponse($array) {
        global $request;
        global $users;
        global $notifications;
        global $category;

        $requestData = array();
        $requestData = $request->listOne( $array['request'] );

        if ($requestData['status'] == "OPEN") {
            $add['post_id'] = $prepare[':post_id'] = $array['request'];
            $add['user_r_id'] = $prepare[':user_r_id'] = $array['user_r_id'];
            $add['user_id'] = $prepare[':user_id'] = $requestData['user_id'];
            
            $check = $this->query("SELECT `ref` FROM `".dbname."`.`request_accept` WHERE `post_id` = :post_id AND `user_id` = :user_id AND `user_r_id` = :user_r_id", $prepare, "count");

            if ($check == 0) {
                $this->create($add);
                $this->reject($array);

                $requestDataField['ref'] = $requestData['ref'];
                $requestDataField['charge'] = $requestData['fee'];
                $requestDataField['duration'] = "";
                $requestDataField['categoryName'] = $category->getSingle( $requestData['category_id'] );
                $requestDataField['location'] = $requestData['address'];

                $msg = $users->listOnValue( $array['user_r_id'], "screen_name" )." accepted your request";
                $data["to"] = $requestData['user_id'];
                $data["title"] = "Request Notification";
                $data["body"] = $msg;
                $data['data']['page_name'] = "handle_request";
                $data['data']['request'] = $requestDataField;
                $notifications->sendPush($data);

                 //send email after
                 $msg .= ". <a href='".URL."?page_name=handle_request&ID=".$requestData['post_id']."&request=".json_encode($requestDataField)."'>Click here</a> to continue review this request";
                $data = $users->listOne($requestData['user_id'], "ref");
                
                $client = $data['last_name']." ".$data['other_names'];
                $subjectToClient = "Request Update";
                $contact = "MOBA <".replyMail.">";
                
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
            }
            return true;
        } else {
            return false;
        }

        // $msgArray['message']  = $msg;
        // $msgArray['user_r_id']  = $messageData['user_id'];
        // $msgArray['user_id']  = $messageData['user_r_id'];
        // $msgArray['post_id']  = $messageData['post_id'];
        // $msgArray['m_type']  = "system";
        // $messages->add($msgArray);

        // $data["to"] = $messageData['user_id'];
        // $data["title"] = "Negotiation Request";
        // $data["body"] = $msg;
        // $data['data']['page_name'] = "messages";
        // $data['data']['provider']['ref'] = $messageData['user_id'];
        // $data['data']['provider']['screen_name'] = $users->listOnValue( $messageData['user_id'], "screen_name" );
        // $data['data']['postId'] = $messageData['post_id'];
        // $notifications->sendPush($data);

        // //send email after

        // $msg .= ". <a href='".$this->seo($messageData['post_id'], "view")."'>Click here</a> to continue review this request";

        // $data = $users->listOne($messageData['user_id'], "ref");
        
        // $client = $data['last_name']." ".$data['other_names'];
        // $subjectToClient = "Negotiation Request Update";
        // $contact = "MOBA <".replyMail.">";
        
        // $fields = 'subject='.urlencode($subjectToClient).
        //     '&last_name='.urlencode($data['last_name']).
        //     '&other_names='.urlencode($data['other_names']).
        //     '&email='.urlencode($data['email']).
        //     '&tag='.urlencode($msg);
        // $mailUrl = URL."includes/views/emails/notification.php?".$fields;
        // $messageToClient = $this->curl_file_get_contents($mailUrl);
        
        // $mail['from'] = $contact;
        // $mail['to'] = $client." <".$data['email'].">";
        // $mail['subject'] = $subjectToClient;
        // $mail['body'] = $messageToClient;
        
        // global $alerts;
        // $alerts->sendEmail($mail);
    }

    public function reject($array) {
        $this->query("DELETE FROM `notifications` WHERE `event` = 'request' AND `event_id` = ".$array['request']." AND `user_id` = ".$array['user_r_id']);

        return true;
    }

    public function create($array) {
        $create = $this->insert("request_accept", $array);
        if ($create) {
            return $create;
        } else {
            return false;
        }
    }

    public function remove($id, $ref="ref") {
        return $this->delete("request_accept", $id, $ref);
    }

    public function getResponse($request) {
        return $this->query("SELECT `users`.`ref`, `users`.`last_name`, `users`.`other_names`, `users`.`screen_name`, `users`.`email`, `users`.`mobile_number`, `users`.`street`, `users`.`city`, `users`.`state`, `users`.`country`, `users`.`account_type`, `users`.`latitude`, `users`.`longitude`, `users`.`about_me`, `users`.`image_url`, `users`.`average_response_time` FROM `users` WHERE `users`.`ref` IN (SELECT `user_r_id` FROM `request_accept` WHERE `post_id` = ".$request.")", false, "list");
    }

    public function updateOneRow($tag, $value, $id) {
        return $this->updateOne("request_accept", $tag, $value, $id, "ref");
    }

    function getSingle($name, $tag="project_name", $ref="ref") {
        return $this->getOneField("request_accept", $name, $ref, $tag);
    }

    function listOne($id) {
        return $this->getOne("request_accept", $id, "ref");
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'post_id', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll("request_accept", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`request_accept` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `post_id` INT NOT NULL, 
            `user_id` INT NOT NULL, 
            `user_r_id` INT NOT NULL, 
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`request_accept`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`request_accept`";

        $this->query($query);
    }
}
?>