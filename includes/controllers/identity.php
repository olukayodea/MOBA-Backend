<?php
class identity extends database {
    /*  create identity
    */
    public function create($array) {
        $replace[] = "name";
        $replace[] = "status";
        if ($array['ref'] == 0) {
            unset($array['ref']);
        }
        $create = $this->replace("identity", $array, $replace);
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

        $this->updateOne("identity", "status", $updateData, $id, "ref");
        return true;
    }

    function remove($id) {
        $this->delete("identity", $id);
        return true;
    }

    function getList($start=false, $limit=false, $type="list") {
        return $this->list("identity", $start, $limit, "name", "ASC", false, $type);
    }

    function getSingle($value, $ref="name", $tag="ref") {
        return $this->getOneField("identity", $value, $tag, $ref);
    }

    function listOne($id, $tag="name") {
        return $this->getOne("identity", $id, $tag);
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll("identity", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function apiGetList($location) {
        $result = array();
        $list = $this->getSortedList("ACTIVE", 'status', "country", $location['ref']);
        
        for ($i = 0; $i < count($list); $i++) {
            $result[$i]['ref'] = $list[$i]['ref'];
            $result[$i]['title'] = $list[$i]['name'];
        }

        return $result;
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`identity` (
            `ref` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(50) NOT NULL, 
            `country` INT NOT NULL, 
            `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`identity`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`identity`";

        $this->query($query);
    }
}
?>