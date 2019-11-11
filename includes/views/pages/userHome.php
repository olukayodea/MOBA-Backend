<?php
    class userHome extends common {
        public function navigationBar($redirect) { ?>
          <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect; ?>/all"><b>List All</b></a></p>
          <?php if ($_SESSION['users']['user_type'] == 1) { ?>
          <div class="moba-line my-2"></div>
          <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/current"; ?>"><b>Current Interest</b></a></p>
          <?php } else if ($_SESSION['users']['user_type'] <= 2) { ?>
          <div class="moba-line my-2"></div>
          <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/open"; ?>"><b>Open Request</b></a></p>
          <?php } ?>
          <div class="moba-line my-2"></div>
          <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/running"; ?>"><b>Active Request</b></a></p>
          <div class="moba-line my-2"></div>
          <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/past"; ?>"><b>Past Job Request</b></a></p>
        <?php }

        public function navigationBarNotification($redirect) { ?>
            <a href="<?php echo URL.$redirect; ?>">Notifications</a> | <a href="<?php echo URL."/inbox"; ?>">Messages</a> | <a href="<?php echo URL.$redirect."ads/saved"; ?>">Saved Ads</a>
        <?php }

        function requestPageContentpublic($ref, $view, $redirect, $data=false) {
            $this->showAll($ref, $view, $redirect);
          }

        public function pageContent($redirect, $id=false, $type=false) {
            if ($redirect == "category") {
                //$this->promoted();
                $this->homeCategory();
            } else if ($redirect == "categoryHome") {
                $this->categoryHomePage($id, $type);
            } else if ($redirect == "categoryList") {
                $this->categoryRequest($id);
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
            } else if ($redirect == "requestProfile") {
                $this->requestProfile($id);
            }
        }
  
        public function showAll($ref, $view, $redirect) {
            global $request;
            global $options;
            global $country;
            global $category;

            if (isset($_REQUEST['page'])) {
                $page = $_REQUEST['page'];
            } else {
                $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;

            $data = $request->listAllData($ref, $view, $start, $limit);

            $list = $data['list'];
            $listCount = $data['listCount'];
            $tag = $data['tag']; ?>

            <h2><?php echo $tag; ?></h2>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Service</th>
                    <th scope="col">Fee</th>
                    <th scope="col">Request Time</th>
                    <th scope="col">Location</th>
                    <th scope="col">Created</th>
                    <th scope="col">Last Modified</th>
                    <th scope="col">#</th>
                </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < count($list); $i++) { ?>
                        <tr>
                            <th scope="row"><?php echo $start+$i+1; ?></th>
                            <td><?php echo $category->getSingle( $list[$i]['category_id'] ); ?></td>
                            <td><?php echo $country->getSingle( $list[$i]['region'], "currency_symbol")." ".number_format($list[$i]['fee'], 2); ?></td>
                            <td><?php echo date(' j-m-Y h:i A', $list[$i]['time']); ?></td>
                            <td><a href="<?php echo "http://maps.google.com/maps?saddr=".$list[$i]['latitude'].",".$list[$i]['longitude']; ?>" target="_blank">Open in Maps</a></td>
                            <td><?php echo $this->get_time_stamp(strtotime($list[$i]['create_time'])); ?></td>
                            <td><?php echo $this->get_time_stamp(strtotime($list[$i]['modify_time'])); ?></td>
                            <th scope="col"><?php echo $this->urlLink($list[$i]['ref'], $list[$i]['status'], $view, $redirect); ?></th>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php $this->pagination($page, $listCount);
        }

        private function urlLink($id, $status, $view, $redirect) {
            if ($status == "OPEN") {
                return "<a href='".URL."newRequestDetails?id=".$id."' title='Continue'><i class='fas fa-forward'></i></a>&nbsp;<a href='".URL."".$redirect."/".$view."?remove&id=".$id."' onClick='return confirm(\"this action end this request, are you sure you want to continue ?\")' title='Remove'><i class='fas fa-trash-alt' style='color:red'></i></a>";
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
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn purple-bn1">Done Editing</button>
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
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn purple-bn1">Done Editing</button>
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
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn purple-bn1">Done Editing</button>
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
                                <button type="submit" name="updateUsername" disabled class="btn purple-bn1">Update Username</button>
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
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn purple-bn1">Done Editing</button>
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
                            <button type="submit" name="updateProfile" class="btn purple-bn1">Update Profile</button>
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
                        <button onclick="location='<?php echo URL; ?>profile'" class="btn purple-bn1">Done Editing</button>
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
                            <button type="submit" name="updatePassword" class="btn purple-bn1">Update Password</button>
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
                $to['latitude'] = $data[$i]['lat'];
                $to['longitude'] = $data[$i]['lng'];
                $mapData = $this->googleDirection($_SESSION['location'], $to) ?>
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
                <a href="<?php echo $this->seo($list[$i]['ref'], "view"); ?>" class="btn purple-bn1">Open</a>
                
            </div>
            </div>
                <?php } ?>

            </div>
            <?php $this->pagination($page, $dataCount, "page", "ad_per_page"); ?>
            <?php } else { ?>
            <p>No Listing in this category yet</p>
            <?php }
        }

        private function promoted($id=false) {
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
                <a href="<?php echo $this->seo($list[$i]['ref'], "view"); ?>" class="btn purple-bn1">Open</a>
                
            </div>
            </div>
                <?php } ?>

                </div>
            <?php }
        }

        private function homeCategory() {
            global $country;
            global $category;

            $location['latitude'] = $_SESSION['location']['latitude'];
            $location['longitude'] = $_SESSION['location']['longitude'];

            $loc = $country->getLoc($_SESSION['location']['code']);

            $list = $category->categoryList($loc['ref'], 0);

            
            for ($i = 0; $i < count($list); $i++) {
             ?>
                <div class="col-lg-3" style="text-align:center">
					<div class="h-100">
						<a href="<?php echo $this->seo( $list[$i]['ref'], "view" ); ?>"><img class="rounded card-img-top" src="<?php echo $category->getIcon($list[$i]['ref']); ?>" alt=""></a>	
						<h6><?php echo strtoupper($list[$i]['category_title']); ?></h6>
					</div>
				</div>
            <?php }
        }

        private function categoryHomePage($id, $type) {
            global $category;
            global $country;
            global $rating;
            global $users;
            global $wallet;
            $data = $category->listOne($id);
            $countryData = $country->listOne($data['country'], "ref");
            $usersData = $category->totalUsers($id);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <img class="card-img-top my-3" src="<?php echo $category->getIcon( $data['ref'] ); ?>" alt="">
                    <i class="fa fa-clock-o"></i> <?php echo $this->numberPrintFormat(count($usersData)); ?>  
                    <span class="float-right"><i class="fa fa-map-marker"></i> <?php echo $_SESSION['location']['city'].", ".$_SESSION['location']['country']; ?></span>
                    
                    
                    <div class="moba-line my-3"></div>
                    <p><b>Completed Jobs:</b> 2.9K</p>	
                    <p><b>Average Response Time:</b> 1Hr 25m</p>
                    <p><b>Callout Charge:</b> <?php echo $countryData['currency_symbol']." ".number_format($data['call_out_charge'], 2); ?></p>		
                    
                </div>
                <div class="col-lg-8">			
                    <h5><?php echo $data['category_title']; ?></h5>
                    <div class="moba-line mb-3"></div>
                    <?php if ($type === false) { ?>
                    <form name="sentMessage" method="post" id="contactForm" action="<?php echo URL; ?>newRequest" novalidate>
                        <input type="hidden" name="id" value="<?php echo $data['ref']; ?>">
                        <div class="control-group form-group my-5">
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="btn purple-bn pd" id="sendMessageButton">Request Service </button>
                            </div>
                        </div>
                        </div>
                    </form>
                    <?php } else {
                        $check = $wallet->getDefault($_SESSION['users']['ref']);
                        if ($check) { ?>
                    <form name="sentMessage" method="post" id="contactForm" action="<?php echo URL; ?>newRequest" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $data['ref']; ?>">

                        <div class="form-group">
                            <input id="autocomplete" name="autocomplete" placeholder="Enter your address" required onfocus="geolocate()" type="text" class="form-control" autocomplete="false" value=""/>
                            <input type="text" name="city" id="locality" value="">
                            <input type="text" name="state" id="administrative_area_level_1" value="">
                            <input type="text" name="postal_code" id="postal_code" value="">
                            <input type="text" name="country" id="country" value="">
                            <input type="text" name="lat" id="lat" value="">
                            <input type="text" name="country_code" id="country_code" value="">
                            <input type="text" name="lng" id="lng" value="">
                            <small id="autocomplete_help" class="form-text text-muted">This will be the location where the service is required.</small>
                        </div>
						<div class="form-group">
                            <input type="number" name="fee" id="fee" class="form-control" placeholder="Price Range" min="<?php echo $data['call_out_charge']; ?>" required>
                            <small id="autocomplete_help" class="form-text text-muted">This amount must be greater than or equal to <?php echo $countryData['currency_symbol']." ".number_format($data['call_out_charge'], 2); ?>.</small>
						</div>
						<div class="form-group">
                            <select name="time" id="time" class="form-control" required>
                                <option value="">Select One</option>
                                <option value="60">Within an Hour</option>
                                <option value="180">1 to 3 Hours</option>
                                <option value="240">3 Hours or more</option>
                            </select>
                            <small id="autocomplete_help" class="form-text text-muted">How soon do you want this service done.</small>
						</div>
						<div class="form-group">
                            <textarea name="description" id="description" required class="form-control" placeholder="Job Description"></textarea>
                            <small id="autocomplete_help" class="form-text text-muted">Tesll us what you want to get done.</small>
                        </div>
                        <div class="form-group">
                            <label for="uploadFile">Include pictures with as much details as you will prefer. You can upload a maximum of 10 media with a maximum fike size of 2MB. (Optional)</label>
                            <div class="row">
                                <div class="col-sm-2 imgUp">
                                    <div class="imagePreview"></div>
                                    <label class="btn btn-primary">
                                    Select File<input type="file" name="uploadFile[]" id="uploadFile" class="uploadFile img" value="Upload Photo" accept="image/*" style="width: 0px;height: 0px;overflow: hidden;" onchange="checkFileSize(event)">
                                    </label>
                                </div><!-- col-2 -->
                                <i class="fa fa-plus imgAdd"></i>
                            </div><!-- row -->
                        </div>
                        
                        <div class="col-lg-12">
                            <button type="submit" class="btn purple-bn pd"  name="sendMessageButton" id="sendMessageButton">Request Service </button>
                        </div>
					</form>
                        </div>
                    </form>
                    <?php } else { ?>
                        <div class="alert alert-danger" role="alert">
                        <strong>You must have at least one payment card saved to make a request. <a href="">Click here to add your payment card</a> then come back to create the request again</strong>
                        </div>
                    <?php } ?>
                    <?php } ?>
                    <div class="control-group form-group my-5">
                    <h5>Popular Service Providers</h5>
                    <div class="moba-line mb-3"></div>
                    <div class="row">
                        <?php if (count($usersData) > 0) { ?>
                        <?php for ($i = 0; $i < count($usersData); $i++) { ?>
                            <div class="col-lg-4">
                                <div class="moba-content__img">
                                    <img src="users/av1.jpg" class="mr-3 float-left"/>
                                    <small><?php echo $users->listOnValue($usersData[$i]['user_id'], "screen_name"); ?></small><br>
                                    <?php echo $rating->drawRate(intval($rating->getRate($usersData[$i]['user_id']))); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <?php } else { ?>
                            <p>No service provider listed here yet</p>
                        <?php } ?>
                    </div>
                    
                    <div class="moba-line m-3"></div>
                        
                </div>
            </div>
            </div>
            <script type="text/javascript" src="<?php echo URL; ?>js/imageUpload.js"></script>
            <script type="text/javascript" src="<?php echo URL; ?>js/places.js"></script>
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GoogleAPI; ?>&libraries=places&callback=initAutocomplete" async defer></script>
            <script type="text/javascript">
                function checkFileSize(e) {
                    var element = e.target.id;
                    if ((document.getElementById(element).files[0].size/1024/1024 ) > 2) {
                        alert('This file size is: ' + (document.getElementById(element).files[0].size/1024/1024).toFixed(2) + "MB");
                        document.getElementById(element).value = "";
                    }
                }
            </script>
        <?php
        }   

        private function categoryRequest($id) {
            global $request;
            global $category;
            global $country;
            global $rating;
            $data = $request->listOne($id);
            $categoryData = $category->listOne($data['category_id']);
            $countryData = $country->listOne($data['region'], "ref");
            $loc = $this->googleGeoLocation($data['longitude'], $data['latitude']);

            $addressData['latitude'] = $data['latitude'];
            $addressData['longitude'] = $data['longitude'];
            $addressData['state_code'] = $loc['province_code'];
            $addressData['state'] = $loc['province'];
            $addressData['code'] = $loc['country_code'];
            $addressData['country'] = $loc['country'];

            $result = $request->findRequest($addressData, $data['category_id'])['data'];
            //echo "<pre>";
            //print_r($result);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <img class="card-img-top my-3" src="<?php echo $category->getIcon( $categoryData['ref'] ); ?>" alt="">
                    <i class="fa fa-map-marker"></i> <?php echo $loc['address']; ?>
                    <div class="moba-line my-3"></div>
                    <p><b>Job Category:</b> <?php echo $categoryData['category_title']; ?></p>	
                    <p><b>Description:</b> <?php echo $data['description']; ?></p>
                    <p><b>Average Time:</b> <?php echo date('l jS \of F Y h:i:s A', $data['time']); ?></p>
                    <p><b>Callout Charge:</b> <?php echo $countryData['currency_symbol']." ".number_format($data['fee'], 2); ?></p>
                    <p><a href="<?php echo URL."ads/all?remove&id=".$id; ?>" class="btn red-bn pd" onClick='return confirm("this action end this request, are you sure you want to continue ?")' title='Remove'><i class="fas fa-trash-alt"></i>&nbsp;&nbsp;Delete</a></p>	
                    
                </div>
                <div class="col-lg-8">			
                    <h5>Search Result for <?php echo $categoryData['category_title']; ?></h5>
                    <div class="moba-line mb-3"></div>
                    <p>Your request for <strong><?php echo $categoryData['category_title']; ?></strong> at <strong><?php echo $loc['address']; ?></strong> brought <strong><?php echo intval(count($result)); ?></strong> Result(s)</p>
                    <div class="row">
                        <?php if (count($result) > 0) { ?>
                            <?php for ($i = 0; $i < count($result); $i++) {
                                $mapData = $this->googleDirection($addressData, $result[$i]); ?>
                                <div class="col-lg-4">
                                    <div class="moba-content__img">
                                    <div class="row">
                                        <div class="col-lg-4">
                                        <img src="users/av1.jpg" class="mr-3 float-left"/>
                                        </div>
                                        <div class="col-lg-7">
                                        <?php echo $result[$i]['screen_name']; ?><br>
                                        <small><i class="fas fa-user-alt"></i>&nbsp;<?php echo $result[$i]['about_me']; ?></small><br>
                                        <small><i class="fas fa-road"></i>&nbsp;<?php echo $mapData['distance']['text'] ?></small><br>
                                        <small><i class="fas fa-clock"></i>&nbsp;<?php echo $mapData['duration']['text'] ?></small><br>
                                        <?php echo $rating->drawRate(intval($rating->getRate($result[$i]['ref']))); ?><br>
                                        <a href="<?php echo $this->seo($result[$i]['ref'], "profile")."/".$id."/view"; ?>" title="View Profile" class="btn purple-bn-small pd"><i class="fas fa-eye"></i></a>&nbsp;<a href="<?php echo $this->seo($result[$i]['ref'], "profile")."/".$id."/message"; ?>" title="Message" class="btn purple-bn-small pd"><i class="fas fa-comments"></i></a>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    
                    <div class="moba-line m-3"></div>
                        
                </div>
            </div>
            </div>
        <?php 
        }

        private function requestProfile($array) {
            global $request;
            global $category;
            global $country;
            global $rating;
            global $users;
            global $rating_question;
            global $messages;
            $data = $request->listOne($array[2]);
            $categoryData = $category->listOne($data['category_id']);
            $countryData = $country->listOne($data['region'], "ref");
            $usersData = $users->listOne($array[0]);
            $user_r_id = $usersData['ref'];
            $user_id = trim($_SESSION['users']['ref']);
            $loc = $this->googleGeoLocation($data['longitude'], $data['latitude']);

            $addressData['latitude'] = $data['latitude'];
            $addressData['longitude'] = $data['longitude'];
            $addressData['state_code'] = $loc['province_code'];
            $addressData['state'] = $loc['province'];
            $addressData['code'] = $loc['country_code'];
            $addressData['country'] = $loc['country'];

            $checkRate = $rating_question->getSortedList("vendors", "question_type");
            // echo "<pre>";
            // print_r($usersData);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <img class="card-img-top my-3" src="<?php echo $users->picURL( $usersData['ref'], 250 ); ?>" alt="<?php echo $usersData['screen_name']; ?>">
                    <i class="fa fa-map-marker"></i> <?php echo $loc['address']; ?>
                    <div class="moba-line my-3"></div>
                    <p><b>Job Category:</b> <?php echo $categoryData['category_title']; ?></p>	
                    <p><b>Description:</b> <?php echo $data['description']; ?></p>
                    <p><b>Average Time:</b> <?php echo date('l jS \of F Y h:i:s A', $data['time']); ?></p>
                    <p><b>Callout Charge:</b> <?php echo $countryData['currency_symbol']." ".number_format($data['fee'], 2); ?></p>
                    <p><a href="<?php echo URL."ads/all?remove&id=".$data['ref']; ?>" class="btn purple-bn pd" onClick='return confirm("this action end this request, are you sure you want to continue ?")' title='Remove'><i class="fas fa-tags"></i>&nbsp;&nbsp;Negotiate Fees</a></p>
                    <?php if ($array[3] == "view") { ?>
                    <p><a href="<?php echo $this->seo($usersData['ref'], "profile")."/".$data['ref']."/message"; ?>" class="btn purple-bn pd" title='Message'><i class="fas fa-comments"></i>&nbsp;&nbsp;Message</a></p>
                    <?php } else { ?>
                    <p><a href="<?php echo $this->seo($usersData['ref'], "profile")."/".$data['ref']."/view"; ?>" class="btn purple-bn pd" title='Message'><i class="fas fa-eye"></i>&nbsp;&nbsp;View Profile</a></p>
                    <?php } ?>
                    <?php if ($data['status'] == "OPEN") { ?>
                        <p><a href="<?php echo URL."ads/all?remove&id=".$data['ref']; ?>" class="btn red-bn pd" onClick='return confirm("this action end this request, are you sure you want to continue ?")' title='Remove'><i class="fas fa-trash-alt"></i>&nbsp;&nbsp;Delete</a></p>
                    <?php } ?>
                    
                </div>
                <div class="col-lg-8">
                    <?php if ($array[3] == "view") { ?>
                        <h5><?php echo $usersData['screen_name']; ?></h5>
                        <div class="moba-line mb-3"></div>
                        <p><?php echo $usersData['about_me']; ?></p>
                        <?php echo $rating->drawRate(intval($rating->getRate($usersData['ref']))); ?>
                        <p><small>Number of Tasks Completed</small><br>
                        <small> Response Time</small><br>
                        <small> Rating<small<br>
                        <strong><?php echo $rating->textRate(intval($rating->getRate($usersData['ref']))); ?></strong></p>

                        <div class="row">
                            <?php if (count($checkRate) > 0) { ?>
                                <?php for ($i = 0; $i < count($checkRate); $i++) { ?>
                                    <div class="col-lg-4">
                                        <div class="moba-content__img">
                                            <?php echo $checkRate[$i]['question']; ?><br>
                                            <?php echo $rating->drawRate(intval($rating->getRate($rating->getRate($usersData['ref'], $checkRate[$i]['ref'])))); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    <?php } else {
                        
                        $messages->markRead($_SESSION['users']['ref'], $data['ref']);
                        $initialComment = $messages->getPage($data['ref'], $user_r_id, $user_id); ?>     			
                        <h5><a name="messages"></a>Messages with <?php echo $users->listOnValue($user_r_id, "screen_name"); ?></h5>
                        <p><?php echo $usersData['about_me']; ?></p>
                        <div class="moba-line mb-3"></div>
                        <?php echo $rating->drawRate(intval($rating->getRate($usersData['ref']))); ?>
                        <div class="moba-line mb-3"></div>
                        <div class="row">
                            <ol id="update" >
                            <?php for ($i = 0; $i < count($initialComment); $i++) { ?>
                                <li class="media" id='<?php echo $initialComment[$i]['ref']; ?>'>
                                <?php $users->getProfileImage($initialComment[$i]['user_id'], "mr-3", "50"); ?>
                                <div class="media-body">
                                <small class="time"><i class="fa fa-clock-o"></i> <?php echo $this->get_time_stamp(strtotime($initialComment[$i]['create_time'])); ?></small>
                                <p class="mt-0">
                                <?php if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                                    $m_type_data = explode("_", $initialComment[$i]['m_type_data'] ) ?>
                                    <i class="fa fa-handshake" aria-hidden="true"></i><br><?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>You have a <?php } ?>new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['country'] )." " .$m_type_data[0]; ?></strong>
                                <?php } else if ($initialComment[$i]['m_type'] == "system" ) {
                                    echo "<i class='fa fa-exclamation' aria-hidden='true'></i>".$initialComment[$i]['message'];
                                } else {
                                    echo $initialComment[$i]['message'];
                                } ?>
                                </p>
                                </div>
                                </li>
                            <?php } ?>
                            </ol>
                            <div id="flash"></div>
                        </div>
                        <?php if ($data['status'] != "COMPLETED") { ?>
                            <div class="row with-margin">
                                <div class="col-lg-12">
                                <form  method="post" name="form" action="">
                                    <div class="input-group input-group-lg">
                                    <?php $users->getProfileImage($user_id, "50"); ?> 
                                    <input type='text' name="content" id="content" class="form-control input-lg" placeholder="Enter your message here..." />
                                    <input type='hidden' name="user_r_id" id="user_r_id" value="<?php echo $user_r_id; ?>" />
                                    <input type='hidden' name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
                                    <input type='hidden' name="post_id" id="post_id" value="<?php echo $data['ref']; ?>" />
                                    <input type='hidden' name="m_type" id="m_type" value="text" />
                                    <span class="input-group-btn">

                                    <input type="button" value="Post"  id="post" class="btn purple-bn pd btn-lg" name="post"/>
                                    </span>
                                    </div><!-- /input-group -->
                                </form>
                                </div><!-- /.col-lg-6 -->
                            </div><!-- /.row -->
                        <?php } ?>

                        <script type="text/javascript">
                            var ref='<?php echo $data['ref'];?>';
                            var user_id='<?php echo $user_id;?>';
                            var user_r_id='<?php echo $user_r_id;?>';
                            var auto_refresh = setInterval(function () {
                            var b=$("ol#update li:last").attr("id");
                            $.getJSON("<?php echo URL; ?>includes/views/scripts/chat_json?post_id="+ref+"&user_id="+user_id+"&user_r_id="+user_r_id,function(data) {
                                $.each(data.posts, function(i,data) {
                                if(b != data.id) {
                                    var dataString = 'id='+ data.user;
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo URL; ?>includes/views/scripts/draw",
                                        data: dataString,
                                        cache: false,
                                        success: function(html){
                                        if (data.m_type == "system") {
                                            var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'><i class='fa fa-exclamation' aria-hidden='true'></i>     "+data.msg+"</p></div></li>";
                                        } else if (data.m_type == "negotiate_charges") {
                                            var msg = '<i class="fa fa-handshake-o" aria-hidden="true"></i><br>You have a new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['region'] ); ?>'+data.data_1+'</strong>'

                                            var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+msg+"</p></div></li>";
                                        } else {
                                            var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+data.msg+"</p></div></li>";
                                        }
                                        $(div_data).appendTo("ol#update");
                                        }
                                    });
                                }
                                });
                            });
                            }, 2000);	

                            $(document).ready(function() {
                                $('#post').click(function() {
                                    post();
                                });
                                $('#content').focus(function() {
                                    var user_id = $("#user_id").val();
                                    var post_id = $("#post_id").val();
                                    var dataString = 'user_id='+ user_id+"&post_id="+post_id;
                                    $.ajax({
                                    type: "POST",
                                    url: "<?php echo URL; ?>includes/views/scripts/markRead",
                                    data: dataString,
                                    cache: false
                                    });
                                });
                            });

                            function post() {
                                var boxval = $("#content").val();
                                var user_r_id = $("#user_r_id").val();
                                var user_id = $("#user_id").val();
                                var post_id = $("#post_id").val();
                                var m_type = $("#m_type").val();
                                var dataString = 'user_id='+ user_id + '&user_r_id=' + user_r_id + '&post_id=' + post_id + '&m_type=' + m_type + '&content=' + boxval;

                                if(boxval.length > 0) {
                                    $("#flash").show();
                                    $("#flash").fadeIn(400).html('<img src="<?php echo URL."img/loading.gif"; ?>" align="absmiddle">&nbsp;<span class="loading">Loading Update...</span>');
                                    $.ajax({
                                    type: "POST",
                                    url: "<?php echo URL; ?>includes/views/scripts/chatajax",
                                    data: dataString,
                                    cache: false,
                                    success: function(html){
                                        $("ol#update").append(html);

                                        $('#content').val('');
                                        $('#content').focus();
                                        $("#flash").hide();
                                    }

                                    });
                                }
                                return false;
                            }

                            $(document).on('keypress', 'form input[type="text"]', function(e) {
                                if(e.which == 13) {
                                    e.preventDefault();
                                    post();
                                    return false;
                                }
                            });
                        </script>
                    <?php } ?>
                    
                    <div class="moba-line m-3"></div>
                        
                </div>
            </div>
            </div>
        <?php 
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
                <a href="<?php echo $this->seo($jobs[$i]['ref'], "view"); ?>" class="btn purple-bn1">Open</a>
                
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
          <table class="table table-striped">
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
          <table class="table table-striped">
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
            if ($text == "post_messages") {
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
