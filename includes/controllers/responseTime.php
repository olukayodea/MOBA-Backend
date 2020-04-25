<?php
class responseTime extends database {
    public function set($task, $user) {
        $query = "SELECT `now_time` FROM `response_time` WHERE `user_id` = ".$user." AND `tast_id` = ".$task;

        $pre_time = $this->query($query, false, "getCol");

        if ($pre_time == '0000-00-00 00:00:00') {
            $pre_time = date("Y-m-d G:H:s");
        }

        $data['user_id'] = $user;
        $data['tast_id'] = $task;
        $data['pre_time'] = $pre_time;

        if ($this->insert("response_time", $data)) {
            $this->updateUser($user);
        }
    }

    private function updateUser($user) {
        global $users;
        $query = $this->query("SELECT AVG(TIMESTAMPDIFF(SECOND, `pre_time`, `now_time`)) FROM `response_time` WHERE `user_id` = ".$user, false, "getCol");

        $users->modifyUser("average_response_time", $this->seconds2human($query), $user);

    }

    private function seconds2human($ss) {
        $s = $ss%60;
        $m = floor(($ss%3600)/60);

        if ($m > 0) {
            $text = "$m minute";
            if ($m > 1) {
                $text = $text."s";
            }
        } else {
            $text = "$s seconds";
        }
        $h = floor(($ss%86400)/3600);
        if ($h > 0) {
            $text = "$h hour";
            if ($h > 1) {
                $text = $text."s";
            }
            $text = $text.", ";
        }
        $d = floor(($ss%2592000)/86400);
        if ($d > 0) {
            $text = "$d day";
            if ($d > 1) {
                $text = $text."s";
            }
            $text = $text.", ";
        }
        $M = floor($ss/2592000);
        if ($M > 0) {
            $text = "$M month";
            if ($M > 1) {
                $text = $text."s";
            }
            $text = $text.", ";
        }
        return $text;
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE `".dbname."`.`response_time` (
            `ref` INT NOT NULL AUTO_INCREMENT ,
            `user_id` INT NOT NULL ,
            `pre_time` DATETIME NOT NULL,
            `now_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `tast_id` INT NOT NULL ,
            PRIMARY KEY (`ref`)
        ) ENGINE = InnoDB;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`response_time`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`response_time`";

        $this->query($query);
    }

}
?>
