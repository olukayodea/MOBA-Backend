<?php
class usersTrack extends database {
    /*  create users track
    */

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`usersTrack` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `user_id` INT NOT NULL, 
            `device_model` VARCHAR(50) NULL, 
            `os_version` VARCHAR(50) NULL, 
            `app_version` VARCHAR(50) NULL, 
            `state` VARCHAR(50) NULL, 
            `country` VARCHAR(50) NULL, 
            `latitude` VARCHAR(50) NULL, 
            `longitude` VARCHAR(50) NULL, 
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`)
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