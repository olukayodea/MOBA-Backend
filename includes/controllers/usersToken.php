<?php
class usersToken extends database {
    /*  create users token
    */
    public function create($array) {
        $create = $this->insert("usersToken", $array, true);
        if ($create) {
            return true;
        } else {
            return false;
        }
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll("usersToken", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`usersToken` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `token` VARCHAR(100) NOT NULL, 
            `user_id` INT NULL,
            `channel` VARCHAR(10) NOT NULL, 
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`), 
            UNIQUE `token` (`token`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`usersTrack`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`usersTrack`";

        $this->query($query);
    }
}
?>