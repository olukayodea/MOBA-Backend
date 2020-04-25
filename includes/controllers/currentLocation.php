<?php
class currentLocation extends database {
    public function set($user, $longitude, $latitude, $city, $state, $country) {

        $data['user_id'] = $user;
        $data['longitude'] = $longitude;
        $data['latitude'] = $latitude;
        $data['city'] = $city;
        $data['state'] = $state;
        $data['country'] = $country;

        $replace[] = "longitude";
        $replace[] = "latitude";
        $replace[] = "city";
        $replace[] = "state";
        $replace[] = "country";

        return $this->replace("currentLocation", $data, $replace);
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE `".dbname."`.`currentLocation` (
            `ref` INT NOT NULL AUTO_INCREMENT ,
            `user_id` INT NOT NULL ,
            `longitude` DOUBLE NOT NULL,
            `latitude` DOUBLE NOT NULL ,
            `city` VARCHAR(255) NOT NULL ,
            `state` VARCHAR(255) NOT NULL ,
            `country` VARCHAR(255) NOT NULL ,
            PRIMARY KEY (`ref`),
            UNIQUE KEY `user_id` (`user_id`)
        ) ENGINE = InnoDB;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`currentLocation`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`currentLocation`";

        $this->query($query);
    }

}
?>
