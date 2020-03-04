<?php
	class messages extends database {
        public function add($array) {
            $create = $this->insert("messages", $array);
            //send push notification
            if ($create) {
                if ($array['m_type'] == "text") {
                    $array['m_type'] = "post_messages";
                }
                $this->sendNotification($array['post_id'], $array['user_r_id'], $array['user_id'], $array['m_type']);
                return true;
            } else {
                return false;
            }
        }

        public function updateOneRow($tag, $value, $id) {
            return $this->updateOne("messages", $tag, $value, $id, "ref");
        }

        public function remove($id) {
            return $this->delete("messages", $id);
        }

        function getNewMessage($ref, $sender) {
            $query = "SELECT `user_id` FROM `messages` WHERE post_id = :post_id AND `user_id` = :user AND `status` = 0";

            $prepare[":post_id"] = $ref;
            $prepare[":user"] = $sender;

            return $this->query($query, $prepare, "count");
        }

        function getList($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
            return $this->lists("messages", $start, $limit, $order, $dir, $type);
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
            return $this->sortAll("messages", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
        }

        function listOne($id, $tag="ref") {
            return $this->getOne("messages", $id, $tag);
        }

        public function getLast($ref, $user, $owner) {
            $query = "SELECT * FROM `messages` WHERE post_id = :post_id AND ((`user_id` = :user AND `user_r_id` = :owner) OR (`user_r_id` = :user AND `user_id` = :owner)) ORDER BY `ref` DESC LIMIT 1";

            $prepare[":post_id"] = $ref;
            $prepare[":owner"] = $owner;
            $prepare[":user"] = $user;

            return $this->query($query, $prepare, "getRow");
        }

        public function getResponse($ref, $owner) {
            $query = "SELECT `user_id` FROM `messages` WHERE post_id = :post_id AND `user_id` != :user GROUP BY `user_id` ORDER BY `ref` ASC";

            $prepare[":post_id"] = $ref;
            $prepare[":user"] = $owner;

            return $this->query($query, $prepare, "list");
        }

        public function sendNotification($ref, $user, $users_r, $m_type="post_messages") {
            global $users;
            global $notifications;
            
            $query = "SELECT `ref` FROM `messages` WHERE post_id = :post_id AND `user_r_id` = :user AND `status` = 0";

            $prepare[":post_id"] = $ref;
            $prepare[":user"] = $user;

            $count = $this->query($query, $prepare, "count");

            if ($count > 0) {
                $data['event'] = $m_type;
                $data['event_id'] = $ref;
                $data['user_id'] = $user;
                $data['user_r_id'] = $users_r;
                $data['message'] = $count." ".$this->addS('message', $count)." from ".$users->listOnValue($users_r, "screen_name");
                $data['email'] = "You have ".$count." unread ".$this->addS('message', $count)." from ".$users->listOnValue($users_r, "screen_name");
                $data['timestamp'] = time()+(60*10);
                $data['count'] = $count;
                $notifications->create($data);

                $data["to"] = $users_r;
                $data["title"] = "New Message";
                $data["body"] = $data['message'];
                $data['data']['page_name'] = "messages";
                $data['data']['provider']['ref'] = $users_r;
                $data['data']['provider']['screen_name'] = $users->listOnValue( $users_r, "screen_name" );
                $data['data']['postId'] = $ref;
                $notifications->sendPush($data);
            }
        }

        public function findNegotiate($id) {
            $query = "SELECT `ref` FROM `messages` WHERE `m_type_data` LIKE '%_".$id."'";

            return $this->query($query, false, "getCol");
        }

        public function getPage($ref, $user, $owner) {
            $query = "SELECT * FROM `messages` WHERE `post_id` = :post_id AND ((`user_id` = :user AND `user_r_id` = :owner) OR (`user_r_id` = :user AND `user_id` = :owner)) ORDER BY `ref` ASC";

            $prepare[":post_id"] = $ref;
            $prepare[":owner"] = $owner;
            $prepare[":user"] = $user;

            return $this->query($query, $prepare, "list");
        }

        function markRead($id, $ref) {
            global $notifications;
            $array['status'] = 1;
            $where['user_r_id'] = $id;
            $where['post_id'] = $ref;
            $this->update("messages", $array, $where, "AND");
            $notifications->markRead("post_messages", $ref, $id);
        }

        function markReadOne($id, $sender, $ref) {
            global $notifications;
            $array['status'] = 1;
            $where['user_id'] = $sender;
            $where['user_r_id'] = $id;
            $where['post_id'] = $ref;
            $this->update("messages", $array, $where, "AND");
            $notifications->markRead("post_messages", $ref, $id);
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`messages` (
                `ref` INT NOT NULL AUTO_INCREMENT,
                `user_id` INT NOT NULL, 
                `user_r_id` INT NOT NULL, 
                `post_id` INT NOT NULL, 
                `message` TEXT NULL, 
                `m_type` VARCHAR(50) NOT NULL, 
                `m_type_data` VARCHAR(5000) NULL, 
                `status` INT NOT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`messages`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`messages`";

            $this->query($query);
        }
	}
?>