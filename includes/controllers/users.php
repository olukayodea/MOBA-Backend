<?php
class users extends database {
    /*  create users
    */
    public function create($array) {
        if ($this->checkExixst("users", "email", $array['email']) < 1) {
            $data = $array;
            $categoryArray = array();
            $photo_file = $data['photo_file'];
            $id_file = $data['id_file'];
            $data['password'] = sha1($array['password']);
            if ($array['category'] != "") {
                $categoryArray = explode(",",$array['category']);
            }
            unset($data['category']);
            unset($data['photo_file']);
            unset($data['id_file']);
            unset($data['kin_name']);
            unset($data['kin_email']);
            unset($data['kin_phone']);
            unset($data['kin_relationship']);
            $create = $this->insert("users", $data);

            if ($create) {
                if (count($categoryArray) > 0) {
                    global $usersCategory;
                    $pushData['user_id'] = $create;
                    for ($i = 0; $i < count($categoryArray); $i++) {
                        $pushData['category_id'] = $categoryArray[$i];
                        $usersCategory->create($pushData);
                    }
                }
                if ($array['kin_name'] != "") {
                    global $usersKin;
                    $kin['user_id'] = $create;
                    $kin['kin_name'] = $array['kin_name'];
                    $kin['kin_email'] = $array['kin_email'];
                    $kin['kin_phone'] = $array['kin_phone'];
                    $kin['kin_relationship'] = $array['kin_relationship'];
                    $usersKin->create($kin);
                }

                if ($photo_file != "") {
                    $this->saveProfilePicture($create, $photo_file, "profile");
                }

                if ($id_file != "") {
                    $this->saveProfilePicture($create, $photo_file, "gov_id");
                }
                $client = $array['last_name']." ".$array['other_names'];
                $subjectToClient = "Welcome to MOBA";
                $contact = "MOBA <".replyMail.">";
                
                $fields = 'subject='.urlencode($subjectToClient).
                    '&last_name='.urlencode($array['last_name']).
                    '&other_names='.urlencode($array['other_names']).
                    '&email='.urlencode($array['email']).
                    '&password='.urlencode($array['password']);
                $mailUrl = URL."includes/views/emails/welcome.php?".$fields;
                $messageToClient = $this->curl_file_get_contents($mailUrl);
                
                $mail['from'] = $contact;
                $mail['to'] = $client." <".$array['email'].">";
                $mail['subject'] = $subjectToClient;
                $mail['body'] = $messageToClient;
                
                global $alerts;
                $alerts->sendEmail($mail);

                return $create;
            } else {
                return "error";
            }
        } else {
            return "duplicate data";
        }
    }

    public function validateAcc($email) {
        if ($this->checkExixst("users", "email", $email) == 1) {
            $data = $this->listOne($email, "email");
            $seed = rand(10000, 99999);
            $urlString = $seed."_".sha1($seed."_".$data['ref']."_".$seed)."_".(time()+(60*60*24))."_".$data['ref'];

            $url = URL."login?recover&token=".$urlString;
            $tag = "We noticed that you recently requested for a new password. please <a href='".$url."'>clcik</a> on the link below to complete the process<br><br>";
            $tag .= $url."<br><br>";
            $tag .= "This link expires in 24 hours. If you did not request this action, please ignore and delete this email";

            $client = $data['last_name']." ".$data['other_names'];
            $subjectToClient = "Password Modification Request";
            $contact = "MOBA <".replyMail.">";
            
            $fields = 'subject='.urlencode($subjectToClient).
                '&last_name='.urlencode($data['last_name']).
                '&other_names='.urlencode($data['other_names']).
                '&email='.urlencode($data['email']).
                '&tag='.urlencode(htmlentities($tag));
            $mailUrl = URL."includes/views/emails/notification.php?".$fields;
            $messageToClient = $this->curl_file_get_contents($mailUrl);
            
            $mail['from'] = $contact;
            $mail['to'] = $client." <".$data['email'].">";
            $mail['subject'] = $subjectToClient;
            $mail['body'] = $messageToClient;
            
            global $alerts;
            $alerts->sendEmail($mail);

            return true;
        } else {
            return false;
        }
    }

    public function changePassword($array) {
        if ($this->checkExixst("users", "ref", $array['ref']) == 1) {
            $data = $this->listOne($array['ref'], "ref");

            if ($this->modifyUser("password", sha1($array['password']), $data['ref'], "ref")) {
                $tag = "Your password was changed successfully. <a href='".URL."'>Sigin in</a> to your MOBA Account to learn more";

                $client = $data['last_name']." ".$data['other_names'];
                $subjectToClient = "Psssword Modification Update";
                $contact = "MOBA <".replyMail.">";
                
                $fields = 'subject='.urlencode($subjectToClient).
                    '&last_name='.urlencode($data['last_name']).
                    '&other_names='.urlencode($data['other_names']).
                    '&email='.urlencode($data['email']).
                    '&tag='.urlencode($tag);
                $mailUrl = URL."includes/views/emails/notification.php?".$fields;
                $messageToClient = $this->curl_file_get_contents($mailUrl);
                
                $mail['from'] = $contact;
                $mail['to'] = $client." <".$data['email'].">";
                $mail['subject'] = $subjectToClient;
                $mail['body'] = $messageToClient;
                
                global $alerts;
                $alerts->sendEmail($mail);

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*  login user and create user session
    */
    public function login($array) {
        $query = "SELECT * FROM `users` WHERE  (`email` = :email AND `password` = :password) AND `status` != 'DELETED'";
        $prepare[':email'] = $array['email'];
        $prepare[':password'] = sha1($array['password']);

        $login = $this->query($query, $prepare, "getRow");

        if ((is_array($login)) && ($login != false)) {
            unset($login['password']);
            $_SESSION['users'] = $login;
            $_SESSION['users']['loginTime'] = time();
            $_SESSION['users']['sessionTime'] = time() + 1800;
            return true;
        } else {
            return false;
        }
    }
    
    public function autoLogin($id, $ref) {
        $query = "SELECT * FROM `users` WHERE  `".$ref."` = :ref AND `status` != 'DELETED'";
        $prepare[':ref'] = $id;

        $login = $this->query($query, $prepare, "getRow");

        if ((is_array($login)) && ($login != false)) {
            unset($login['password']);
            $_SESSION['users'] = $login;
            $_SESSION['users']['loginTime'] = time();
            $_SESSION['users']['sessionTime'] = time() + 1800;
            return true;
        } else {
            return false;
        }
    }

    public function logout() {
        $_SESSION = array();
        if(isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy();
    }

    public function modify($array) {
        $ref = $array['ref'];
        unset($array['ref']);
        if ($array['photo_file'] != "") {
            unset($array['photo_file']);
        }
        if ($array['id_file'] != "") {
            unset($array['id_file']);
        }
        if ($array['category'] != "") {
            $categoryArray = explode(",",$array['category']);
            if (count($categoryArray) > 0) {
                $this->delete("usersCategory", $ref, "user_id");
                global $usersCategory;
                
                $pushData['user_id'] = $ref;
                for ($i = 0; $i < count($categoryArray); $i++) {
                    $pushData['category_id'] = $categoryArray[$i];
                    $usersCategory->create($pushData);
                }
            }
            unset($array['category']);
        }
        
        if ($array['kin_name'] != "") {
            global $usersKin;
            $kin['user_id'] = $ref;
            $kin['kin_name'] = $array['kin_name'];
            $kin['kin_email'] = $array['kin_email'];
            $kin['kin_phone'] = $array['kin_phone'];
            $kin['kin_relationship'] = $array['kin_relationship'];
            $usersKin->create($kin);
            unset($array['kin_name']);
            unset($array['kin_email']);
            unset($array['kin_phone']);
            unset($array['kin_relationship']);
        }
        return $this->update("users", $array, array("ref" => $ref));
    }

    public function modifyUser($tag, $value, $id, $ref) {
        return $this->updateOne("users", $tag, $value, $id,$ref);
    }

    function getList($start=false, $limit=false, $order="ref", $dir="ASC", $type="list") {
        return $this->lists("users", $start, $limit, $order, $dir, false, $type);
    }

    function listOne($id, $tag="ref") {
        return $this->getOne("users", $id, $tag);
    }

    function listOnValue($id, $reference) {
        return $this->getOneField("users", $id, "ref", $reference);
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false, $type="list") {
        return $this->sortAll("users", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit, $type);
    }

    function getProfileImage($id, $class='', $width='50') {
        $data = $this->listOne($id);
        $image_Url = $data["image_url"];
        $screen_name = $data["screen_name"];
        $login_type = $data["login_type"];

        if (trim($image_Url) != "") {
            if ($login_type == "local") { ?>
            <img class="<?php echo $class; ?> profile" src="<?php echo URL.$image_Url; ?>" alt="<?php echo $screen_name; ?>" height="<?php echo $width; ?>">
        <?php } else if ($login_type == "social_media") { ?>
            <img class="<?php echo $class; ?> profile" src="<?php echo $image_Url; ?>" alt="<?php echo $screen_name; ?>" height="<?php echo $width; ?>">
        <?php }
        } else { ?>
            <img class="<?php echo $class; ?> main_img profile" data-name="<?php echo $screen_name; ?>" alt="<?php echo $screen_name; ?>">
            <script src="<?php echo URL; ?>js/initial.js"></script>
            <script type="text/javascript">
            $(document).ready(function(){
                $('.main_img').initial({
                    width:<?php echo $width; ?>,
                    charCount: 1,
                    fontSize: <?php echo $width-10; ?>,
                    fontWeight: 400,
                    height:<?php echo $width; ?>
                });
            })
            </script>
        <?php }
    }

    function saveProfilePicture($id, $file, $api=false, $data=false) {
        global $media;
        if ($api == false) {
            $upload = $media->uploadDP($id, $file);
        } else {
            $upload = $media->uploadAPI($id, $file, $api);
        }

        if ($upload) {
            if ($upload['title'] == "OK") {
                if ($api == "gov_id") {
                    $t = "id_url";
                } else if ($api == "profile") {
                    $t = "image_url";
                } else {
                    $t = "image_url";
                }
                $this->modifyUser("image_url", $upload['desc'], $id, "ref");
            }

            if ($data) {
                $this->modifyUser("id_type", $data['id_type'], $id, "ref");
                $this->modifyUser("id_expiry", $data['id_expiry'], $id, "ref");
                $this->modifyUser("id_number", $data['id_number'], $id, "ref");
                $this->modifyUser("verified", 1, $id, "ref");
            }
            
            return $upload;
        } else {
            return false;
        }
    }

    function saveGovernmentId($id, $file) {
        global $media;
        $upload = $media->uploadDP($id, $file, false);

        if ($upload) {
            if ($upload['title'] == "OK") {
                $this->modifyUser("verified", 1, $id, "ref");
                $this->modifyUser("id_url", $upload['desc'], $id, "ref");
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function removeProfilePicture($id) {
        return $this->modifyUser("image_url", "", $id, "ref");
    }

    function toggleStatus($id) {
        $data = $this->listOne($id);
        if ($data['status'] == "ACTIVE") {
            $updateData = "INACTIVE";

            $tag = "There is a new modification on your account.<br>The following action was performed on your account: <strong>Account Deactivated </strong><br><br>Please contact us to contest this action";
        } else if ($data['status'] == "INACTIVE") {
            $updateData = "ACTIVE";
            $tag = "There is a new modification on your account.<br>The following action was performed on your account: <strong>Account Activated</strong>";
        }
        $tag .= ". <a href='".URL."'>Sigin in</a> to your MOBA Account to learn more";

        if ($this->modifyUser("status", $updateData, $data['ref'], "ref")) {
            //send email

            $client = $data['last_name']." ".$data['other_names'];
            $subjectToClient = "Account Modification Update";
            $contact = "MOBA <".replyMail.">";
            
            $fields = 'subject='.urlencode($subjectToClient).
                '&last_name='.urlencode($data['last_name']).
                '&other_names='.urlencode($data['other_names']).
                '&email='.urlencode($data['email']).
                '&tag='.urlencode($tag);
            $mailUrl = URL."includes/views/emails/notification.php?".$fields;
            $messageToClient = $this->curl_file_get_contents($mailUrl);
            
            $mail['from'] = $contact;
            $mail['to'] = $client." <".$data['email'].">";
            $mail['subject'] = $subjectToClient;
            $mail['body'] = $messageToClient;
            
            global $alerts;
            $alerts->sendEmail($mail);
        }
        return true;
    }

    function verify($id, $status) {
        $data = $this->listOne($id);
        if ($status == 1) {
            $updateData = 2;
            $tag = "There is a new modification on your account.<br>The following action was performed on your account: <strong>Account verification completed</strong>";
        } else {
            $updateData = 0;
            $tag = "There is a new modification on your account.<br>The following action was performed on your account: <strong>Account verification was refused</strong>";
        }
        $tag .= ". <a href='".URL."'>Sigin in</a> to your MOBA Account to learn more";
        if ($this->modifyUser("verified", $updateData, $data['ref'], "ref")) {
            //send email

            $client = $data['last_name']." ".$data['other_names'];
            $subjectToClient = "Account Verification Update";
            $contact = "MOBA <".replyMail.">";
            
            $fields = 'subject='.urlencode($subjectToClient).
                '&last_name='.urlencode($data['last_name']).
                '&other_names='.urlencode($data['other_names']).
                '&email='.urlencode($data['email']).
                '&tag='.urlencode($tag);
            $mailUrl = URL."includes/views/emails/notification.php?".$fields;
            $messageToClient = $this->curl_file_get_contents($mailUrl);
            
            $mail['from'] = $contact;
            $mail['to'] = $client." <".$data['email'].">";
            $mail['subject'] = $subjectToClient;
            $mail['body'] = $messageToClient;
            
            global $alerts;
            $alerts->sendEmail($mail);
            return true;
        } else {
            return false;
        }
    }

    function toggleAdmin($id) {
        $data = $this->listOne($id);
        if ($data['user_type'] == 1) {
            $updateData = 0;
            $tag = "There is a new modification on your account.<br>The following action was performed on your account: <strong>Administrator Status Revoked</strong>";
        } else {
            $updateData = 1;
            $tag = "There is a new modification on your account.<br>The following action was performed on your account: <strong>Administrator Status Authorized</strong><br><br>Please logout of your account and log back in to notice this update on your account";
        }
        $tag = ". <a href='".URL."'>Sigin in</a> to your MOBA Account to learn more";
        if ($this->modifyUser("user_type", $updateData, $data['ref'], "ref")) {
            //send email

            $client = $data['last_name']." ".$data['other_names'];
            $subjectToClient = "Account Modification Update";
            $contact = "MOBA <".replyMail.">";
            
            $fields = 'subject='.urlencode($subjectToClient).
                '&last_name='.urlencode($data['last_name']).
                '&other_names='.urlencode($data['other_names']).
                '&email='.urlencode($data['email']).
                '&tag='.urlencode($tag);
            $mailUrl = URL."includes/views/emails/notification.php?".$fields;
            $messageToClient = $this->curl_file_get_contents($mailUrl);
            
            $mail['from'] = $contact;
            $mail['to'] = $client." <".$data['email'].">";
            $mail['subject'] = $subjectToClient;
            $mail['body'] = $messageToClient;
            
            global $alerts;
            $alerts->sendEmail($mail);
            return true;
        } else {
            return false;
        }
    }
    
    function usersMailSearch($val, $mobile=false) {
        global $search;
        $data = $search->usersMailSearch($val, $mobile);
        
        $array = array();
        for ($i = 0; $i < count($data); $i++) {
            $array[$data[$i]['ref']] = $data[$i]['last_name']." ".$data[$i]['other_names']." (".$data[$i]['screen_name'].")";
        }

        return $array;
    }
    
    private function formatResult($data, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->clean($data[$i], $single);
            }
        } else {
            $data = $this->clean($data, $single);
        }
        return $data;
    }

    private function clean($data) {
        unset($data['user_type']);
        unset($data['password']);
        unset($data['account_type_token']);
        unset($data['token']);
        if ($data['badge'] == 1) {
            $data['verified'] = "true";
        } else {
            $data['verified'] = "false";
        }
        if ($data['screen_name_cam_change'] == 1) {
            $data['screen_name_cam_change'] = "no_change";
        } else {
            $data['screen_name_cam_change'] = "change";
        }

        if ($data['image_url'] == "") {
            $data['image_url'] = "https://ui-avatars.com/api/?name=".urlencode($data['screen_name']);
        } else if (($data['image_url'] != "") && ($data['login_type'] == "local")) {
            $data['image_url'] = URL.$data['image_url'];
        }
        unset($data['badge']);
        unset($data['login_type']);
        return $data;
    }

    public function apiGetList($type, $user, $ref=false, $page=1, $location=false) {
        global $wallet;
        global $bank_account;
        global $usersKin;
        if (intval($page) == 0) {
            $page = 1;
        }
        if ($type == "contact") {
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['data'] = $this->usersMailSearch($ref, $user);
        } else if ($type == "getOne") {
            $return['status'] = "200";
            $return['message'] = "OK";
            $return['data'] = $this->formatResult( $this->listOne($user), true );
            $kin = $usersKin->listOne($user);
            if ($kin) {
                $return['data']['next_of_kin'] = $kin;
            }
            $return['wallet'] = $wallet->apiGetWalletList("balance", $location['ref'], $user)['data'];
            $return['bank_accounts'] =  $bank_account->listAllUserData($user, 0, 20)['list'];
            $return['bank_cards'] = $wallet->listAllUserData($user, 0, 20)['list'];
        }
        return $return;
    }
    
    function createUnique($username) {
        $num = $username.rand(1000, 9999);
        return $num;
    }
    
    function confirmUnique($key) {
        if ($this->checkExixst("users", "screen_name", $key) == 0) {
            return $key;
        } else {
            return $this->confirmUnique($this->createUnique($key));
        }
    }

    public function initialize_table() {
        //create database
        $query = "CREATE TABLE IF NOT EXISTS `".dbname."`.`users` (
            `ref` INT NOT NULL AUTO_INCREMENT, 
            `user_type` INT NOT NULL, 
            `last_name` VARCHAR(50) NOT NULL, 
            `other_names` VARCHAR(50) NOT NULL, 
            `screen_name` VARCHAR(50) NOT NULL, 
            `screen_name_cam_change` INT NOT NULL, 
            `email` VARCHAR(255) NOT NULL, 
            `password` VARCHAR(1000) NOT NULL,
            `mobile_number` VARCHAR(20) NOT NULL, 
            `street` VARCHAR(255) NOT NULL, 
            `city` VARCHAR(255) NOT NULL, 
            `state` VARCHAR(255) NOT NULL, 
            `country` VARCHAR(255) NOT NULL, 
            `about_me` VARCHAR(255) NOT NULL, 
            `image_url` VARCHAR(255) NULL, 
            `id_url` VARCHAR(255) NULL, 
            `average_response_time` VARCHAR(255) NULL, 
            `id_type` INT NOT NULL,
            `id_expiry` VARCHAR(50) NULL,
            `id_number` VARCHAR(50) NULL,
            `verified` INT NOT NULL, 
            `activation_token` VARCHAR(10) NOT NULL,
            `account_type_token` VARCHAR(255) NOT NULL,
            `firebase_token` VARCHAR(255) NOT NULL,
            `account_type` varchar(20) NOT NULL DEFAULT 'local',
            `token` VARCHAR(50) NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'NEW',
            `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ref`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;";

        $this->query($query);
    }

    public function clear_table() {
        //clear database
        $query = "TRUNCATE `".dbname."`.`users`";

        $this->query($query);
    }

    public function delete_table() {
        //clear database
        $query = "DROP TABLE `".dbname."`.`users`";

        $this->query($query);
    }
}
include_once("usersCategory.php");
include_once("usersTrack.php");
include_once("usersKin.php");
$usersCategory  = new usersCategory;
$usersTrack     = new usersTrack;
$usersKin       = new usersKin;
?>