<?php
    class search extends database {
        public function isFeaturedData($location, $start, $limit) {
            $return['data'] = $this->isFeatured($location, "list", $start, $limit);
            $return['count'] = $this->isFeatured($location, "count");

            return $return;
        }

        public function isFeatured($location, $type="list", $start=false, $limit=false) {
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];

            if ($limit == true ) {
                $limitData = " LIMIT ".$start.", ".$limit;
            } else {
                $limitData = "";
            }
            
            //$query = "SELECT `ref`, `last_name`, `other_names`, `screen_name`, `email`, `mobile_number`, `street`, `city`, `state`, `country`, `account_type`, `latitude`, `longitude`, `about_me`, `image_url`, `average_response_time`, SQRT(((`latitude` - ".$latitude.")*(`latitude` - ".$latitude.")) + ((`longitude` - ".$longitude.")*(`longitude` - ".$longitude."))) AS `total` FROM `users` WHERE `status` = 'ACTIVE' AND `user_type` = 1 AND `is_featured` = 1 AND ((`country` LIKE '".$location['code']."' OR `country` LIKE '".$location['country']."') AND (`state` LIKE '".$location['state_code']."' OR `state` LIKE '".$location['state']."')) ORDER BY `total` ASC".$limitData;
            
            $query = "SELECT `ref`, `last_name`, `other_names`, `screen_name`, `email`, `mobile_number`, `street`, `city`, `state`, `country`, `account_type`, `latitude`, `longitude`, `about_me`, `image_url`, `average_response_time`, SQRT(((`latitude` - ".$latitude.")*(`latitude` - ".$latitude.")) + ((`longitude` - ".$longitude.")*(`longitude` - ".$longitude."))) AS `total` FROM `users` WHERE `status` = 'ACTIVE' AND `user_type` = 1 AND `is_featured` = 1 ORDER BY `total` ASC".$limitData;

            return $this->run($query, false, $type);
        }

        public function aroundMeData($location, $start, $limit) {
            $return['data'] = $this->aroundMe($location, "list", $start, $limit);
            $return['count'] = $this->aroundMe($location, "count");

            return $return;
        }

        public function aroundMe($location, $type="list", $start=false, $limit=false) {
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];

            if ($limit == true ) {
                $limitData = " LIMIT ".$start.", ".$limit;
            } else {
                $limitData = "";
            }
            
            //$query = "SELECT `ref`, `last_name`, `other_names`, `screen_name`, `email`, `mobile_number`, `street`, `city`, `state`, `country`, `account_type`, `latitude`, `longitude`, `about_me`, `image_url`, `average_response_time`, SQRT(((`latitude` - ".$latitude.")*(`latitude` - ".$latitude.")) + ((`longitude` - ".$longitude.")*(`longitude` - ".$longitude."))) AS `total` FROM `users` WHERE `status` = 'ACTIVE' AND `user_type` = 1 AND ((`country` LIKE '".$location['code']."' OR `country` LIKE '".$location['country']."') AND (`state` LIKE '".$location['state_code']."' OR `state` LIKE '".$location['state']."')) ORDER BY `is_featured` DESC, `total` ASC".$limitData;

            $query = "SELECT `ref`, `last_name`, `other_names`, `screen_name`, `email`, `mobile_number`, `street`, `city`, `state`, `country`, `account_type`, `latitude`, `longitude`, `about_me`, `image_url`, `average_response_time`, SQRT(((`latitude` - ".$latitude.")*(`latitude` - ".$latitude.")) + ((`longitude` - ".$longitude.")*(`longitude` - ".$longitude."))) AS `total` FROM `users` WHERE `status` = 'ACTIVE' AND `user_type` = 1 ORDER BY `is_featured` DESC, `total` ASC".$limitData;

            return $this->run($query, false, $type);
        }

        public function keywordSearchData($location, $keyWord, $start, $limit) {
            $return['data'] = $this->keywordSearch($location, $keyWord, "list", $start, $limit);
            $return['count'] = $this->keywordSearch($location, $keyWord, "count");

            return $return;
        }
        
        public function keywordSearch($location, $val, $type="list", $start=false, $limit=false) {
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];

            if ($limit == true ) {
                $limitData = " LIMIT ".$start.", ".$limit;
            } else {
                $limitData = "";
            }
            
            //$query = "SELECT `users`.`ref`, `users`.`last_name`, `users`.`other_names`, `users`.`screen_name`, `users`.`email`, `users`.`mobile_number`, `users`.`street`, `users`.`city`, `users`.`state`, `users`.`country`, `users`.`account_type`, `users`.`latitude`, `users`.`longitude`, `users`.`about_me`, `users`.`image_url`, `users`.`average_response_time`, SQRT(((`users`.`latitude` - ".$latitude.")*(`users`.`latitude` - ".$latitude.")) + ((`users`.`longitude` - ".$longitude.")*(`users`.`longitude` - ".$longitude."))) AS `total` FROM `users`,`usersCategory` WHERE `users`.`status` = 'ACTIVE' AND `users`.`ref` = `usersCategory`.`user_id` AND `users`.`user_type` = 1 AND (`users`.`last_name` LIKE :s OR `users`.`other_names` LIKE :s OR `users`.`screen_name` LIKE :s OR `usersCategory`.`category_id` IN (SELECT `category`.`ref` FROM `category` WHERE `category`.`category_title` LIKE :s)) AND ((`country` LIKE '".$location['code']."' OR `country` LIKE '".$location['country']."') AND (`state` LIKE '".$location['state_code']."' OR `state` LIKE '".$location['state']."')) GROUP BY `users`.`ref` ORDER BY `users`.`is_featured` DESC, `total` ASC".$limitData;
            
            $query = "SELECT `users`.`ref`, `users`.`last_name`, `users`.`other_names`, `users`.`screen_name`, `users`.`email`, `users`.`mobile_number`, `users`.`street`, `users`.`city`, `users`.`state`, `users`.`country`, `users`.`account_type`, `users`.`latitude`, `users`.`longitude`, `users`.`about_me`, `users`.`image_url`, `users`.`average_response_time`, SQRT(((`users`.`latitude` - ".$latitude.")*(`users`.`latitude` - ".$latitude.")) + ((`users`.`longitude` - ".$longitude.")*(`users`.`longitude` - ".$longitude."))) AS `total` FROM `users`,`usersCategory` WHERE `users`.`status` = 'ACTIVE' AND `users`.`ref` = `usersCategory`.`user_id` AND `users`.`user_type` = 1 AND (`users`.`last_name` LIKE :s OR `users`.`other_names` LIKE :s OR `users`.`screen_name` LIKE :s OR `usersCategory`.`category_id` IN (SELECT `category`.`ref` FROM `category` WHERE `category`.`category_title` LIKE :s)) GROUP BY `users`.`ref` ORDER BY `users`.`is_featured` DESC, `total` ASC".$limitData;

            $prepare[":s"] = $val;

            return $this->run($query, $prepare, $type, "s");
        }
        
        public function category($country, $val) {
            $query = "SELECT * FROM `category` WHERE `country` = '".$country."' AND `category`.`category_title` LIKE :s ORDER BY `category_title` ASC";

            $prepare[":s"] = $val;

            return $this->run($query, $prepare, "list", "s");
        }

        public function catSearchData($location, $keyWord, $start, $limit) {
            $return['data'] = $this->catSearch($location, $keyWord, "list", $start, $limit);
            $return['count'] = $this->catSearch($location, $keyWord, "count");

            return $return;
        }

        public function catSearch($location, $val, $type="list", $start=false, $limit=false) {
            $data = $location;
            $latitude = $data['latitude'];
            $longitude = $data['longitude'];

            if ($limit == true ) {
                $limitData = " LIMIT ".$start.", ".$limit;
            } else {
                $limitData = "";
            }
            
            $query = "SELECT `users`.`ref`, `users`.`last_name`, `users`.`other_names`, `users`.`screen_name`, `users`.`email`, `users`.`mobile_number`, `users`.`street`, `users`.`city`, `users`.`state`, `users`.`country`, `users`.`account_type`, `users`.`latitude`, `users`.`longitude`, `users`.`about_me`, `users`.`image_url`, `users`.`average_response_time`, SQRT(((`users`.`latitude` - ".$latitude.")*(`users`.`latitude` - ".$latitude.")) + ((`users`.`longitude` - ".$longitude.")*(`users`.`longitude` - ".$longitude."))) AS `total` FROM `usersCategory`, `users` WHERE `users`.`status` = 'ACTIVE' AND `users`.`user_type` = 1 AND `usersCategory`.`user_id` = `users`.`ref` AND `category_id` = ".$val." AND ((`users`.`country` LIKE '".$data['code']."' OR `users`.`country` LIKE '".$data['country']."') AND (`users`.`state` LIKE '".$data['state_code']."' OR `users`.`state` LIKE '".$data['state']."')) GROUP BY `usersCategory`.`user_id` ORDER BY `users`.`is_featured` DESC, `total` ASC".$limitData;
            
            return $this->run($query, false, $type, "s");
        }
    }
?>