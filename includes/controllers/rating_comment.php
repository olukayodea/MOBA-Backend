<?php
    class rating_comment extends database {
        public function create($array) {
            $create = $this->insert("rating_comment", $array);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

		function getSingle($value, $ref="comment", $tag="ref") {
            return $this->getOneField("rating_comment", $value, $tag, $ref);
		}

        function listOne($id, $tag="ref") {
            return $this->getOne("rating_comment", $id, $tag);
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("rating_comment", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`rating_comment` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `reviewed_by` INT NOT NULL, 
                `project_id` INT NOT NULL, 
                `comment` VARCHAR(5000) NOT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`rating_comment`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`rating_comment`";

            $this->query($query);
        }
    }
?>