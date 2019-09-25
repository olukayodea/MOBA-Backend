<?php
    class projects_data extends database {
        /*  create users
        */
        public function create($array) {
            $create = $this->insert("projects_data", $array);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }
        
        function remove($id) {
            $this->delete("projects_data", $id);
            return true;
        }

        public function updateDate($array) {
            global $projects;
            global $messages;
            $data = $this->listOne($array['project_data_id']);
            $project_data = $projects->listOne($data['project_id']);
            $raw_get_data = unserialize($data['data_field']);
            
            if ($array['log'] == "log_hours") {
                $pending = intval($raw_get_data['content']);
                $done = intval($raw_get_data['complete']);
                $raw_get_data['content'] = $pending-$array['duration'];
                $raw_get_data['complete'] = $done+$array['duration'];
                $total = $raw_get_data['content']+$raw_get_data['complete'];
                if ($raw_get_data['content'] == 0) {
                    $raw_get_data['status'] = "Complete";
                } else {
                    $raw_get_data['status'] = "Pending";
                }

                $edit = $this->modifyOne("data_field", serialize($raw_get_data), $array['project_data_id']);

                if ($edit) {
                    $msg = $array['duration']." ".$this->addS("Hour", $array['duration'])." has been approved out of ".$total." ".$this->addS("Hour", $total).". Total hours completed is ".$raw_get_data['complete']." ".$this->addS("Hour", $raw_get_data['complete']).". Total hours remaining is ".$raw_get_data['content']." ".$this->addS("Hour", $raw_get_data['content']);
                    $msgArray['message']  = $msg;
                    $msgArray['user_r_id']  = $project_data['client_id'];
                    $msgArray['user_id']  = $project_data['user_id'];
                    $msgArray['project_id']  = $project_data['ref'];
                    $msgArray['m_type']  = "system";
                    $messages->add($msgArray);

                    $end_date = time()+(60*60*$raw_get_data['content']);

                    $this->updateOne("projects", "end_date", $end_date, $project_data['ref'], "ref");
                    return true;
                }
            } else if ($array['log'] == "log_mil") {
                $raw_get_data['content'][$array['milestone_data_id']]['attention'] = 0;
                $raw_get_data['content'][$array['milestone_data_id']]['complete'] = 1;
                $sum = 0;
                for ($i = 0; $i < count($raw_get_data['content']); $i++) {
                    if ($raw_get_data['content'][$i]['complete'] == 1) {
                        $sum++;
                    }
                }
                if ($sum == count($raw_get_data['content'])) {
                    $raw_get_data['status'] = "Complete";
                } else {
                    $raw_get_data['status'] = "Pending";
                }

                $edit = $this->modifyOne("data_field", serialize($raw_get_data), $array['project_data_id']);

                if ($edit) {
                    $msg = "A milestone has been approved";
                    $msgArray['message']  = $msg;
                    $msgArray['user_r_id']  = $project_data['client_id'];
                    $msgArray['user_id']  = $project_data['user_id'];
                    $msgArray['project_id']  = $project_data['ref'];
                    $msgArray['m_type']  = "system";
                    $messages->add($msgArray);

                    $total_time = 0;
                    for ($i = 0; $i < count($raw_get_data['content']); $i++) {
                        if (intval($raw_get_data['content'][$i]['complete'] ) != 1) {
                            $total_time = $total_time + ($raw_get_data['content'][$i]['duration'] * $projects->getDate($raw_get_data['content'][$i]['duration_lenght']));
                        }
                    }
                    $end_date = time()+$total_time;

                    $this->updateOne("projects", "end_date", $end_date, $project_data['ref'], "ref");
                    return true;
                }
            } else if ($array['log'] == "log_mil_review") {
                $raw_get_data['content'][$array['milestone_data_id']]['attention'] = 1;

                $edit = $this->modifyOne("data_field", serialize($raw_get_data), $array['project_data_id']);
                if ($edit) {
                    $msg = "The following milestone is ready for review '<br><br><strong>". $raw_get_data['content'][$array['milestone_data_id']]['data']."</strong>'";

                    $msgArray['message']  = $msg;
                    $msgArray['user_r_id']  = $project_data['user_id'];
                    $msgArray['user_id']  = $project_data['client_id'];
                    $msgArray['project_id']  = $project_data['ref'];
                    $msgArray['m_type']  = "system";
                    $messages->add($msgArray);

                    return true;
                }
            }
        }

        public function checkCurrent($array, $view="count", $status=0) {
            $query = "SELECT * FROM `projects_data` WHERE `status` = :status AND `project_id` = :project_id AND ((`user_id` = :user_id AND `user_r_id` = :user_r_id) OR (`user_id` = :user_r_id AND `user_r_id` = :user_id))";
            
            $prepare[":project_id"] = $array['project'];
            $prepare[":user_id"] = $array['user'];
            $prepare[":user_r_id"] = $array['user_r'];
            $prepare[":status"] = $status;

            return $this->run($query, $prepare, $view);
        }

        public function findMe($array) {
            $query = "SELECT * FROM `projects_data` WHERE `status` = 0 AND `project_id` = :project_id AND `user_id` = :user_id";
            
            $prepare[":project_id"] = $array['project'];
            $prepare[":user_id"] = $array['user'];

            return $this->run($query, $prepare, "count");
        }

        public function findOwner($array) {
            $query = "SELECT * FROM `projects_data` WHERE `project_id` = :project_id AND `user_r_id` = :user_id";
            
            $prepare[":project_id"] = $array['project'];
            $prepare[":user_id"] = $array['user'];

            return $this->run($query, $prepare, "count");
        }

        function getList($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
            return $this->list("projects_data", $start, $limit, $order, $dir, false, $type);
        }

		function getSingle($value, $tag="project_id", $ref="ref") {
            return $this->getOneField("projects_data", $value, $ref, $tag);
		}

        function listOne($id, $tag="ref") {
            return $this->getOne("projects_data", $id, $tag);
        }
        
        public function modifyOne($tag, $value, $id, $ref="ref") {
            return $this->updateOne("projects_data", $tag, $value, $id,$ref);
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`projects_data` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `project_id` INT NOT NULL, 
                `user_id` INT NOT NULL, 
                `user_r_id` INT NOT NULL, 
                `data_field` TEXT NOT NULL, 
                `status` INT NOT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`projects_data`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`projects_data`";

            $this->query($query);
        }
    }
?>