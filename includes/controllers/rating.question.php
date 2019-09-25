<?php
    class rating_question extends database {
        public function create($array) {
            $replace[] = "question_type";
            $replace[] = "question";
            $replace[] = "status";
            if ($array['ref'] == 0) {
                unset($array['ref']);
            }
            $create = $this->replace("rating_question", $array, $replace);
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

            $this->updateOne("rating_question", "status", $updateData, $id, "ref");
            return true;
        }

        function remove($id) {
            $this->delete("rating_question", $id);
            return true;
        }

        function getList($start=false, $limit=false, $type="list") {
            return $this->list("rating_question", $start, $limit, "question", "ASC", false, $type);
        }

		function getSingle($value, $ref="question", $tag="ref") {
            return $this->getOneField("rating_question", $value, $tag, $ref);
		}

        function listOne($id, $tag="ref") {
            return $this->getOne("rating_question", $id, $tag);
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("rating_question", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`rating_question` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `question_type` VARCHAR(50) NOT NULL, 
                `question` VARCHAR(5000) NOT NULL, 
                `status` VARCHAR(50) NOT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`rating_question`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`rating_question`";

            $this->query($query);
        }
    }
?>