<?php
    class rating extends database {
        public function addRate($array) {
            global $request;
            global $rating_comment;
            $postArray['user_id'] = $array['user_id'];
            $postArray['reviewed_by'] = $array['reviewed_by'];
            $postArray['post_id'] = $array['post_id'];

            foreach ($array['rating'] as $key => $value) {
                $postArray['question_id'] = $key;
                $postArray['review'] = $value;

                $this->create($postArray);
            }

            unset($postArray['question_id']);
            unset($postArray['review']);
            $postArray['comment'] = $array['comment'];
            $rating_comment->create($postArray);

            if ($array['type'] == "user_id") {
                $request->updateOneRow( "user_rate", 1, $array['post_id'] );
            } else  if ($array['type'] == "client_id ") {
                $request->updateOneRow( "client_rate", 1, $array['post_id'] );
            }

            return true;
        }

        public function create($array) {
            $create = $this->insert("rating", $array);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

        public function getRate($user, $question_id=false) {
            $query = "SELECT AVG(`review`) FROM `rating` WHERE `user_id` = :user";
            if ($question_id !== false) {
                $query .= " AND `question_id` = :question_id";
                $prepare[':question_id'] = $question_id;
            }
            $prepare[':user'] = $user;
            return  $this->query($query, $prepare, "getCol");
        }

        public function drawRate($rate) {
            if ($rate >= 5) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate > 4) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fas fa-star-half-alt" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate == 4) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate > 3) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fas fa-star-half-alt" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate == 3) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate > 2) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fas fa-star-half-alt" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate == 2) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate > 1) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="fas fa-star-half-alt" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate == 1) {
                return '<i class="fa fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate > 0) {
                return '<i class="fas fa-star-half-alt" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            } else if ($rate == 0) {
                return '<i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i><i class="far fa-star" aria-hidden="true" style="color:#F93"></i>';
            }
        }

        public function textRate($rate) {
            if ($rate >= 5) {
                return 'Excellent';
            } else if ($rate > 4) {
                return 'Very Good';
            } else if ($rate == 4) {
                return 'Good';
            } else if ($rate > 3) {
                return 'Above Average';
            } else if ($rate == 3) {
                return 'Average';
            } else if ($rate > 2) {
                return 'Below Average';
            } else if ($rate == 2) {
                return 'Bad';
            } else if ($rate > 1) {
                return 'Very Bad';
            } else if ($rate == 0) {
                return 'Unrated';
            }
        }


        function listOne($id, $tag="ref") {
            return $this->getOne("rating", $id, $tag);
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("rating", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function publicPage($id, $view) {
            $query = "SELECT ( SUM(`review`)/( COUNT(`review`)* 5 ) )* 5 AS `val`, `rating_question`.`question_type`, `rating_question`.`question` FROM `rating`, `rating_question` WHERE `rating`.`question_id` = `rating_question`.`ref` AND `user_id` = ".$id." AND `rating_question`.`question_type` = '".$view."' GROUP BY `question_id`";

            return $this->run($query, false, "list");
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`rating` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `reviewed_by` INT NOT NULL, 
                `post_id` INT NOT NULL, 
                `question_id` INT NOT NULL, 
                `review` INT NOT NULL,
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`),
                INDEX par_ind (`question_id`),
                FOREIGN KEY (question_id)
                    REFERENCES rating_question(`ref`)
                    ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`rating`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`rating`";

            $this->query($query);
        }
    }
?>