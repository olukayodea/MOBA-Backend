<?php
class country extends database {
    /*  create country
    */
    public function create($array) {
        $replace[] = "code";
        $replace[] = "name";
        $replace[] = "currency";
        $replace[] = "currency_symbol";
        $replace[] = "dial_code";
        $replace[] = "featured_ad";
        $replace[] = "si_unit";
        $replace[] = "tax";
        $replace[] = "status";
        if ($array['ref'] == 0) {
            unset($array['ref']);
        }
        $create = $this->replace("country", $array, $replace);
        if ($create) {
            if ($this->checkExixst("country", "is_default", 1,"count") < 1) {
                $this->setDefault($create);
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function countryAPI() {
        global $country_state;
        $data = $this->getSortedList("ACTIVE", "status");

        for ($i = 0; $i < count($data); $i++) {
            $state = $country_state->getSortedList("ACTIVE", "status", "country", $data[$i]['ref']);
            for ($j = 0; $j < count($data); $j++) {
                $stateList[] = ucfirst(strtolower($state[$j]['state']));
            }
            $list[$i]['name'] = ucfirst(strtolower($data[$i]['name']));
            $list[$i]['states'] = $stateList;
        }

        return $list;
    }

    function toggleStatus($id) {
        $data = $this->listOne($id);
        if ($data['status'] == "ACTIVE") {
            $updateData = "INACTIVE";
        } else if ($data['status'] == "INACTIVE") {
            $updateData = "ACTIVE";
        }

        $this->updateOne("country", "status", $updateData, $id, "ref");
        return true;
    }

    function remove($id) {
        $this->delete("country", $id);
        return true;
    }

    function setDefault($id) {
        $getFormer = $this->getSingle("1", "ref", "is_default");
        $this->updateOne("country", "is_default", 0, $getFormer, "ref");
        $this->updateOne("country", "is_default", 1, $id, "ref");
        return true;
    }

    function getCountryData($value, $view="currency_symbol", $row="name") {
        return $this->getSingle($value, $view, $row);
    }

    function getList($start=false, $limit=false, $type="list") {
        return $this->lists("country", $start, $limit, "name", "ASC", false, $type);
    }

    function getSingle($value, $ref="name", $tag="ref") {
        return $this->getOneField("country", $value, $tag, $ref);
    }

    function listOne($id, $tag="code") {
        return $this->getOne("country", $id, $tag);
    }

    function getLoc($code, $tag="code") {
        $data = $this->listOne($code, $tag);

        if ($data) {
            return $data;
        } else {
            return $this->listOne(1, "is_default");
        }
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll("country", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`country` (
            `ref` INT NOT NULL AUTO_INCREMENT,
            `code` VARCHAR(2) NOT NULL, 
            `name` VARCHAR(20) NOT NULL, 
            `currency` VARCHAR(3) NOT NULL, 
            `currency_symbol` VARCHAR(20) NOT NULL, 
            `dial_code` VARCHAR(5) NOT NULL, 
            `si_unit` VARCHAR(10) NOT NULL, 
            `featured_ad` DOUBLE NOT NULL, 
            `tax` INT NOT NULL, 
            `is_default` INT NOT NULL, 
            `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`),
            UNIQUE KEY `code` (`code`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`country`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`country`";

        $this->query($query);
    }
}
include_once("country_state.php");
$country_state  = new country_state;
?>