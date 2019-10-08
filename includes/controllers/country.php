<?php
    class country_state extends database {
        /*  create country
        */
        public function create($array) {
            $replace[] = "state";
            if ($array['ref'] == 0) {
                unset($array['ref']);
            }
            $create = $this->replace("country_state", $array, $replace);
            if ($create) {
                return true;
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

            $this->updateOne("country_state", "status", $updateData, $id, "ref");
            return true;
        }

        function remove($id) {
            $this->delete("country_state", $id);
            return true;
        }

        function getList($start=false, $limit=false, $type="list") {
            return $this->lists("country_state", $start, $limit, "name", "ASC", false, $type);
        }

		function getSingle($value, $ref="state", $tag="ref") {
            return $this->getOneField("country_state", $value, $tag, $ref);
		}

        function listOne($id, $tag="state") {
            return $this->getOne("country_state", $id, $tag);
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
            return $this->sortAll("country_state", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`country_state` (
                `ref` INT NOT NULL AUTO_INCREMENT,
                `country` INT NOT NULL, 
                `state` VARCHAR(50) NOT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`country_state`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`country_state`";

            $this->query($query);
        }
    }
    include_once("country_state.php");
    $country_state  = new country_state;
?>