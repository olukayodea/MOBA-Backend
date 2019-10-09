<?php
class usersCategory extends users {
    /*  create users track
    */
    public function create($array) {
        $create = $this->insert("usersCategory", $array);
        if ($create) {
            return $create;
        } else {
            return false;
        }
    }

    function getSortedListCat($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll("usersCategory", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`usersCategory` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `user_id` INT NOT NULL, 
            `category_id` INT NOT NULL, 
            PRIMARY KEY (`ref`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`usersCategory`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`usersCategory`";

        $this->query($query);
    }
}
?>