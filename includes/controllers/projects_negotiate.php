<?php
    class projects_negotiate extends database {
        /*  create users
        */
        public function create($array) {
            $create = $this->insert("projects_negotiate", $array);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

        public function updateOneRow($tag, $value, $id) {
            return $this->updateOne("projects_negotiate", $tag, $value, $id, "ref");
        }

        function getSingle($name, $tag="project_name", $ref="ref") {
            return $this->getOneField("projects_negotiate", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("projects_negotiate", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'project_id', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
            return $this->sortAll("projects_negotiate", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
        }

        public function checkCurrent($array) {
            $query = "SELECT * FROM `projects_negotiate` WHERE `status` = 0 AND `project_id` = :project_id AND `user_id` = :user_id AND `user_r_id` = :user_r_id";
            $prepare[":project_id"] = $array['project'];
            $prepare[":user_id"] = $array['user'];
            $prepare[":user_r_id"] = $array['user_r'];

            return $this->run($query, $prepare, "count");
        }

        public function getApproved($array) {
            $query = "SELECT * FROM `projects_negotiate` WHERE `status` = 2 AND `project_id` = :project_id AND (( `user_id` = :user_id AND `user_r_id` = :user_r_id ) OR (`user_id` = :user_r_id AND `user_r_id` = :user_id )) ORDER BY `ref` DESC LIMIT 1";
            $prepare[":project_id"] = $array['project'];
            $prepare[":user_id"] = $array['user'];
            $prepare[":user_r_id"] = $array['user_r'];

            return $this->run($query, $prepare, "getRow");
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`projects_negotiate` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `project_id` INT NOT NULL, 
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
            $query = "TRUNCATE `".dbname."`.`projects_negotiate`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`projects_negotiate`";

            $this->query($query);
        }
    }
?>