<?php
    class media extends database {

        /*  create users
        */
        public function create($files, $array, $api=false) {
            $folderPath = "media/".$this->hashDir($array)."/";
            foreach($files as $raw) {
                if ($api == false ) {
                    $uploadFile = $this->uploadFile($raw, $folderPath);
                    if ($uploadFile['title'] == "OK") {
                        $data['user_id'] = $array['user_id'];
                        $data['post_id'] = $array['post_id'];
                        $data['media_type'] = $uploadFile['type'];
                        $data['media_url'] = $folderPath.$uploadFile['desc'];
                        $this->insert("media", $data);
                    }
                } else {
                    $uploadFile = $this->uploadProjectApi($raw, $folderPath);
                    if ($uploadFile['title'] == "OK") {
                        $data['user_id'] = $array['user_id'];
                        $data['post_id'] = $array['post_id'];
                        $data['media_type'] = $uploadFile['type'];
                        $data['media_url'] = $folderPath.$uploadFile['desc'];

                       $this->insert("media", $data);
                    }
                }
            }
        }

        public function remove($id, $ref="post_id") {
            $data = $this->getOne("media", $id, $ref);
            $dirArray['user_id'] = $data['user_id'];
            $dirArray['post_id'] = $data['post_id'];
            unset($data);
            $remove = $this->delete("media", $id, $ref);
            $remove = true;
            if ($remove){
                $this->deleteDir($this->cleanUrl()."/media/request/".$id."/");
                return true;
            } else {
                return false;
            }
        }

        public function removeOne($id, $api=false) {
            $data = $this->getOne("media", $id);
            $remove = $this->delete("media", $id, "ref");
            if ($api == true) {
                $i = "../";
            } else {
                $i = $this->cleanUrl();
            }
            if ($remove){
                @unlink($i."/".$data['media_url']);
                return true;
            } else {
                return false;
            }
        }

        private function deleteDir($dirPath) {
            if (! is_dir($dirPath)) {
            }
            if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                $dirPath .= '/';
            }
            $files = @glob($dirPath . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $this->deleteDir($file);
                } else {
                    @unlink($file);
                }
            }
            rmdir($dirPath);
        }

        private function cleanUrl() {
            $string = explode("/admin", getcwd());

            return $string[0];
        }

        public function getCover($id, $sort='post_id') {
            $data = $this->getOne("media", $id, $sort);
            if ($data['media_url'] != "") {
                return URL.$data['media_url'];
            } else {
                return URL."img/no_image.png"; 
            }
        }

        function mediaDefault() {
            return URL."img/no_image.png"; 
        }

        public function getAlbum($id) {
            return $this->sortAll("media", $id, "post_id");
        }

        function reArrayFiles($file_post) {
            $file_ary = array();
            $file_count = count($file_post['name']);
            $file_keys = array_keys($file_post);
        
            for ($i=0; $i<$file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $file_post[$key][$i];
                }
            }
            return $file_ary;
        }

        function hashDir($array) {
            return sha1($array['user_id']."_".$array['post_id']);
        }
		
		function uploadFile($array, $userDoc) {
			ini_set("memory_limit", "200000000");
			$uploadedfile = $array['tmp_name'];
			$msg = array();
			if ($array["error"] == 1) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file exceeds the mazimum upload file limit";
			} else if ($array["error"] == 2 ) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file exceeds the mazimum upload file limit";
			} else if ($array["error"] == 3) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file was only partially uploaded, please re-upload file";
			} else if ($array["error"] == 4) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Missing file, please check the uploaded file and try again";
			} else if ($array["error"] == 6) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Missing a temporary folder, contact the website administrator";
			} else if ($array["error"] == 7) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Failed to write file to disk, contact the administrator";
			} else if ($array["error"] == 0) {
				$media_file = stripslashes($array['name']);
				$uploadedfile = $array['tmp_name']; 
				$extension = $this->getExtension($media_file);
				$extension = strtolower($extension);
				
				if($array['size'] < 2097152) {					
					$file = rand(10, 99).time().rand(100, 999).".".$extension;
					
					if(!is_dir($userDoc)) {
						mkdir($userDoc, 0777, true);
					}
					
					$newFile = $userDoc.$file;
					$move = move_uploaded_file($uploadedfile, $newFile);
					
					if ($move) {
						$msg['title'] = "OK";
                        $msg['desc'] = $file;
                        $msg['type'] = $array['type'];
					} else {
						$msg['title'] = "ERROR";
						$msg['desc'] = "an error occured";
					}
				} else {
					$msg['title'] = "ERROR";
					$msg['desc'] = "the file exceed 2MB is not allowed";
				}
			}
			return $msg;
        }
        
        public function uploadDP($ref, $array, $pic=true) {
			ini_set("memory_limit", "200000000");
			$uploadedfile = $array['tmp_name'];
			if ($array["error"] == 1) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file exceeds the mazimum upload file limit";
			} else if ($array["error"] == 2 ) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file exceeds the mazimum upload file limit";
			} else if ($array["error"] == 3) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file was only partially uploaded, please re-upload file";
			} else if ($array["error"] == 4) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Missing file, please check the uploaded file and try again";
			} else if ($array["error"] == 6) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Missing a temporary folder, contact the website administrator";
			} else if ($array["error"] == 7) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Failed to write file to disk, contact the administrator";
			} else if ($array["error"] == 0) {
				$media_file = stripslashes($array['name']);
				$uploadedfile = $array['tmp_name']; 
				$extension = $this->getExtension($media_file);
				$extension = strtolower($extension);
				
				if($array['size'] < 2097152) {			
                    if ($pic === true) {
                        $file = sha1($ref).".".$extension;
                    } else {
                        $file = "gov_id.".$extension;
                    }
                    $dir = "media/profiles/".$ref."/";
					$userDoc = $dir;
					if(!is_dir($userDoc)) {
						mkdir($userDoc, 0777, true);
					}
					$newFile = $userDoc.$file;
					$move = move_uploaded_file($uploadedfile, $newFile);
					
					if ($move) {
						$msg['title'] = "OK";
                        $msg['desc'] = $dir.$file;
					} else {
						$msg['title'] = "ERROR";
						$msg['desc'] = "an error occured";
					}
				} else {
					$msg['title'] = "ERROR";
					$msg['desc'] = "the file exceed 2MB is not allowed";
				}
            }
			return $msg;
        }
        
        public function uploadIcon($ref, $array) {
			ini_set("memory_limit", "200000000");
			$uploadedfile = $array['tmp_name'];
			if ($array["error"] == 1) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file exceeds the mazimum upload file limit";
			} else if ($array["error"] == 2 ) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file exceeds the mazimum upload file limit";
			} else if ($array["error"] == 3) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "The uploaded file was only partially uploaded, please re-upload file";
			} else if ($array["error"] == 4) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Missing file, please check the uploaded file and try again";
			} else if ($array["error"] == 6) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Missing a temporary folder, contact the website administrator";
			} else if ($array["error"] == 7) {
				$msg['title'] = "ERROR";
				$msg['desc'] = "Failed to write file to disk, contact the administrator";
			} else if ($array["error"] == 0) {
				$media_file = stripslashes($array['name']);
				$uploadedfile = $array['tmp_name']; 
				$extension = $this->getExtension($media_file);
				$extension = strtolower($extension);
				
				if($array['size'] < 524288) {
                    $file = $ref.".".$extension;
                    $dir = "media/categories/";
					$userDoc = "../".$dir;
					if(!is_dir($userDoc)) {
						mkdir($userDoc, 0777, true);
					}
					$newFile = $userDoc.$file;
					$move = move_uploaded_file($uploadedfile, $newFile);
					
					if ($move) {
						$msg['title'] = "OK";
                        $msg['desc'] = $dir.$file;
					} else {
						$msg['title'] = "ERROR";
						$msg['desc'] = "an error occured";
					}
				} else {
					$msg['title'] = "ERROR";
					$msg['desc'] = "the file exceed 2MB is not allowed";
				}
            }
			return $msg;
        }

        private function uploadProjectApi($data, $dir) {
            $size = (int) (strlen(rtrim($data, '=')) * 3 / 4);
            if($size < 2097152) {	
                $file = rand(10, 99).time().rand(100, 999).".png";
                $userDoc = "../".$dir;
                if(!is_dir($userDoc)) {
                    mkdir($userDoc, 0777, true);
                }
                $newFile = $userDoc.$file;
                $ifp = fopen( $newFile, 'wb' );
                fwrite( $ifp, base64_decode( $data ) );
                fclose( $ifp );
                
                if(!file_exists($newFile)){
                    touch($newFile);
                    chmod($newFile, 0777);
                }
                
                $msg['title'] = "OK";
                $msg['desc'] = $file;
                $msg['type'] = "image/png";
            } else {
                $msg['title'] = "ERROR";
                $msg['desc'] = "the file exceed 2MB is not allowed";
            }

            return $msg;
        }

        public function uploadAPI($ref, $data, $type="profile") {
            $size = (int) (strlen(rtrim($data, '=')) * 3 / 4);
            if($size < 2097152) {	
                if ($type == "profile") {
                    $file = sha1($ref).".png";
                    $dir = "media/profiles/".$ref."/";
                    $userDoc = "../".$dir;
                } else if ($type == "gov_id") {
                    $file = "gov_id.png";
                    $dir = "media/profiles/".$ref."/";
                    $userDoc = "../".$dir;
                } else if ($type == "request") {
                    $file = time().rand(10,99).".png";
                    $dir = "media/request/".$ref."/";
                    $userDoc = "../".$dir;
                }
                if(!is_dir($userDoc)) {
                    mkdir($userDoc, 0777, true);
                }
                $newFile = $userDoc.$file;
                $ifp = fopen( $newFile, 'wb' );
                fwrite( $ifp, base64_decode( $data ) );
                fclose( $ifp );
                if(!file_exists($newFile)){
                    touch($newFile);
                    chmod($newFile, 0777);
                }
                
                $msg['title'] = "OK";
                $msg['desc'] = $dir.$file;
            } else {
                $msg['title'] = "ERROR";
                $msg['desc'] = "the file exceed 2MB is not allowed";
            }
            return $msg;
        }
        
        public function removeAPI($array) {
            $data = $this->getOne("media", $array['image_id'], "ref");

            if ($array['user_id'] = $data['user_id']) {
                if ($this->removeOne($data['ref'], true)) {
                    $return['status'] = "200";
                    $return['message'] = "OK";
                } else {
                    $return['status'] = "500";
                    $return['message'] = "Internal Server Error";
                }
            } else {
                $return['status'] = "501";
                $return['message'] = "Not Implemented";
                $return['additional_message'] = "You can not delet this media item at this time";
            }

            return $return;
        }

        public function initialize_table() {
            //create database
            $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`media` (
                `ref` INT NOT NULL AUTO_INCREMENT, 
                `user_id` INT NOT NULL, 
                `post_id` INT NOT NULL, 
                `media_type` VARCHAR(50) NOT NULL, 
                `media_url` VARCHAR(1000) NOT NULL, 
                `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`ref`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

            $this->query($query);
        }

        public function clear_table() {
            //clear database
            $query = "TRUNCATE `".dbname."`.`media`";

            $this->query($query);
        }

        public function delete_table() {
            //clear database
            $query = "DROP TABLE `".dbname."`.`media`";

            $this->query($query);
        }
    }
?>