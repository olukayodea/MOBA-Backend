<?php
    class category extends database {
        /*  create users
        */
        public function create($array) {
            $replace = array();
            $replace[] = "parent_id";
            $replace[] = "category_title";
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
            return $this->list("category", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
        }

		function getSingle($name, $tag="category_title", $ref="ref") {
            return $this->getOneField("category", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("category", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'category_title', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
            return $this->sortAll("category", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
        }

        function categoryList($country, $parent = 0) {
            return $this->getSortedList($parent, "parent_id", "status", "ACTIVE", "country", $country);
        }

        public function getIcon($id) {
          $data = $this->getSingle($id, "image_url");
    
          if ($data != "") {
            return URL."media/categories/".$data;
          } else {
            return URL."media/categories/0.png";
          }
        }

        public function apiGetList($location, $type="all") {
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
                $result[$i]['icon'] = $this->getIcon($list[$i]['ref']);
                //$result[$i]['ad_count'] = $search->catSearch($location, $list[$i]['ref'], "count");
                if ($type == "all") {
                    $sub = $this->categoryList($location['ref'], $list[$i]['ref']);
                    for ($j = 0; $j < count($sub); $j++) {
                        $result[$i]['sub'][$j]['ref'] = $sub[$j]['ref'];
                        $result[$i]['sub'][$j]['title'] = $sub[$j]['category_title'];
                        $result[$i]['sub'][$j]['icon'] = $this->getIcon($sub[$j]['ref']);
                        //$result[$i]['sub'][$j]['ad_count'] = $search->catSearch($location, $sub[$j]['ref'], "count");
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