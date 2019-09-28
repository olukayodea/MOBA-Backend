<?php
    class banks extends database {
        /*  create users
        */
        public function create($array) {
            $replace = array();
            $replace[] = "financial_institution";
            $replace[] = "name";
            $replace[] = "status";
            $replace[] = "country";
            if ($array['ref'] == 0) {
                unset($array['ref']);
            }
            $create = $this->replace("banks", $array, $replace);
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

            $this->updateOne("banks", "status", $updateData, $id, "ref");
            return true;
        }

        function remove($id) {
            $this->updateOne("banks", "status", "DELETED", $id, "ref");
            return true;
        }

        function getList($start=false, $limit=false, $order="name", $dir="ASC", $type="list") {
            return $this->lists("banks", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
        }

		function getSingle($name, $tag="name", $ref="ref") {
            return $this->getOneField("banks", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("banks", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'name', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
            return $this->sortAll("banks", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
        }

        public function apiGetList($region) {
            $list = $this->getSortedList("ACTIVE", "status", "country", $region['ref']);

            for ($i = 0; $i < count($list); $i++) {
                unset($list[$i]['financial_institution']);
                unset($list[$i]['country']);
                unset($list[$i]['status']);
                unset($list[$i]['create_time']);
                unset($list[$i]['modify_time']);
            }

            return $list;
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`banks` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `name` VARCHAR(50) NOT NULL,
                `financial_institution` VARCHAR(50) NOT NULL,
                `country` INT NOT NULL, 
                `status` varchar(20) NOT NULL DEFAULT 'INACTIVE',
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`banks`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`banks`";

            $this->query($query);
        }
    }
?>