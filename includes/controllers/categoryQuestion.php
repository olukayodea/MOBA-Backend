<?php
    class categoryQuestion extends database {
        /*  create users
        */
        public function create($array) {
            $replace = array();
            $replace[] = "title";
            $replace[] = "type";
            $replace[] = "data";
            if ($array['ref'] == 0) {
                unset($array['ref']);
            }
            $create = $this->replace("categoryQuestion", $array, $replace);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

        function remove($id) {
            return $this->delete("categoryQuestion", $id);
        }

        function getList($start=false, $limit=false, $order="category_id", $dir="ASC", $type="list") {
            return $this->lists("categoryQuestion", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
        }

		function getSingle($name, $tag="category_id", $ref="ref") {
            return $this->getOneField("categoryQuestion", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("categoryQuestion", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'category_id', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("categoryQuestion", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }
        
        public function categoryListPages($id, $start, $limit) {
            $return['data'] = $this->categoryList($id, "list", $start, $limit);
            $return['count'] = $this->categoryList($id, "count");

            return $return;
        }

        function categoryList( $id, $type="list", $start=false, $limit=false) {
            return $this->getSortedList($id, "category_id", false, false, false, false, "title", "ASC", "AND", $start,$limit, $type);
        }

        private function clean($data) {
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

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`categoryQuestion` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `title` VARCHAR(500) NOT NULL,
                `category_id` INT NOT NULL, 
                `type` INT NOT NULL, 
                `data` TEXT NULL, 
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