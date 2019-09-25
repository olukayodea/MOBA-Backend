<?php
    class project_save extends database {
        /*  create users
        */
        public function create($array) {
            $create = $this->insert("project_save", $array);
            if ($create) {
                return $create;
            } else {
                return false;
            }
        }

        function remove($id) {
            $this->delete("project_save", $id);
            return true;
        }

        function manage($array) {
            if ($array['action'] == "add") {
                $add['project_id'] = $array['id'];
                $add['user_id'] = $array['ref'];

                if ($this->create($add)) {
                    return '<div id="save_icon" data-action="rem" data-ref="'. $array['ref'].'" data-id="'.$array['id'].'"><i class="fas fa-save bluecolor"></i> Remove From Saved List</div>';
                } else {
                    return '<div id="save_icon" data-action="add" data-ref="'. $array['ref'].'" data-id="'.$array['id'].'"><i class="far fa-save"></i> Add to Saved List</div>';
                }
            } else if ($array['action'] == "rem") {
                $data = $this->getSortedList($array['id'], "project_id", "user_id", $array['ref'], false, false, "ref", "ASC", "AND", false, false, "getRow");

                if ($this->remove($data['ref'])) {
                    return '<div id="save_icon" data-action="add" data-ref="'. $array['ref'].'" data-id="'.$array['id'].'"><i class="far fa-save"></i> Add to Saved List</div>';
                } else {
                    return '<div id="save_icon" data-action="rem" data-ref="'. $array['ref'].'" data-id="'.$array['id'].'"><i class="fas fa-save bluecolor"></i> Remove From Saved List</div>';
                }
            }
        }

        public function getStatus($id) {
            $ref = $_SESSION['users']['ref'];
            if ($ref > 0) {
                if ($this->getSortedList($id, "project_id", "user_id", $ref, false, false, "ref", "ASC", "AND", false, false, "count") == 0) { ?>
                    <div id="save_icon" data-action="add" data-ref="<?php echo $ref; ?>" data-id="<?php echo $id; ?>"><i class="far fa-save"></i> Add to Saved List</div>
                <?php } else { ?>
                    <div id="save_icon" data-action="rem" data-ref="<?php echo $ref; ?>" data-id="<?php echo $id; ?>"><i class="fas fa-save bluecolor"></i> Remove From Saved List</div>
                <?php }
            }
        }

        public function updateOneRow($tag, $value, $id) {
            return $this->updateOne("project_save", $tag, $value, $id, "ref");
        }

        function getSingle($name, $tag="project_id", $ref="ref") {
            return $this->getOneField("project_save", $name, $ref, $tag);
		}

        function listOne($id) {
            return $this->getOne("project_save", $id, "ref");
        }

        function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'project_id', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
            return $this->sortAll("project_save", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`project_save` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `project_id` INT NOT NULL, 
                `user_id` INT NOT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`project_save`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`project_save`";

            $this->query($query);
        }
    }
?>