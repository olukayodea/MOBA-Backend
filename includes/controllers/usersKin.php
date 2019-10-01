<?php
class usersKin extends users {
    /*  create users track
    */
    public function create($array) {
        $replace = array();
        $replace[] = "kin_name";
        $replace[] = "kin_email";
        $replace[] = "kin_phone";
        $replace[] = "kin_relationship";
        $create = $this->replace("usersKin", $array, $replace);
        if ($create) {
            return true;
        } else {
            return false;
        }
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`usersKin` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `user_id` INT NOT NULL, 
            `kin_name` VARCHAR(255) NOT NULL,
            `kin_email` VARCHAR(255) NOT NULL,
            `kin_phone` VARCHAR(255) NOT NULL,
            `kin_relationship` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`ref`),
            UNIQUE KEY `user_id` (`user_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`usersKin`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`usersKin`";

        $this->query($query);
    }
}
?>