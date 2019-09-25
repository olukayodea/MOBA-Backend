<?php
    class userHome extends common {
        public function navigationBarNotification($redirect) { ?>
            <a href="<?php echo URL.$redirect; ?>">Notifications</a> | <a href="<?php echo URL."/inbox"; ?>">Messages</a> | <a href="<?php echo URL.$redirect."ads/saved"; ?>">Saved Ads</a>
        <?php }

        public function pageContent($redirect, $id=false, $type=false) {
            if ($redirect == "index") {
                $this->promoted();
                $this->aroundMe();
                $this->recentlyPosted();
            } else if ($redirect == "jobs") {
                $this->promoted($id);
                $this->categoryList($id);
            } else if ($redirect == "search") {
                $this->search($id, $type);
            } else if ($redirect == "publicProfile") {
                $this->publicProfile($id);
            } else if ($redirect == "profile") {
                $this->profileHome();
            } else if ($redirect == "updateProfile") {
                $this->editPage($type);
            } else if ($redirect == "notifications") {
                $this->listNotification($type);
            }
        }

        public function editPage($type) {
            if ($type == "img") {
                $this->editImagePage();
            } else if ($type == "IDs") {
                $this->editIdPage();
            } else if ($type == "screenname") {
                $this->editScreenname();
            } else if ($type == "profile") {
                $this->editProfilePage();
            } else if ($type == "screenname") {
                $this->editPasswordPage();
            } else if ($type == "Password") {
                $this->editPasswordPage();
            } else {
                $this->profileHome();
            }
        }

        private function editImagePage() {
            global $users;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>

            <div class="row">
                <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <?php $users->getProfileImage($ref, "card-img-top", "50"); ?>
                    <?php if ($data['image_url'] != "") { ?>
                        <div class="card-body">
                            <a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')" class="card-link">Remove Display Image</a>
                        </div>
                    <?php } ?>
                    <div class="card-footer">
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn btn-primary">Done Editing</button>
                    </div>
                </div>
                <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <div class="card-body">
                        <h5 class="card-title">Modify Display Picture</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Drag and Drop a file or select one<br>
                        Click on the Upload Button to upload the image<br>
                        Or remoe the selected file by clicking on the 'Remove' or 'cancel' Button</h6>
                        <form enctype="multipart/form-data" method="post">
                            <div class="form-group">
                                <input id="file-3" type="file">
                            </div>
                        </form>
                    </div>
                </div>
            </div> 
            <script src="<?php echo URL; ?>js/fileinput.min.js" type="text/javascript"></script>
            <script src="<?php echo URL; ?>js/fileinput.theme.min.js" type="text/javascript"></script>
            <script type="text/javascript">
                var user_id = <?php echo $data['ref']; ?>;
                $("#file-3").fileinput({
                    theme: "fa",
                    showCaption: false,
                    browseClass: "btn btn-secondary",
                    fileType: "any",
                    uploadUrl: '<?php echo URL; ?>/includes/views/scripts/imageUpload.php',
                    uploadExtraData: {id: user_id},
                    allowedFileExtensions: ['jpg', 'png', 'gif'],
                    maxFileSize: 2048,
                    required: true
                });
                $('#file-3').on('fileuploaded', function() {
                    location.reload(true);
                });

            </script>
            <?php
        }
        
        private function editIdPage() {
            global $users;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>
            <div class="row">
                <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <?php $users->getProfileImage($ref, "card-img-top", "50"); ?>
                    <div class="card-footer">
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn btn-primary">Done Editing</button>
                    </div>
                </div>
                <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <div class="card-body">
                        <h5 class="card-title">Upload Government ID</h5>
                        <?php if ($data['verified'] == 2) { ?>
                            <p>Your account has been verified</p>
                        <?php } else if ($data['verified'] == 1) { ?>
                            <p>Your uploaded ID is currently being verified your account will be fully active once verified</p>
                        <?php } else { ?>
                        <h6 class="card-subtitle mb-2 text-muted">Drag and Drop a file or select one<br>
                        Click on the Upload Button to upload the image<br>
                        Or remoe the selected file by clicking on the 'Remove' or 'cancel' Button</h6>
                        <form enctype="multipart/form-data" method="post">
                            <div class="form-group">
                                <input id="file-3" type="file" accept="png, .jpg, .jpeg, .pdf">
                            </div>
                        </form>
                        <?php } ?>
                    </div>
                </div>
            </div> 
            <script src="<?php echo URL; ?>js/fileinput.min.js" type="text/javascript"></script>
            <script src="<?php echo URL; ?>js/fileinput.theme.min.js" type="text/javascript"></script>
            <script type="text/javascript">
                var user_id = <?php echo $data['ref']; ?>;
                $("#file-3").fileinput({
                    theme: "fa",
                    showCaption: false,
                    browseClass: "btn btn-secondary",
                    fileType: "any",
                    uploadUrl: '<?php echo URL; ?>/includes/views/scripts/idUpload.php',
                    uploadExtraData: {id: user_id},
                    allowedFileExtensions: ['jpg', 'png', 'pdf'],
                    maxFileSize: 2048,
                    required: true
                });
                $('#file-3').on('fileuploaded', function() {
                    location.reload(true);
                });

            </script>
            <?php
        }

        private function editScreenname() {
            global $users;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>
            <div class="row">
                <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <?php $users->getProfileImage($ref, "card-img-top", "50"); ?>
                    <div class="card-footer">
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn btn-primary">Done Editing</button>
                    </div>
                </div>
                <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <div class="card-body">
                        <h5 class="card-title">Update Username</h5>

                        <form enctype="multipart/form-data" method="post">
                            <div class="form-group row">
                                <label for="screen_name" class="col-sm-2 col-form-label">Username</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="screen_name" name="screen_name" value="<?php echo $data['screen_name']; ?>" aria-describedby="usernameHelp" required placeholder="Enter a new username">
                                </div>
                                <small id="usernameHelp">You can only change your username once.</small>
                            </div>
                            <?php if ($data['screen_name_cam_change'] == 0) { ?>
                                <input type="hidden" name="ref" value="<?php echo $data['ref']; ?>">
                                <button type="submit" name="updateUsername" disabled class="btn btn-primary">Update Username</button>
                            <?php } else { ?>
                                <p class="vendor">You can only modify your username once</p>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div> 
            <script type="text/javascript">
                $( "#screen_name" ).on('keyup keypress blur change', function() {
                    var val = $( "#screen_name" ).val();
                    $.post("<?php echo URL."includes/views/scripts/getUsername"; ?>", {username: val}, function(result){
                        console.log(result);
                        var obj = result;
                        if (obj.status == "OK") {
                            $("#usernameHelp").css('color', 'green');
                            $('button[type=submit]').prop( "disabled", false );
                        } else {
                            $("#usernameHelp").css('color', 'red');
                            $('button[type=submit]').prop( "disabled", true );
                        }
                        console.log(obj.status);
                        console.log(obj.message);
                        $("#usernameHelp").html(obj.message);
                    });
                });
            </script>
            <?php
        }

        private function editProfilePage() {
            global $users;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>
            <div class="row">
                <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <?php $users->getProfileImage($ref, "card-img-top", "50"); ?>
                    <?php if ($data['image_url'] != "") { ?>
                        <div class="card-body">
                            <a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')" class="card-link">Remove Display Image</a>
                        </div>
                    <?php } ?>
                    <div class="card-footer">
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn btn-primary">Done Editing</button>
                    </div>
                </div>
                <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <div class="card-body">
                        <h5 class="card-title">Update Profile</h5>

                        <form enctype="multipart/form-data" method="post">
                            <div class="form-group row">
                                <label for="other_names" class="col-sm-2 col-form-label">First Name</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="other_names" name="other_names" value="<?php echo $data['other_names']; ?>" required placeholder="Enter FIrst name and any other names">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="last_name" class="col-sm-2 col-form-label">Last Name</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $data['last_name']; ?>" required placeholder="Enter Last name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="screen_name" class="col-sm-2 col-form-label">Screen Name</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="screen_name" name="screen_name" value="<?php echo $data['screen_name']; ?>" required placeholder="Enter Screen name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-sm-2 col-form-label">Email Name</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="email" name="email" disabled value="<?php echo $data['email']; ?>">
                                </div>
                            </div>
                            <input type="hidden" name="ref" value="<?php echo $data['ref']; ?>">
                            <button type="submit" name="updateProfile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div> 
            <?php
        }

        private function editPasswordPage() {
            global $users;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>
            <div class="row">
                <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <?php $users->getProfileImage($ref, "card-img-top", "50"); ?>
                    <?php if ($data['image_url'] != "") { ?>
                        <div class="card-body">
                            <a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')" class="card-link">Remove Display Image</a>
                        </div>
                    <?php } ?>
                    <div class="card-footer">
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn btn-primary">Done Editing</button>
                    </div>
                </div>
                <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <div class="card-body">
                        <h5 class="card-title">Update Password</h5>

                        <form enctype="multipart/form-data" method="post">
                            <div class="form-group row">
                                <label for="old_password" class="col-sm-2 col-form-label">Old Password</label>
                                <div class="col-sm-10"><span id="sprytextfield1">
                                  <input type="password" class="form-control" id="old_password" name="old_password" value="" placeholder="Enter current Password" required />
                                <span class="textfieldRequiredMsg">A value is required.</span></span></div>
                            </div>
                            <div class="form-group row">
                                <label for="new_password" class="col-sm-2 col-form-label">Last New Password</label>
                                <div class="col-sm-10"><span id="sprypassword1">
                                <input type="password" class="form-control" id="new_password" name="new_password" value="" placeholder="Enter new Password" required />
                                <span class="passwordRequiredMsg">A value is required.</span><span class="passwordInvalidStrengthMsg">The password doesn't meet the specified strength.</span></span></div>
                            </div>
                            <div class="form-group row">
                                <label for="confirm_new" class="col-sm-2 col-form-label">Confirm New Password</label>
                                <div class="col-sm-10"><span id="spryconfirm1">
                                  <input type="password" class="form-control" id="confirm_new" name="confirm_new" value="" placeholder="Confirm New Password" required />
                                <span class="confirmRequiredMsg">A value is required.</span><span class="confirmInvalidMsg">The values don't match.</span></span>
                                <div id="pswd_info">
                                    Password requirements:
                                    <ul>
                                        <li id="letter" class="invalid">At least <strong>one letter</strong></li>
                                        <li id="capital" class="invalid">At least <strong>one capital letter</strong></li>
                                        <li id="number" class="invalid">At least <strong>one number</strong></li>
                                        <li id="length" class="invalid">Be at least <strong>6 characters</strong></li>
                                    </ul>
                                </div>
                            </div>
                            <input type="hidden" name="ref" value="<?php echo $data['ref']; ?>">
                            <button type="submit" name="updatePassword" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div> 
            
<script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprypassword1 = new Spry.Widget.ValidationPassword("sprypassword1", {minAlphaChars:1, minNumbers:1, minUpperAlphaChars:1, validateOn:["change"]});
var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryconfirm1", "new_password");

$(document).ready(function() {
  $('#pswd_info').css('display', 'none');
	$('#new_password').keyup(function() {
    // keyup code here
    var pswd = $(this).val();
    var c_lenght = false;
    var c_char = false;
    var c_upper = false;
    var c_number = false;
    //validate the length
    if ( pswd.length < 6 ) {
      $('#length').removeClass('valid').addClass('invalid');
      c_lenght = false;
    } else {
      $('#length').removeClass('invalid').addClass('valid');
      c_lenght = true;
    }

    //validate letter
    if ( pswd.match(/[A-Za-z]/) ) {
      $('#letter').removeClass('invalid').addClass('valid');
      c_char = true;
    } else {
      $('#letter').removeClass('valid').addClass('invalid');
      c_char = false;
    }

    //validate capital letter
    if ( pswd.match(/[A-Z]/) ) {
      $('#capital').removeClass('invalid').addClass('valid');
      c_upper = true;
    } else {
      $('#capital').removeClass('valid').addClass('invalid');
      c_upper = false;
    }

    //validate number
    if ( pswd.match(/\d/) ) {
      $('#number').removeClass('invalid').addClass('valid');
      c_number = true;
    } else {
      $('#number').removeClass('valid').addClass('invalid');
      c_number = false;
    }

    if ((c_number == true) && (c_upper == true) && (c_char == true) && (c_lenght == true)) {
      $('input[type=submit]').prop( "disabled", false );
    } else {
      $('input[type=submit]').prop( "disabled", true );
    }

	}).focus(function() {
		$('#pswd_info').show();
	}).blur(function() {
		$('#pswd_info').hide();
	});
});
        </script>
            <?php
            
        }

        public function updateProfile($array) {
            global $users;
            return $users->modify($array);
        }

        public function updateScreenName($array) {
            global $users;
            if ($users->modifyUser("screen_name", $_POST['screen_name'], $_POST['ref'], "ref")) {
                return $users->modifyUser("screen_name_cam_change", 1, $_POST['ref'], "ref");
            }
        }

        public function updatePassword($array) {
            global $users;
            $getOldPassword = $users->listOnValue($array['ref'], "password");
            $oldPassword = sha1($array['old_password']);
            $newArray['password'] = $array['new_password'];
            $newArray['ref'] = $array['ref'];
            if ($getOldPassword == $oldPassword) {
                return $users->changePassword($newArray);
            } else {
                return "invalid";
            }
            
        }

        public function search($s, $type) {
            $starttime = microtime();
            global $search;
            global $media;
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("search_per_page");
            $start = $page*$limit;

            if ($type == "category") {
                $catData = $search->catSearchData($_SESSION['location'], $s, $start, $limit);
                $data = $catData['data'];
                $dataCount = $catData['count'];
            } else if ($type == "keyword") {
                $keywordData = $search->keywordSearchData($_SESSION['location'], $s, $start, $limit);
                $data = $keywordData['data'];
                $dataCount = $keywordData['count'];
            } else {
                $jobData = $search->jobSearchData($_SESSION['location'], $s, $start, $limit);
                $data = $jobData['data'];
                $dataCount = $jobData['count'];
            }
            $endtime = microtime(); ?>
            <h4>Search Result for '<?php echo $s; ?>'</h4>
            <p>Showing <?php echo $dataCount; ?> <?php echo $this->addS("result", count($data)); ?> in <?php echo round($endtime-$starttime, 6)."s"; ?></p>
    <?php if (count($data) > 0) { ?>
<ul class="list-unstyled">
    <?php for ($i = 0; $i < count($data); $i++) {
        $mapData = $this->googleDirection($data[$i]['lat'], $data[$i]['lng']) ?>
  <li class="media my-4">
    <img class="mr-3" style="width: 96px; height: 96px;" src="<?php echo $media->getCover($data[$i]['ref']); ?>" alt="Generic placeholder image">
    <div class="media-body">
      <a href="<?php echo $this->seo($data[$i]['ref'], "view"); ?>""><h5 class="mt-0 mb-1"><?php echo $data[$i]['project_name']; ?></h5></a>
      <i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->truncate( $data[$i]['project_dec'], 250); ?><br>
      <i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data[$i]['address']; ?><br>
      <i class="fa fa-car" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $mapData['distance']['text']." in ".$mapData['duration']['text']; ?><br>
      <small><i class="fa fa-tags" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->getTagFromWord($data[$i]['tag'], "tag", "blank"); ?></small>

    </div>
  </li>
    <?php } ?>
</ul>
<?php $this->pagination($page, $dataCount, "page", "search_per_page"); ?>
    <?php } else { ?>
    <p>You search result for "<?php echo $s; ?>" brought no result.</p>
    <?php } ?>
        <?php }

        public function categoryList($id) {
            global $search;
            global $media;
            global $users;
            global $country;
            global $category;
            global $rating;
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("ad_per_page");
            $start = $page*$limit;

            $catData = $search->catSearchData($_SESSION['location'], $id, $start, $limit);
            $list = $catData['data'];
            $dataCount = $catData['count'];?>

            <H4>Ads in <?php  echo ucwords(strtolower($category->getSingle($id))); ?></H4>
            <?php if (count($list) > 0) { ?>
            <div class="row">
                <?php for ($i = 0; $i < count($list); $i++) {
                    if ($list[$i]['project_type'] == "client") {
                        $project_type = "+";
                    } else {
                        $project_type = "-";
                    } ?>
            <div class="card" style="width: 21rem;">
            <img class="card-img-top" src="<?php echo $media->getCover($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['project_name']; ?>">
            
            <div class="card-body">
                <h5 class="card-title"><?php echo $list[$i]['project_name']; ?></h5>
                <p class="card-text"><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $list[$i]['address']; ?></p>
                <p class="card-text"><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($list[$i]['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($list[$i]['user_id'], "screen_name"); ?>&nbsp;<?php echo $rating->drawRate($rating->getRate($list[$i]['user_id'])); ?></a></p>
                <p class="card-text <?php echo $list[$i]['project_type']; ?>"><i class="fa fa-money" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $project_type." ".$country->getCountryData( $list[$i]['country'] )." ".number_format($list[$i]['default_fee'], 2)." ".$this->cleanText($list[$i]['billing_type']); ?></p>
            </div>
            <div class="card-footer">
                <a href="<?php echo $this->seo($list[$i]['ref'], "view"); ?>" class="btn btn-primary">Open</a>
                
            </div>
            </div>
                <?php } ?>

            </div>
            <?php $this->pagination($page, $dataCount, "page", "ad_per_page"); ?>
            <?php } else { ?>
            <p>No Listing in this category yet</p>
            <?php }
        }

        public function promoted($id=false) {
            global $projects;
            global $media;
            global $users;
            global $country;
            global $rating;
            
            $latitude = $_SESSION['location']['latitude'];
            $longitude = $_SESSION['location']['longitude'];

            $list = $projects->promotedData($id, $longitude, $latitude);
            if (count($list) > 0) {
             ?>
             <H4> Featured Ads in <?php  echo ucwords(strtolower($_SESSION['location']['city'])); ?></H4>
            <div class="row">
                <?php for ($i = 0; $i < count($list); $i++) {
                    if ($list[$i]['project_type'] == "client") {
                        $project_type = "+";
                    } else {
                        $project_type = "-";
                    } ?>
            <div class="card" style="width: 21rem;">
            <img class="card-img-top" src="<?php echo $media->getCover($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['project_name']; ?>">
            
            <div class="card-body">
                <h5 class="card-title"><?php echo $list[$i]['project_name']; ?></h5>
                <p class="card-text"><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $list[$i]['address']; ?></p>
                <p class="card-text"><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($list[$i]['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($list[$i]['user_id'], "screen_name"); ?>&nbsp;<?php echo $rating->drawRate($rating->getRate($list[$i]['user_id'])); ?></a></p>
                <p class="card-text <?php echo $list[$i]['project_type']; ?>"><i class="fa fa-money" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $project_type." ".$country->getCountryData( $list[$i]['country'] )." ".number_format($list[$i]['default_fee'], 2)." ".$this->cleanText($list[$i]['billing_type']); ?></p>
            </div>
            <div class="card-footer">
                <a href="<?php echo $this->seo($list[$i]['ref'], "view"); ?>" class="btn btn-primary">Open</a>
                
            </div>
            </div>
                <?php } ?>

                </div>
            <?php }
        }

        public function aroundMe() {
            global $projects;
            global $media;
            global $users;
            global $country;
            global $rating;
            global $options;

            if (isset($_REQUEST['aroundPage'])) {
              $page = $_REQUEST['aroundPage'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("ad_per_page");
            $start = $page*$limit;
            
            $latitude = $_SESSION['location']['latitude'];
            $longitude = $_SESSION['location']['longitude'];

            $data = $projects->aroundMeData($longitude, $latitude, $start, $limit);

            $list = $data['list'];
            $dataCount = $data['dataCount'];
            
            if (count($list) > 0) {
             ?>
             <H4>Ads Around Me</H4>
            <div class="row">
                <?php for ($i = 0; $i < count($list); $i++) {
                    if ($list[$i]['project_type'] == "client") {
                        $project_type = "+";
                    } else {
                        $project_type = "-";
                    } ?>
            <div class="card" style="width: 21rem;">
            <img class="card-img-top" src="<?php echo $media->getCover($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['project_name']; ?>">
            
            <div class="card-body">
                <h5 class="card-title"><?php echo $list[$i]['project_name']; ?></h5>
                <p class="card-text"><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $list[$i]['address']; ?></p>
                <p class="card-text"><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($list[$i]['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($list[$i]['user_id'], "screen_name"); ?>&nbsp;<?php echo $rating->drawRate($rating->getRate($list[$i]['user_id'])); ?></a></p>
                <p class="card-text <?php echo $list[$i]['project_type']; ?>"><i class="fa fa-money" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $project_type." ".$country->getCountryData( $list[$i]['country'] )." ".number_format($list[$i]['default_fee'], 2)." ".$this->cleanText($list[$i]['billing_type']); ?></p>
            </div>
            <div class="card-footer">
                <a href="<?php echo $this->seo($list[$i]['ref'], "view"); ?>" class="btn btn-primary">Open</a>
                
            </div>
            </div>
                <?php } ?>

            </div>
            <?php $this->pagination($page, $dataCount, "aroundPage", "ad_per_page"); ?>
            <?php }
        }

        public function recentlyPosted() {
            global $projects;
            global $media;
            global $users;
            global $country;
            global $rating;
            global $options;

            if (isset($_REQUEST['recentPage'])) {
              $page = $_REQUEST['recentPage'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("ad_per_page");
            $start = $page*$limit;
            
            $latitude = $_SESSION['location']['latitude'];
            $longitude = $_SESSION['location']['longitude'];

            $data = $projects->recentlyPostedData($longitude, $latitude, $start, $limit);
            
            $list = $data['list'];
            $dataCount = $data['dataCount'];

            if (count($list) > 0) {
             ?>
            <H4> Recently Posted Ads Around <?php  echo $_SESSION['location']['city']; ?> </H4>
            <div class="row">
                <?php for ($i = 0; $i < count($list); $i++) {
                    if ($list[$i]['project_type'] == "client") {
                        $project_type = "+";
                    } else {
                        $project_type = "-";
                    } ?>
            <div class="card" style="width: 21rem;">
            <img class="card-img-top" src="<?php echo $media->getCover($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['project_name']; ?>">
            
            <div class="card-body">
                <h5 class="card-title"><?php echo $list[$i]['project_name']; ?></h5>
                <p class="card-text"><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $list[$i]['address']; ?></p>
                <p class="card-text"><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($list[$i]['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($list[$i]['user_id'], "screen_name"); ?>&nbsp;<?php echo $rating->drawRate($rating->getRate($list[$i]['user_id'])); ?></a></p>
                <p class="card-text <?php echo $list[$i]['project_type']; ?>"><i class="fa fa-money" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $project_type." ".$country->getCountryData( $list[$i]['country'] )." ".number_format($list[$i]['default_fee'], 2)." ".$this->cleanText($list[$i]['billing_type']); ?></p>
            </div>
            <div class="card-footer">
                <a href="<?php echo $this->seo($list[$i]['ref'], "view"); ?>" class="btn btn-primary">Open</a>
                
            </div>
            </div>
                <?php } ?>

            </div>
            <?php $this->pagination($page, $dataCount, "recentPage", "ad_per_page"); ?>
            <?php }
        }

        public function profileHome() {
            global $users;
            global $projects;
            global $media;
            global $country;
            global $wallet; 

            $regionData = $country->getLoc($_SESSION['location']['code']);
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);

            $jobs = $projects->getSortedList("ACTIVE", "status", "user_id", $ref, false, false, "ref", "DESC", "AND", 0, 10);
             ?>
            <div class="row">
                <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
                <?php $users->getProfileImage($ref, "card-img-top", "25"); ?>
                <div class="card-body">
                    <a href="<?php echo URL."edit/Image"; ?>" class="card-link">Modify Display Picture</a>
                </div>
                </div>
                <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $data['last_name']." ".$data['other_names']; ?>&nbsp;&nbsp;<?php if ( $data['verified'] == 2) { ?><i class="fas fa-user-check"></i>&nbsp;<?php } if ( $data['badge'] == 1) {?><i class="fas fa-award"></i><?php } ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?php echo $data['screen_name']; if ($data['screen_name_cam_change'] == 0) { ?> (<a href="<?php echo URL."edit/ScreenName"; ?>" class="card-link">edit username</a>)<?php } ?><br>
                    <small><a href="<?php echo URL."profile/".$data['screen_name']; ?>" target="_blank"><?php echo URL."profile/".$data['screen_name']; ?></a></small></h6>
                    <p class="card-text">Email<br>
                    <strong><?php echo $data['email']; ?></strong></p>
                    <p class="card-text">Wallet Balance<br>
                    <p><i class="fas fa-piggy-bank"></i></i>&nbsp;Current Balance:&nbsp;<?php echo $regionData['currency_symbol']." ".number_format($wallet->balance($ref, $regionData['ref'], true), 2); ?></p>
                    <p><i class="fas fa-piggy-bank"></i></i>&nbsp;Available Balance:&nbsp;<?php echo $regionData['currency_symbol']." ".number_format($wallet->balance($ref, $regionData['ref']), 2); ?></p>
                    <a href="<?php echo URL."wallet"; ?>" class="card-link">View Wallet</a></p>
                    <a href="<?php echo URL."edit/Profile"; ?>" class="card-link">Update Profile</a>
                    <a href="<?php echo URL."edit/Password"; ?>" class="card-link">Update Password</a>
                    <a href="<?php echo URL."edit/IDs"; ?>" class="card-link">Upload IDs</a>
                </div>
                </div>
            </div>
            <h4>Active Jobs</h4>
            <div class="row">
                <?php for ($i = 0; $i < count($jobs); $i++) { ?>
            <div class="card" style="width: 20rem;">
            <img class="card-img-top" src="<?php echo $media->getCover($jobs[$i]['ref']); ?>" alt="<?php echo $jobs[$i]['project_name']; ?>">
            
            <div class="card-body">
                <h5 class="card-title"><?php echo $jobs[$i]['project_name']; ?></h5>
            </div>
            <div class="card-footer">
                <a href="<?php echo $this->seo($jobs[$i]['ref'], "view"); ?>" class="btn btn-primary">Open</a>
                
            </div>
            </div>
                <?php } ?>

            </div>
        <?php }

        private function publicProfile($view) {
            global $users;
            global $rating_comment;
            global $rating; 

            $data = $users->listOne($view, "screen_name");
            $ratingDataVendor = $rating->publicPage($data['ref'], "vendors");
            $ratingDataClients = $rating->publicPage($data['ref'], "clients");
            $comments = $rating_comment->getSortedList($data['ref'], "user_id");
            ?>
            <div class="row">
                <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <?php $users->getProfileImage($data['ref'], "card-img-top", "25"); ?>
                </div>
                <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $data['last_name']." ".$data['other_names']; ?>&nbsp;&nbsp;<?php if ( $data['verified'] == 2) { ?><i class="fas fa-user-check"></i>&nbsp;<?php } if ( $data['badge'] == 1) {?><i class="fas fa-award"></i><?php } ?></h5>
                    <p><?php  echo $rating->drawRate($rating->getRate($data['ref'])); ?></p>
                    <h6 class="card-subtitle mb-2 text-muted"><?php echo $data['screen_name']; ?></h6>
                    <p> User since <?php echo $this->get_time_stamp( strtotime( $data['create_time'] ) ); ?></p>
                    <h4>Ratings for Posted Jobs Offered</h4>
                    <?php if (count($ratingDataClients) > 0) { ?>
                        <p><?php for ($i = 0; $i < count($ratingDataClients); $i++) {
                            echo "<strong>".$ratingDataClients[$i]['question']."</strong>: ".$rating->drawRate($ratingDataClients[$i]['val'])."<br>";
                        } ?></p>
                    <?php } else { ?>
                        <p>Not rated yet</p>
                    <?php } ?>
                    <h4>Ratings for Posted Services Provided</h4>
                    <?php if (count($ratingDataVendor) > 0) { ?>
                        <p><?php for ($i = 0; $i < count($ratingDataVendor); $i++) {
                            echo "<strong>".$ratingDataVendor[$i]['question']."</strong>: ".$rating->drawRate($ratingDataVendor[$i]['val'])."<br>";
                        } ?></p>
                    <?php } else { ?>
                        <p>Not rated yet</p>
                    <?php } ?>
                    <h4>Comments</h4>
                    <?php if (count($comments) > 0) { ?>
                        <p><?php for ($i = 0; $i < count($comments); $i++) {
                            if ($comments[$i]['comment'] != "") {
                                echo $comments[$i]['comment']."<br><small>".$users->listOnValue( $comments[$i]['reviewed_by'], "screen_name").", ".$this->get_time_stamp( strtotime( $comments[$i]['create_time'] ) )."</small><br><br>";
                            }
                        }?></p>
                    <?php } else { ?>
                        <p>Not rated yet</p>
                    <?php } ?>
                </div>
                </div>
            </div>
        <?php }

        public function drawMap() { ?>
            <div class="map"></div>
            <script src="js/app.js"></script>
            <script type="text/javascript">
                const apiKey = 'AIzaSyBfB1Qa1TIfjSrCK9FiStkD0KareG4atLs';
                const latitude = <?php echo $_SESSION['location']['latitude']; ?>;
                const longitude = <?php echo $_SESSION['location']['longitude']; ?>;
            </script>
            <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GoogleAPI; ?>&callback=initMap"></script>
        <?php 
        }

        public function listNotification($view) {
            if ($view == "all") {
                return $this->notifyList();
            } else if ($view == "messages") {  
                return $this->messages();
            }
        }

        function messages() {
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            global $notifications;
            $notificationList = $notifications->getSortedList($_SESSION['users']['ref'], "user_id", "event", "messages", false, false, "ref", "DESC", "AND", $start, $limit); 
            $notificationListCount = $notifications->getSortedList($_SESSION['users']['ref'], "user_id", "event", "messages", false, false, "ref", "DESC", "AND", false, false, "count"); 
            ?>
            <h2>Message Inbox</h2>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Sender</th>
                <th scope="col">Message</th>
                <th scope="col">Created</th>
                <th scope="col">Last Modified</th>
              </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < count($notificationList); $i++) { ?>
                <tr <?php if ($notificationList[$i]['status'] == 0) { ?>class="table-active"<?php } ?>>
                <th scope="row"><?php echo $start+$i+1; ?></th>
                <td><?php echo $this->url( $notificationList[$i]['event'], $notificationList[$i]['event_id']); ?></td>
                <td><?php echo $notificationList[$i]['message']; ?></td>
                <td><?php echo $notificationList[$i]['create_time']; ?></td>
                <td><?php echo $notificationList[$i]['modify_time']; ?></td>
              </tr>
                <?php } ?>
            </tbody>
          </table>
          <?php $this->pagination($page, $notificationListCount);
        }

        private function notifyList() {
            global $notifications;
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;

            $listArray = $notifications->listAllData($_SESSION['users']['ref'], $start, $limit);

            $notificationList = $listArray['list'];
            $notificationListCounut = $listArray['listCount']; 
            ?>
            <h2>All Notifications</h2>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Event</th>
                <th scope="col">Message</th>
                <th scope="col">Created</th>
                <th scope="col">Last Modified</th>
                <th scope="col">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < count($notificationList); $i++) { ?>
                <tr <?php if ($notificationList[$i]['status'] == 0) { ?>class="table-active"<?php } ?>>
                <th scope="row"><?php echo $start+$i+1; ?></th>
                <td><?php echo $this->url( $notificationList[$i]['event'], $notificationList[$i]['event_id']); ?></td>
                <td><?php echo $notificationList[$i]['message']; ?></td>
                <td><?php echo $notificationList[$i]['create_time']; ?></td>
                <td><?php echo $notificationList[$i]['modify_time']; ?></td>
                <td><?php echo $this->url( $notificationList[$i]['event'], $notificationList[$i]['event_id'], true); ?></td>
              </tr>
                <?php } ?>
            </tbody>
          </table>
          <?php $this->pagination($page, $notificationListCounut);
        }

        private function url($text, $id, $link=false) {
            global $projects;
            global $media;
            if ($text == "project_messages") {
                if ($link) {
                    $show = "Open";
                } else {
                    $show = '<img src="'.$media->getCover($id).'" alt="'.$projects->getSingle($id).'" class="img-thumbnail" style="width:auto;height:75px;" height="50px">';
                }
                return '<a href="'.$this->seo($id, "view").'#messages">'.$show.'</a>';
            }
        }

        public function headMeta($ref) {
            global $category;
            $data = $category->listOne($ref, "ref"); ?>
            <title><?php echo $data['category_title']; ?></title>
            <meta name="Description" content="Ads in <?php echo $data['category_title']; ?>">
            <meta name="Keywords" content="<?php echo $data['category_title']; ?>">
            <link rel="canonical" href="<?php echo $this->seo($ref, "category"); ?>" />
            <meta property="og:title" content="<?php echo $data['category_title']; ?>" />
            <meta property="og:url" content="<?php echo $this->seo($ref, "category"); ?>" />
        <?php }
    }
?>
