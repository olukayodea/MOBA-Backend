<?php
    class notifications extends database {
        /*  create users
        */
        public function create($array) {
            $count = $this->getSortedList($array['user_id'], "user_id", "event", $array['event'], "event_id", $array['event_id']);
            if (count($count) > 0) {
                $array['status'] = 0;
                $where['user_id'] = $array['user_id'];
                $where['event'] = $array['event'];
                $where['event_id'] = $array['event_id'];
                $create = $this->update("notifications", $array, $where, "AND");
            } else {
                $create = $this->insert("notifications", $array);
            }
            if ($create) {
                return true;
            } else {
                return false;
            }
        }

        function markRead($event, $ref, $id) {
            $array['status'] = 1;
            $array['sent_mail'] = 1;
            $where['event'] = $event;
            $where['event_id'] = $ref;
            $where['user_id'] = $id;
            return $this->update("notifications", $array, $where, "AND");
        }

        function markReadOne($ref) {
            $array['status'] = 1;
            $array['sent_mail'] = 1;
            $where['ref'] = $ref;
            return $this->update("notifications", $array, $where);
        }

		function getSingle($name, $tag="message", $ref="ref") {
            return $this->getOneField("notifications", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("notifications", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("notifications", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function getCount($id, $type="fall") {
            $query = "SELECT SUM(`count`) FROM `notifications` WHERE `status` = 0 AND `user_id` = ".$id;

            if ($type == "post_messages") {
                $query .= " AND `event` = 'post_messages'";
            }

            return $this->run($query, false, "getCol");
        }

        function senemail() {
            global $users;
            global $alerts;
            global $notifications;
            $time = time()+(60*10);
            $query = "SELECT * FROM `notifications` WHERE `status` = 0 AND `timestamp` < ".$time." LIMIT 10";

            $list = $this->run($query, false, "list");

            for ($i = 0; $i < count($list); $i++) {
                $user_data = $users->listOne($list[$i]['user_id']);
                $client = $user_data['last_name']." ".$user_data['other_names'];
                $subjectToClient = $list[$i]['message'];
                $contact = "MOBA <".replyMail.">";
                
                $fields = 'subject='.urlencode($subjectToClient).
                    '&last_name='.urlencode($user_data['last_name']).
                    '&other_names='.urlencode($user_data['other_names']).
                    '&email='.urlencode($user_data['email']).
                    '&tag='.urlencode(htmlentities($list[$i]['email']));
                $mailUrl = URL."includes/views/emails/notification.php?".$fields;
                $messageToClient = $this->curl_file_get_contents($mailUrl);
                
                $mail['from'] = $contact;
                $mail['to'] = $client." <".$user_data['email'].">";
                $mail['subject'] = $subjectToClient;
                $mail['body'] = $messageToClient;
                
                $alerts->sendEmail($mail);

                $data["to"] = $list[$i]['user_id'];
                $data["title"] = "System Notification";
                $data["body"] = $subjectToClient;
                $data['data']['page_name'] = "home";
                $notifications->sendPush($data);

                $array['timestamp'] = time()+(60*60*24);
                $where['ref'] = $list[$i]['ref'];
                $this->update("notifications", $array, $where);

            }
        }

        public function sendPush($array) {
            global $usersToken;

            $userData = $usersToken->getSortedList($array['to'], "user_id");

            $token = array();
            $input = array();
            $count = 0;
            for ($i = 0; $i < count($userData); $i++) {
                if ($userData[$i]['channel'] == "app") {
                    $input[$count]['to'] = $userData[$i]['token'];
                    $input[$count]['title'] = $array['title'];
                    $input[$count]['body'] = $array['body'];
                    $input[$count]['data'] = $array['data'];
                    $count++;
                } else {
                    $token[] = $userData[$i]['token'];
                }
            }
            
            if (count($input) > 0) {
                $sendExpo = [$input];
                $url = "https://exp.host/--/api/v2/push/send";
                $header[] = "Content-Type: application/json";
    
                $post_data = json_encode($sendExpo);
    
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_VERBOSE, true );
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
                $output = curl_exec($ch);
                curl_close($ch);
                $data = json_decode( $output );
                return $data;
            }

            if (count($token) > 0) {
                
                // require_once 'vendor/autoload.php';

                // $client = new Google_Client();
                // $client->useApplicationDefaultCredentials(); 
                // $client->setAuthConfig(__DIR__.'/moba-6a561-5da551422b4e.json');
                // $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                // $httpClient = $client->authorize();

                // $project = "moba-6a561";
                // $message = [
                //     "message" => [
                //         "to" =>  $token[0],
                //         "notification" => [
                //             "body" => "This is an FCM notification message!",
                //             "title" => "FCM Message",
                //         ]
                //     ]
                // ];
                // $response = $httpClient->post("https://fcm.googleapis.com/v1/projects/{$project}/messages:send", ['json' => $message]);

                // echo "<pre>";
                // print_r($response);
                // //send firebase
                // $url = "https://fcm.googleapis.com/v1/projects/moba-web-api/messages:send";
                // $header[] = "Content-Type: application/json";
                // $header[] = "Authorization: Bearer AAAA5ehhqFQ:APA91bFYIfAJIzHKa6s2vt9uXUhNSZKmPvEZYsTAriqGv9ZyGr826bYD4q4iyfv78BxEx58GRbxvdk9J1rJdBmY-0zB5KDuRZ1PRv68vtRMW7DayqC9VXINNlQ-hEx7EqqPXGtxORsw7";

                // $data['notification']['title'] = $array['title'];
                // $data['notification']['body'] = $array['body'];
                // $data['notification']['click_action'] = $array['click_action'];
                // $data['notification']['icon'] = URL."images/logo.png";
                // if (count($token) > 1) {
                //     $data['registration_ids'] = $token;
                // } else {
                //     $data['to'] = $token[0];
                // }

                // $ch = curl_init($url);
                // curl_setopt($ch, CURLOPT_VERBOSE, true);
                // curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
                // curl_setopt($ch, CURLOPT_POST, 1);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                // $output = curl_exec($ch);
                // curl_close($ch);

                // $result = json_decode($output, true);

                // if ($result['success'] == 1) {
                //     return true;
                // } else {
                //     return false;
                // }
            }
        }

        public function listAllData($user, $start, $limit) {     
            $return['list'] = $this->getSortedList($user, "user_id", false, false, false, false, "ref", "DESC", "AND", $start, $limit);
            $return['listCount'] = $this->getSortedList($user, "user_id", false, false, false, false, "ref", "DESC", "AND", false, false, "count");

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
            unset($data['count']);
            unset($data['user_id']);
            unset($data['email']);
            unset($data['timestamp']);            
            
            if ($data['status'] == 0) {
                $data['status'] = "New";
            } else if ($data['status'] == 1) {
                $data['status'] = "Read";
            }
            return $data;
        }

        public function apiGetList($action, $user, $ref=false, $page=1) {
            global $inbox;
            global $options;
            $current = intval($page)-1;
            
            $limit = $options->get("result_per_page_mobile");
            $start = $current*$limit;
            if ($action == "getOne") {
                if ($ref) {
                    $data = $this->listOne($ref);
                    if ($user == $data['user_id']) {
                        $return['status'] = "200";
                        $return['message'] = "OK";
                        $return['counts'] = $inbox->counters($user);
                        $return['data'] = $this->formatResult($data, true);
                    } else {
                        $return['status'] = "403";
                        $return['message'] = "Forbidden";
                    }
                } else {
                    $return['status'] = "400";
                    $return['message'] = "Bad Request";
                    $return['additional_message'] = "notification ref missing in URL";
                }
            } else {
                $result = $this->listAllData($user, $start, $limit);
                $return['status'] = "200";
                $return['message'] = "OK";
                $return['counts'] = $inbox->counters($user);
                $return['data'] = $this->formatResult( $result['list'] );
            }

            return $return;
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`notifications` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `event` VARCHAR(50) NOT NULL,
                `event_id` INT NOT NULL, 
                `user_id` INT NOT NULL, 
                `user_r_id` INT NOT NULL, 
                `message` VARCHAR(50) NOT NULL,
                `email` VARCHAR(500) NOT NULL,
                `timestamp` VARCHAR(50) NOT NULL,
                `count` INT NOT NULL, 
                `status` INT NOT NULL DEFAULT '0',
                `sent_mail` INT NOT NULL DEFAULT '0',
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`notifications`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`notifications`";

            $this->query($query);
        }
    }
?>