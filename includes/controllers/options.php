<?php
	class options extends database {
		function add($name, $value) {
            $data = array();
            $replace = array();
            $data['name'] = $name;
            $data['value'] = $value;

            $replace[] = "value";
			return $this->replace("options", $data, $replace);
		}
		
		function remove($name) {
            return $this->delete("options", $name, "name");
		}
		
		function get($name) {
            return $this->getOneField("options", $name, "name", "value");
		}

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`options` (
                `ref` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL, 
                `value` TEXT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`),
                UNIQUE KEY `name` (`name`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`options`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`options`";

            $this->query($query);
        }
	}
?>