<?php
    class search extends database {
        public function jobSearchData($location, $keyWord, $start, $limit) {
            $return['data'] = $this->jobSearch($location, $keyWord, "list", $start, $limit);
            $return['count'] = $this->jobSearch($location, $keyWord, "count");

            return $return;
        }

        public function jobSearch($location, $val, $type="list", $start=false, $limit=false) {
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];

            if ($limit == true ) {
                $limitData = " LIMIT ".$start.", ".$limit;
            } else {
                $limitData = "";
            }
            
            if (isset($_SESSION['filter'])) {
                $filter = "AND `project_type` = '".$_SESSION['filter']."'  ";
            } else {
                $filter = "";
            }
            $query = "SELECT `ref`, `user_id`, `category_id`, `project_code`, `project_name`, `project_dec`, `project_type`, `tag`, `allow_remote`, `address`,`default_fee`, `lat`, `lng`, SQRT(((`lat`- ".$latitude.")*(`lat`- ".$latitude.")) + ((`lng`- ".$longitude.")*(`lng`- ".$longitude."))) AS `total`, MATCH(`tag`) AGAINST (:ft) AS `score`, MATCH(`address`) AGAINST (:ft) AS `score2`, MATCH(`project_dec`) AGAINST (:ft) AS `score3` FROM `projects` WHERE status = 'ACTIVE' ".$filter."AND (`project_code` LIKE :s OR `project_name` LIKE :s OR `tag` LIKE :s OR `address` LIKE :s OR `city` LIKE :s OR `state` LIKE :s OR `postal_code` LIKE :s OR `country` LIKE :s OR MATCH(`project_dec`) AGAINST (:ft) OR MATCH(`tag`) AGAINST (:ft) OR MATCH(`address`) AGAINST (:ft)) AND ((`country` LIKE '".$location['code']."' OR `country` LIKE '".$location['country']."') AND (`state` LIKE '".$location['state_code']."' OR `state` LIKE '".$location['state']."')) ORDER BY `total` ASC, `score3`,`score2`,`score` DESC".$limitData;

            $prepare[":s"] = $val;
            $prepare[":ft"] = $val;

            return $this->run($query, $prepare, $type, "s");
        }

        public function keywordSearchData($location, $keyWord, $start, $limit) {
            $return['data'] = $this->keywordSearch($location, $keyWord, "list", $start, $limit);
            $return['count'] = $this->keywordSearch($location, $keyWord, "count");

            return $return;
        }
        
        private function keywordSearch($location, $val, $type="list", $start=false, $limit=false) {
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];

            if ($limit == true ) {
                $limitData = " LIMIT ".$start.", ".$limit;
            } else {
                $limitData = "";
            }
            
            if (isset($_SESSION['filter'])) {
                $filter = "AND `project_type` = '".$_SESSION['filter']."'  ";
            } else {
                $filter = "";
            }
            $query = "SELECT `ref`, `user_id`, `category_id`, `project_code`, `project_name`, `project_dec`, `project_type`, `tag`, `allow_remote`, `default_fee`,`address`, `lat`, `lng`, SQRT(((`lat`- ".$latitude.")*(`lat`- ".$latitude.")) + ((`lng`- ".$longitude.")*(`lng`- ".$longitude."))) AS `total`, MATCH(`tag`) AGAINST (:ft) AS `score` FROM `projects` WHERE status = 'ACTIVE' ".$filter."AND (`tag` LIKE :s OR MATCH(`tag`) AGAINST (:ft)) AND ((`country` LIKE '".$location['code']."' OR `country` LIKE '".$location['country']."') AND (`state` LIKE '".$location['state_code']."' OR `state` LIKE '".$location['state']."')) ORDER BY `total` ASC, `score` DESC".$limitData;

            $prepare[":s"] = $val;
            $prepare[":ft"] = $val;

            return $this->run($query, $prepare, $type, "s");
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

            if (isset($_SESSION['filter'])) {
                $filter = "AND `project_type` = '".$_SESSION['filter']."'  ";
            } else {
                $filter = "";
            }
            
            $query = "SELECT `ref`, `user_id`, `category_id`, `project_code`, `project_name`, `project_dec`, `project_type`, `tag`, `allow_remote`, `address`,`country`,`country`,`is_featured`,`page_visit`,`billing_type`,`default_fee`, `lat`, `lng`, SQRT(((`lat`- ".$latitude.")*(`lat`- ".$latitude.")) + ((`lng`- ".$longitude.")*(`lng`- ".$longitude."))) AS `total` FROM `projects` WHERE status = 'ACTIVE' ".$filter."AND (`category_id` LIKE '".$val.",%' OR `category_id` LIKE '".$val."' OR `category_id` LIKE '%,".$val."' OR `category_id` LIKE '%,".$val.",%') AND ((`country` LIKE '".$data['code']."' OR `country` LIKE '".$data['country']."') AND (`state` LIKE '".$data['state_code']."' OR `state` LIKE '".$data['state']."')) ORDER BY `total` ASC".$limitData;

            return $this->run($query, false, $type, "s");
        }

        public function usersMailSearch($val, $mobile=false, $type="list", $start=false, $limit=false) {
            if ($mobile == false) {
                $ref = $_SESSION['users']['ref'];
            } else {
                $ref = $mobile;
            }
            if ($limit == true ) {
                $limitData = " LIMIT ".$start.", ".$limit;
            } else {
                $limitData = "";
            }
            $query = "SELECT `ref`, `last_name`, `other_names`, `screen_name` FROM `users` WHERE (`email` LIKE :s OR `last_name` LIKE :s OR `other_names` LIKE :s OR `screen_name` LIKE :s) AND ref != ".$ref." ORDER BY `last_name` ASC".$limitData;

            $prepare[":s"] = $val;

            return $this->run($query, $prepare, $type, "s");
        }
    }
?>