<?php
    class category extends database {
        /*  create users
        */
        public function create($array) {
            $replace = array();
            $replace[] = "parent_id";
            $replace[] = "category_title";
            $replace[] = "call_out_charge";
            $replace[] = "status";
            if ($array['ref'] == 0) {
                unset($array['ref']);
            }

            $create = $this->replace("category", $array, $replace);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

        function toggleStatus($id) {
            $data = $this->listOne($id);
            if ($data['status'] == "ACTIVE") {
                $updateData = "INACTIVE";
            } else if ($data['status'] == "INACTIVE") {
                $updateData = "ACTIVE";
            }

            $this->updateOne("category", "status", $updateData, $id, "ref");
            $this->updateOne("category", "status", $updateData, $id, "parent_id");
            return true;
        }

        function remove($id) {
            $data = $this->listOne($id);
            $this->updateOne("category", "parent_id", $data['parent_id'], $id, "parent_id");
            $this->updateOne("category", "status", "DELETED", $id, "ref");
            return true;
        }

        function getList($start=false, $limit=false, $order="category_title", $dir="ASC", $type="list") {
            return $this->lists("category", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
        }

		function getSingle($name, $tag="category_title", $ref="ref") {
            return $this->getOneField("category", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("category", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'category_title', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("category", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function categoryListPages($country, $start, $limit) {
            $return['data'] = $this->categoryList($country, 0, "list", $start, $limit);
            $return['count'] = $this->categoryList($country, 0, "count");

            return $return;
        }

        function categoryList($country, $parent = 0, $type="list", $start=false, $limit=false) {
            return $this->getSortedList($parent, "parent_id", "status", "ACTIVE", "country", $country, "category_title", "ASC", "AND", $start,$limit, $type);
        }

        public function totalUsers($id, $location) {
            $query = "SELECT `usersCategory`.`user_id`, `users`.`average_response_time`, `users`.`screen_name`, SQRT(((`users`.`latitude` - ".$location['latitude'].")*(`users`.`latitude` - ".$location['latitude'].")) + ((`users`.`longitude` - ".$location['longitude'].")*(`users`.`longitude` - ".$location['longitude']."))) AS `total` FROM `usersCategory`, `users`, `currentLocation` WHERE `users`.`status` = 'ACTIVE' AND `users`.`user_type` = 1 AND `users`.`verified` = 2 AND `usersCategory`.`user_id` = `users`.`ref` AND `users`.`ref` = `currentLocation`.`user_id` AND `category_id` = ".$id." AND ((`users`.`country` LIKE '%".$location['code']."%' OR `users`.`country` LIKE '%".$location['country']."%' OR `currentLocation`.`country` LIKE '%".$location['code']."%' OR `currentLocation`.`country` LIKE '%".$location['country']."%%') AND (`users`.`state` LIKE '%".$location['state_code']."%' OR `users`.`state` LIKE '%".$location['state']."%' OR `currentLocation`.`state` LIKE '%".$location['state_code']."%' OR `currentLocation`.`state` LIKE '%".$location['state']."%')) GROUP BY `usersCategory`.`user_id` ORDER BY `users`.`is_featured` DESC, `total` ASC LIMIT 20";

            $prepare[":id"] = $id;
            $prepare[":state"] = $location['state'];
            $prepare[":country"] = $location['country'];

            return $this->run($query, $prepare, "list");

        }

        public function getIcon($id) {
          $data = $this->getSingle($id, "image_url_svg");
    
          if ($data != "") {
            $file = URL."media/categories/".$data;
            $file_headers = @get_headers($file);
            if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
                return URL."media/categories/0.svg";
            } else {
                return URL."media/categories/".$data;
            }
          } else {
            return URL."media/categories/0.png";
          }
        }

        private function clean($data) {
            $data['image_url'] = $this->getIcon($data['ref']);

            $data['questionnaire'] = $this->apiGetQustions($data['ref']);
            unset($data['status']);
            unset($data['country']);
            unset($data['parent_id']);
            unset($data['create_time']);
            unset($data['modify_time']);
            return $data;
        }
        
        public function formatResult($data, $single=false) {
            if ($single == false) {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i] = $this->clean($data[$i]);
                }
            } else {
                $data = $this->clean($data);
            }
            return $data;
        }

        public function apiGetQustions($id) {
            global $categoryQuestion;
            $question = $categoryQuestion->getSortedList($id, "category_id");

            for ($i = 0; $i < count($question); $i++) {
                $data[$i]['question'] = $question[$i]['title'];
                if ($question[$i]['type'] == 0) {
                    $data[$i]['questionType'] = "Selection";
                    $data[$i]['questionTypeOptions'] = explode("\n", $question[$i]['data'] );
                } else {
                    $data[$i]['questionType'] = "Text";
                }
            }

            return $data;
        }

        public function apiGetList($location, $type="all") {
            global $search;
            $result = array();
            
            if ($type == "all") {
                $x = 0;
            } else if ($type == "parent") {
                $x = 0;
            } else {
                $x = intval($type);
            }
            $list = $this->categoryList($location['ref'], $x);
            
            for ($i = 0; $i < count($list); $i++) {
                $result[$i]['ref'] = $list[$i]['ref'];
                $result[$i]['title'] = $list[$i]['category_title'];
                $result[$i]['call_out_charge'] = $list[$i]['call_out_charge'];
                $result[$i]['icon'] = $this->getIcon($list[$i]['ref']);
                $result[$i]['ad_count'] = $search->catSearch($location, $list[$i]['ref'], "count");
                
                if ($type == "all") {
                    $sub = $this->categoryList($location['ref'], $list[$i]['ref']);
                    for ($j = 0; $j < count($sub); $j++) {
                        $result[$i]['sub'][$j]['ref'] = $sub[$j]['ref'];
                        $result[$i]['sub'][$j]['title'] = $sub[$j]['category_title'];
                        $result[$i]['sub'][$j]['call_out_charge'] = $sub[$j]['call_out_charge'];
                        $result[$i]['sub'][$j]['icon'] = $this->getIcon($sub[$j]['ref']);
                        $result[$i]['sub'][$j]['ad_count'] = $search->catSearch($location, $sub[$j]['ref'], "count");
                    }
                }
            }

            return $result;
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`category` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `parent_id` INT NOT NULL, 
                `country` INT NOT NULL, 
                `category_title` VARCHAR(50) NOT NULL,
                `image_url` VARCHAR(500) NOT NULL,
                `image_url_svg` VARCHAR(500) NOT NULL,
                `call_out_charge` DOUBLE NOT NULL, 
                `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`category`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`category`";

            $this->query($query);
        }
    }
?>