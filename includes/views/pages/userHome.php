<?php
    class userHome extends common {
        public function navigationBar($redirect) { ?>
          <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect; ?>/all"><b>List All</b></a></p>
          <?php if ($_SESSION['users']['user_type'] == 1) { ?>
          <div class="moba-line my-2"></div>
          <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/available"; ?>"><b>Currently Available</b></a></p>
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
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect; ?>"><b>Notifications</b></a></p>
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/inbox"; ?>"><b>Messages</b></a></p>
        <?php }

        public function requestPageContentpublic($ref, $view, $redirect, $data=false) {
            $this->showAll($ref, $view, $redirect);
        }

        public function pageContent($redirect, $id=false, $type=false) {
            if ($redirect == "requestDetails") {
                $this->requestDetails($id);
            } else if ($redirect == "homeSelect") {
                $this->homeSelect();
            } else if ($redirect == "homeList") {
                $this->homeCatList();
            } else if ($redirect == "category") {
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

        private function homeSelect() {
            global $pageHeader;
            global $category;
            global $country;
            $loc = $country->getLoc($_SESSION['location']['code']);

            $list = $category->categoryList($loc['ref'], 0);
            $pageHeader->loginStrip(true); ?>
            <div class="container-fluid p-0">
                <div class="row jcontent no-gutters">
                
                    <div class="col-lg-6 left-bg py-5">
                    
                        <div class="pdd">
                            <h2>FIND AND HIRE THE BEST PROFESSIONAL ARTISANS TODAY.</h2>
                            <p>Efficient and effectively verified workers at your fingertips.</p>
                            <form method="post" name="sentMessage" id="contactForm" action="<?php echo URL; ?>newRequest">
                                <div class="form-row">
                                    <div class="col-10">
                                        <select name="id" id="id" class="form-control" required>
                                            <option value="">Select Category to Start</option>
                                            <?php for ($i = 0; $i < count($list); $i++) { ?>  
                                                <option value="<?php echo $list[$i]['ref']; ?>"><?php echo $list[$i]['category_title']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <button type="submit" name="setLocation" class="btn purple-bn1-home mb-2"><i class="fa fa-arrow-right" aria-hidden="true"></i> Go</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 right-img"></div>
                </div>
            </div>
        <?php
        }

        private function homeCatList() { ?>
            <div class="container">
                <h4>HOW MOBA WORKS</h4>
                <div class="row">
                    <div class="col-lg-4">
                        <span>1</span><br><br>
                        <h5 class="mt-3">Describe The Task</h5>
                        <p>Select the category that best fits the service you require, click on "Request Service", describe your task and state your location.</p>
                    </div>
                    <div class="col-lg-4">
                        <span>2</span><br><br>
                        <h5 class="mt-3">Get Matched</h5>
                        <p>You will be matched with the closest available verified professional in your vicinity.</p>				
                    </div>
                    <div class="col-lg-4">
                        <span>3</span><br><br>
                        <h5 class="mt-3">Get It Done</h5>
                        <p>The verified professional gets the job done.</p>				
                    </div>
                </div>
                <div class="moba-line mt-5"></div>
            </div>
        <?php }
  
        public function showAll($ref, $view, $redirect) {
            global $request;
            global $options;
            global $country;
            global $category;
            global $users;

            $location = $_SESSION['location'];
 
            if (isset($_REQUEST['page'])) {
                $page = $_REQUEST['page'];
            } else {
                $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            $data = $request->listAllData($ref, $view, $start, $limit, $location);

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
                    <?php if ($view != "open") { ?>
                    <th scope="col">Service Provider</th>
                    <?php } ?>
                    <?php if ($view == "running") { ?>
                    <th scope="col">Start Time</th>
                    <?php } else { ?>
                    <th scope="col">Request Time</th>
                    <?php } ?>
                    <?php if ($view == "past") { ?>
                    <th scope="col">End Time</th>
                    <?php } ?>
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
                            <td><?php echo $country->getSingle( $list[$i]['region'], "currency_symbol").number_format($list[$i]['fee'], 2); ?></td>
                            <?php if ($view != "open") { ?>
                            <td><?php echo $users->listOnValue($list[$i]['client_id'], "screen_name"); ?></td>
                            <?php } ?>
                            <?php if ($view == "running") { ?>
                            <td><?php echo date(' j-m-Y h:i A', $list[$i]['start_date']); ?></td>
                            <?php } else { ?>
                            <td><?php echo date(' j-m-Y h:i A', $list[$i]['time']); ?></td>
                            <?php } ?>
                            <?php if ($view == "past") { ?>
                            <td><?php echo date(' j-m-Y h:i A', $list[$i]['end_date']); ?></td>
                            <?php } ?>
                            <td><a href="<?php echo "http://maps.google.com/maps?saddr=".$list[$i]['latitude'].",".$list[$i]['longitude']; ?>" target="_blank">Open in Maps</a></td>
                            <td><?php echo $this->get_time_stamp(strtotime($list[$i]['create_time'])); ?></td>
                            <td><?php echo $this->get_time_stamp(strtotime($list[$i]['modify_time'])); ?></td>
                            <th scope="col"><?php echo $this->urlLink($list[$i]['ref'], $list[$i]['status'], $view, $redirect, $$list[$i]['review_status']); ?></th>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php $this->pagination($page, $listCount);
        }

        private function urlLink($id, $status, $view, $redirect, $review_status=false) {
            if ($status == "OPEN") {
                return "<a href='".URL."newRequestDetails?id=".$id."' title='Continue'><i class='fas fa-forward'></i></a>&nbsp;<a href='".URL."".$redirect."/".$view."?remove&id=".$id."' onClick='return confirm(\"this action end this request, are you sure you want to continue ?\")' title='Remove'><i class='fas fa-trash-alt' style='color:red'></i></a>";
            } else if ($status == "ACTIVE") {
                return "<a href='".URL."requestDetails?id=".$id."' title='Review'><i class='fas fa-eye'></i></a>". ($review_status == 1 ? '<i class="fas fa-exclamation-triangle" style="color:#ffa500"></i>' : '');
            } else if ($status == "COMPLETED") {
                return "<a href='".URL."requestDetails?id=".$id."' title='Review'><i class='fas fa-eye'></i></a>". ($review_status == 1 ? '<i class="fas fa-exclamation-triangle" style="color:#ffa500"></i>' : '');
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
            global $request;
            global $users;
            global $rating;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php $users->getProfileImage($ref, "card-img-top my-3", 25, false); ?>
                    <?php echo $rating->drawRate(intval($rating->getRate($data['ref']))); ?>
                    <div class="moba-line my-3"></div>
                    <p><?php echo $data['about_me']; ?></p>
                    <p><b>Rating:</b> <?php echo $rating->textRate(intval($rating->getRate($data['ref']))); ?></p>
                    <?php if ($data['user_type'] == 1) { ?>
                    <p><b>Number of Tasks Completed:</b> <?php echo $request->taskCompleted($data['ref'], "client_id"); ?></p>
                    <?php } else if ($data['user_type'] != 1) { ?>
                    <p><b>Number of Hires:</b> <?php echo $request->taskCompleted($data['ref'], "user_id"); ?></p>	
                    <?php } ?>
                    <p><b>Average Response Time:</b> <?php echo $data['average_response_time']; ?></p>
                    <p><a href="<?php echo URL."edit/Image"; ?>" class="btn purple-bn pd">Modify Display Picture</a></p>
                    <?php if ($data['image_url'] != "") { ?>
                        <p><a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')"  class="btn red-bn pd">Remove Display Image</a></p>
                    <?php } ?>
                    <p><a href="<?php echo URL."profile"; ?>" class="btn purple-bn pd">Done Editing</a></p>
                </div>
                <div class="col-lg-8">
                    <h5>Update Display <Picture></Picture></h5>
                    <div class="moba-line mb-3"></div>
                    <p>Drag and Drop a file or select one<br>
                    Click on the Upload Button to upload the image<br>
                    Or remove the selected file by clicking on the 'Remove' or 'cancel' Button</p>
                    <form enctype="multipart/form-data" method="post">
                        <div class="form-group">
                            <input id="file-3" type="file" accept="image/*">
                        </div>
                    </form>
                    <div class="moba-line m-3"></div>
                        
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
                    browseClass: "btn purple-bn pd",
                    fileType: "any",
                    uploadUrl: '<?php echo URL; ?>/includes/views/scripts/imageUpload.php',
                    uploadExtraData: {id: user_id},
                    allowedFileExtensions: ['jpg', 'png', 'gif', 'jpeg'],
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
            global $request;
            global $users;
            global $rating;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php $users->getProfileImage($ref, "card-img-top my-3", 25, false); ?>
                    <?php echo $rating->drawRate(intval($rating->getRate($data['ref']))); ?>
                    <div class="moba-line my-3"></div>
                    <p><?php echo $data['about_me']; ?></p>
                    <p><b>Rating:</b> <?php echo $rating->textRate(intval($rating->getRate($data['ref']))); ?></p>
                    <?php if ($data['user_type'] == 1) { ?>
                    <p><b>Number of Tasks Completed:</b> <?php echo $request->taskCompleted($data['ref'], "client_id"); ?></p>
                    <?php } else if ($data['user_type'] != 1) { ?>
                    <p><b>Number of Hires:</b> <?php echo $request->taskCompleted($data['ref'], "user_id"); ?></p>	
                    <?php } ?>
                    <p><b>Average Response Time:</b> <?php echo $data['average_response_time']; ?></p>
                    <p><a href="<?php echo URL."edit/Image"; ?>" class="btn purple-bn pd">Modify Display Picture</a></p>
                    <?php if ($data['image_url'] != "") { ?>
                        <p><a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')"  class="btn red-bn pd">Remove Display Image</a></p>
                    <?php } ?>
                    <p><a href="<?php echo URL."profile"; ?>" class="btn purple-bn pd">Done Editing</a></p>
                </div>
                <div class="col-lg-8">
                    <h5>Upload Government ID</h5>
                    <div class="moba-line mb-3"></div>
                    <?php if ($data['verified'] == 2) { ?>
                        <p>Your account has been verified</p>
                    <?php } else if ($data['verified'] == 1) { ?>
                        <p>Your uploaded ID is currently being verified your account will be fully active once verified</p>
                    <?php } else { ?>
                        <h6 class="card-subtitle mb-2 text-muted">Drag and Drop a file or select one<br>
                        Click on the Upload Button to upload the image<br>
                        Or remove the selected file by clicking on the 'Remove' or 'cancel' Button</h6>
                        <form enctype="multipart/form-data" method="post">
                            <div class="form-group">
                                <input id="file-3" type="file" accept="png, .jpg, .jpeg, .pdf">
                            </div>
                        </form>
                    <?php } ?>
                    <div class="moba-line m-3"></div>
                        
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
                    browseClass: "btn purple-bn pd",
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
            global $request;
            global $users;
            global $rating;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php $users->getProfileImage($ref, "card-img-top my-3", 25, false); ?>
                    <?php echo $rating->drawRate(intval($rating->getRate($data['ref']))); ?>
                    <div class="moba-line my-3"></div>
                    <p><?php echo $data['about_me']; ?></p>
                    <p><b>Rating:</b> <?php echo $rating->textRate(intval($rating->getRate($data['ref']))); ?></p>
                    <?php if ($data['user_type'] == 1) { ?>
                    <p><b>Number of Tasks Completed:</b> <?php echo $request->taskCompleted($data['ref'], "client_id"); ?></p>
                    <?php } else if ($data['user_type'] != 1) { ?>
                    <p><b>Number of Hires:</b> <?php echo $request->taskCompleted($data['ref'], "user_id"); ?></p>	
                    <?php } ?>
                    <p><b>Average Response Time:</b> <?php echo $data['average_response_time']; ?></p>
                    <p><a href="<?php echo URL."edit/Image"; ?>" class="btn purple-bn pd">Modify Display Picture</a></p>
                    <?php if ($data['image_url'] != "") { ?>
                        <p><a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')"  class="btn red-bn pd">Remove Display Image</a></p>
                    <?php } ?>
                    <p><a href="<?php echo URL."profile"; ?>" class="btn purple-bn pd">Done Editing</a></p>
                </div>
                <div class="col-lg-8">
                    <h5>Update Username</h5>
                    <div class="moba-line mb-3"></div>

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
                    <div class="moba-line m-3"></div>
                        
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
            global $request;
            global $users;
            global $rating;
            global $category;
            global $usersKin;
            global $country;
            
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            $loc = $country->getLoc($_SESSION['location']['code']);
            $list = $category->categoryList($loc['ref'], 0);
            $myList = explode(",", $users->getCatNameList($ref, false));
            $getKin = $usersKin->listOneKin($ref);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php $users->getProfileImage($ref, "card-img-top my-3", 25, false); ?>
                    <?php echo $rating->drawRate(intval($rating->getRate($data['ref']))); ?>
                    <div class="moba-line my-3"></div>
                    <p><?php echo $data['about_me']; ?></p>
                    <p><b>Rating:</b> <?php echo $rating->textRate(intval($rating->getRate($data['ref']))); ?></p>
                    <?php if ($data['user_type'] == 1) { ?>
                    <p><b>Number of Tasks Completed:</b> <?php echo $request->taskCompleted($data['ref'], "client_id"); ?></p>
                    <?php } else if ($data['user_type'] != 1) { ?>
                    <p><b>Number of Hires:</b> <?php echo $request->taskCompleted($data['ref'], "user_id"); ?></p>	
                    <?php } ?>
                    <p><b>Average Response Time:</b> <?php echo $data['average_response_time']; ?></p>
                    <p><a href="<?php echo URL."edit/Image"; ?>" class="btn purple-bn pd">Modify Display Picture</a></p>
                    <?php if ($data['image_url'] != "") { ?>
                        <p><a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')"  class="btn red-bn pd">Remove Display Image</a></p>
                    <?php } ?>
                    <p><a href="<?php echo URL."profile"; ?>" class="btn purple-bn pd">Done Editing</a></p>
                </div>
                <div class="col-lg-8">
                    <h5>Update Profile</h5>
                    <div class="moba-line mb-3"></div>

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
                            <label for="email" class="col-sm-2 col-form-label">Email Name</label>
                            <div class="col-sm-10">
                            <input type="text" class="form-control" id="email" name="email" disabled value="<?php echo $data['email']; ?>">
                            </div>
                        </div>
                        <?php if ($data['user_type'] == 1) { ?>
                            <div class="form-group row">
                                <label for="mobile_number" class="col-sm-2 col-form-label">Mobile number</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="<?php echo $data['mobile_number']; ?>" required placeholder="Enter Mobile Number">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="street" class="col-sm-2 col-form-label">Street Address</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="street" name="street" value="<?php echo $data['street']; ?>" required placeholder="Enter street address">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="city" class="col-sm-2 col-form-label">City</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo $data['city']; ?>" required placeholder="Enter city eg. Ikeja">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="state" class="col-sm-2 col-form-label">State</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo $data['state']; ?>" required placeholder="Enter your state Eg. Lagos">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="last_name" class="col-sm-2 col-form-label">Selected Services</label>
                                <div class="col-sm-10">
                                <select class="form-control" id="category_select" name="category_select[]" required multiple>
                                    <?php for ($i = 0; $i < count($list); $i++) { ?>
                                    <option <?php if (in_array($list[$i]['ref'], $myList)) { ?>selected<?php } ?> value="<?php echo $list[$i]['ref']; ?>"><?php echo ucfirst(strtolower($list[$i]['category_title'])); ?></option>
                                    <?php } ?>
                                </select>
                                </div>
                            </div>
                            <h5>Next of Kin</h3>
                            <div class="form-group row">
                                <label for="kin_name" class="col-sm-2 col-form-label">Name</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="kin_name" name="kin_name" value="<?php echo $getKin['kin_name']; ?>" required placeholder="Enter Last name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kin_email" class="col-sm-2 col-form-label">Email Address</label>
                                <div class="col-sm-10">
                                <input type="email" class="form-control" id="kin_email" name="kin_email" value="<?php echo $getKin['kin_email']; ?>" required placeholder="Enter Last name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kin_phone" class="col-sm-2 col-form-label">Phone Number</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="kin_phone" name="kin_phone" value="<?php echo $getKin['kin_phone']; ?>" required placeholder="Enter Last name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kin_relationship" class="col-sm-2 col-form-label">Relationship</label>
                                <div class="col-sm-10">
                                <input type="text" class="form-control" id="kin_relationship" name="kin_relationship" value="<?php echo $getKin['kin_relationship']; ?>" required placeholder="Enter Last name">
                                </div>
                            </div>
                        <?php } ?>
                        <input type="hidden" id="country" name="country" readonly value="<?php echo $data['country']; ?>">
                        <input type="hidden" id="screen_name" name="screen_name" readonly value="<?php echo $data['screen_name']; ?>">
                        <input type="hidden" name="ref" value="<?php echo $data['ref']; ?>">
                        <button type="submit" name="updateProfile" class="btn purple-bn1">Update Profile</button>
                    </form>
                    <div class="moba-line m-3"></div>
                        
                </div>
            </div>
            </div>
            <?php
        }

        private function editPasswordPage() {
            global $users;
            global $rating;
            global $request;
            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            ?>

            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php $users->getProfileImage($ref, "card-img-top my-3", 25, false); ?>
                    <?php echo $rating->drawRate(intval($rating->getRate($data['ref']))); ?>
                    <div class="moba-line my-3"></div>
                    <p><?php echo $data['about_me']; ?></p>
                    <p><b>Rating:</b> <?php echo $rating->textRate(intval($rating->getRate($data['ref']))); ?></p>
                    <?php if ($data['user_type'] == 1) { ?>
                    <p><b>Number of Tasks Completed:</b> <?php echo $request->taskCompleted($data['ref'], "client_id"); ?></p>
                    <?php } else if ($data['user_type'] != 1) { ?>
                    <p><b>Number of Hires:</b> <?php echo $request->taskCompleted($data['ref'], "user_id"); ?></p>	
                    <?php } ?>
                    <p><b>Average ResponseTime:</b> <?php echo $data['average_response_time']; ?></p>
                    <p><a href="<?php echo URL."edit/Image"; ?>" class="btn purple-bn pd">Modify Display Picture</a></p>
                    <?php if ($data['image_url'] != "") { ?>
                        <p><a href="<?php echo URL."edit/Image?removeImage"; ?> "onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')"  class="btn red-bn pd">Remove Display Image</a></p>
                    <?php } ?>
                    <p><a href="<?php echo URL."profile"; ?>" class="btn purple-bn pd">Done Editing</a></p>
                </div>
                <div class="col-lg-8">
                    <h5>Update Password</h5>
                    <div class="moba-line mb-3"></div>
                    <form enctype="multipart/form-data" method="post">
                        <div class="form-group row">
                            <label for="old_password" class="col-sm-2 col-form-label">Old Password</label>
                            <div class="col-sm-10"><span id="sprytextfield1">
                                <input type="password" class="form-control" id="old_password" name="old_password" value="" placeholder="Enter current Password" required />
                            <span class="textfieldRequiredMsg">A value is required.</span></span></div>
                        </div>
                        <div class="form-group row">
                            <label for="new_password" class="col-sm-2 col-form-label">Last New Password</label>
                            <div class="col-sm-10">
                                <span id="sprypassword1">
                                    <input type="password" class="form-control" id="new_password" name="new_password" value="" placeholder="Enter new Password" required />
                                    <span class="passwordRequiredMsg">A value is required.
                                    </span>
                                    <span class="passwordInvalidStrengthMsg">The password doesn't meet the specified strength.</span>
                                </span>

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
                            
                        </div>
                        <div class="form-group row">
                            <label for="confirm_new" class="col-sm-2 col-form-label">Confirm New Password</label>
                            <div class="col-sm-10"><span id="spryconfirm1">
                                <input type="password" class="form-control" id="confirm_new" name="confirm_new" value="" placeholder="Confirm New Password" required />
                            <span class="confirmRequiredMsg">A value is required.</span><span class="confirmInvalidMsg">The values don't match.</span></span>
                        </div>
                        <input type="hidden" name="ref" value="<?php echo $data['ref']; ?>">
                        <button type="submit" name="updatePassword" class="btn purple-bn1">Update Password</button>
                    </form>
                    <div class="moba-line m-3"></div>
                        
                </div>
            </div>
            </div>
            
            <script type="text/javascript">
                var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
                var sprypassword1 = new Spry.Widget.ValidationPassword("sprypassword1", {minAlphaChars:1, minNumbers:1, minUpperAlphaChars:1, validateOn:["change"]});
                var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryconfirm1", "new_password");

                $(document).ready(function() {
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
                $jobData = $search->aroundMeData($_SESSION['location'], $start, $limit);
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
                <a href="<?php echo $this->seo($data[$i]['ref'], "view"); ?>"><h5 class="mt-0 mb-1"><?php echo $data[$i]['project_name']; ?></h5></a>
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

        private function homeCategory() {
            global $country;
            global $category;
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
        
        private function requestDetails($id) {
            global $request;
            global $category;
            global $country;
            global $rating;
            global $rating_comment;
            global $rating_question;
            global $users;
            global $messages;
            global $media;
            $data = $request->listOne($id);
            $getAlbum = $media->getAlbum($data['ref']);
            $categoryData = $category->listOne($data['category_id']);
            $countryData = $country->listOne($data['region'], "ref");

            if ($_SESSION['users']['ref'] == $data['user_id']) {
                $tagline = "client_id";
                $user_r_id = $data['client_id'];
                $user_id = $data['user_id'];
                $rateType = "vendors";
            } else {
                $tagline = "user_id";
                $user_r_id = $data['user_id'];
                $user_id = $data['client_id'];
                $rateType = "clients";
            }
            $initialComment = $messages->getPage($data['ref'], $user_r_id, $user_id);


            if ($data['status'] == "COMPLETED") {
                $checkRate = $rating->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "post_id", $data['ref']);
                $checkComment = $rating_comment->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "post_id", $data['ref'], "ref", "ASC", "AND", false, false, "getRow");

                $rateQuestion = $rating_question->getSortedList($rateType, "question_type");
            }
            ?>
            <div class="container my-5">
                <div class="row py-5">	
                    <div class="col-lg-4">
                        <img class="card-img-top my-3" src="<?php echo $category->getIcon( $categoryData['ref'] ); ?>" alt="">
                        <?php if (count($getAlbum) > 0) {
                            for ($i = 0; $i < count($getAlbum); $i++) { ?>
                                <a data-fancybox="gallery" href="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>"><img src="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:70px;"></a>
                            <?php }
                        } ?>
                        <div class="moba-line my-3"></div>
                        <p><?php echo $data['description']; ?></p>
                        <p><b>Started:</b><?php echo $this->get_time_stamp( $data['start_date'] ); ?></p>
                        <?php if ($data['status'] == "COMPLETED") { ?>
                            <p><b>Ended:</b><?php echo $this->get_time_stamp( $data['end_date'] ); ?></p>
                        <?php } ?>
                        <p><b>Client:</b> <?php echo $users->listOnValue($data['user_id'], "screen_name"); ?></p>
                        <p><b>Service Provider:</b> <?php echo $users->listOnValue($data['client_id'], "screen_name"); ?></p>
                        <p><b>Address:</b> <?php echo $data['address']; ?></p>
                        <p><b>Amount Charged:</b> <?php echo $countryData['currency_symbol']." ".number_format($data['fee'], 2); ?></p>
                        <?php if ($data['status'] != "COMPLETED") { ?>
                            <?php if ($data['client_id'] == $_SESSION['users']['ref']) {
                                if ($data['review_status'] == 1) { ?>
                                    <p>You requested a review <?php echo $this->get_time_stamp($data['review_status_time']); ?></p>
                                <?php } else { ?>
                                    <p><a href="<?php echo URL."requestDetails?id=".$data['ref']."&request_approve"; ?>" id="approve_ad" class="btn purple-bn pd" title='Request Review'><i class="fas fa-exclamation-triangle" style="color:#ffa500"></i>&nbsp;<i class="fas fa-clipboard-check"></i>&nbsp;&nbsp;Request Review</a></p>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($data['review_status'] == 1) { ?>
                                <p><i class="fas fa-exclamation-triangle" style="color:#ffa500"></i>&nbsp;Your attention is needed, please check your messages</p>
                            <?php } ?>
                            <?php if ($data['user_id'] == $_SESSION['users']['ref']) { ?>
                                <p><a href="<?php echo URL."requestDetails?id=".$data['ref']."&approve"; ?>" id="approve_ad" class="btn purple-bn pd" title='Mark as Completed'><i class="fas fa-clipboard-check"></i>&nbsp;&nbsp;Mark as Completed</a></p>
                            <?php } ?>
                            <p><a href="<?php echo URL."ads/all?remove&id=".$data['ref']; ?>" class="btn red-bn pd" onClick='return confirm("this task will be flagged, do you want to continue ?")' title='Report'><i class="fas fa-bell"></i>&nbsp;&nbsp;Report</a></p>
                        <?php } ?>
                    </div>
                    <div class="col-lg-8">			
                        <h5><?php echo $category->getSingle( $data['category_id'] ); ?></h5>
                        <div class="moba-line mb-3"></div>
                        <?php if ($data['status'] == "COMPLETED") { ?>
                            <h2>Review And Comment</h2>
                            <?php if (count($checkRate) > 0) { ?>
                                <?php for ($i = 0; $i < count($checkRate); $i++) { ?>
                                    <strong><?php echo $rating_question->getSingle( $checkRate[$i]['question_id'] ); ?></strong><br>
                                    <?php echo $rating->drawRate($checkRate[$i]['review']); ?><br><br>
                                <?php } ?>
                                <p><strong>Comments</strong><br>
                                <?php echo $checkComment['comment']; ?><p>
                            <?php } else { ?>
                                <p>Kindly rate <?php echo $users->listOnValue($user_r_id, "screen_name"); ?> performance on the following headings:</p>
                                <form method="post" action="<?php echo URL."requestDetails?id=".$data['ref']; ?>">
                                    <input name="user_id" type="hidden" value="<?php echo $user_id; ?>">
                                    <input name="reviewed_by" type="hidden" value="<?php echo $user_r_id; ?>">
                                    <input name="post_id" type="hidden" value="<?php echo $data['ref']; ?>">
                                    <?php for ($i = 0; $i < count($rateQuestion); $i++) { ?>
                                    <p>
                                    <strong><?php echo $rateQuestion[$i]['question']; ?></strong><br>
                                    <fieldset class="rating form-group">
                                    <input type="radio" id="star5<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="5" /><label for="star5<?php echo $rateQuestion[$i]['ref']; ?>" title="Rocks!">5 stars</label>
                                    <input type="radio" id="star4<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="4" /><label for="star4<?php echo $rateQuestion[$i]['ref']; ?>" title="Pretty good">4 stars</label>
                                    <input type="radio" id="star3<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="3" /><label for="star3<?php echo $rateQuestion[$i]['ref']; ?>" title="Meh">3 stars</label>
                                    <input type="radio" id="star2<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="2" /><label for="star2<?php echo $rateQuestion[$i]['ref']; ?>" title="Kinda bad">2 stars</label>
                                    <input type="radio" id="star1<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="1" /><label for="star1<?php echo $rateQuestion[$i]['ref']; ?>" title="Sucks big time">1 star</label>
                                    </fieldset>
                                    <br><br>
                                    </p>
                                    <?php } ?>
                                    <div class="form-group">
                                    <label for="comment">Comments</label>
                                    <small>Optional</small>
                                    <textarea name="comment" id="comment" class="form-control" placeholder="Optional comment"></textarea>
                                    </div>
                                    <input type="hidden" name="type" value="<?php echo $tagline; ?>">
                                    <button type="submit" name="saveRate" class="purple-bn">Rate</button>
                                </form>
                            <?php } ?>
                        <?php } ?>

                        <div class="moba-line mb-3"></div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="map" style="height:500px;width:100%;"></div><br<br>
                            </div>
                        </div>
                        <div class="moba-line m-3"></div>
                        <h5><a name="messages"></a>Messages with <?php echo $users->listOnValue($user_r_id, "screen_name"); ?></h5>
                        <small>Average Response Time:</b> <?php echo $users->listOnValue($user_r_id, 'average_response_time'); ?></small>
                        <div class="moba-line mb-3"></div>
                        <div class="row">
                            <ol id="update" >
                            <?php for ($i = 0; $i < count($initialComment); $i++) { ?>
                                <li class="media" id='<?php echo $initialComment[$i]['ref']; ?>'>
                                    <?php $users->getProfileImage($initialComment[$i]['user_id'], "mr-3", false); ?>
                                    <div class="media-body">
                                        <small class="time"><i class="fa fa-clock-o"></i> <?php echo $this->get_time_stamp(strtotime($initialComment[$i]['create_time'])); ?></small>
                                        <p class="mt-0">
                                            <?php if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                                                $m_type_data = explode("_", $initialComment[$i]['m_type_data'] ) ?>
                                                <i class="fa fa-handshake" aria-hidden="true"></i><br><?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>You have a <?php } ?>new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['region'], "currency_symbol", "ref" )." " .$m_type_data[0]; ?></strong>
                                                <?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>
                                                <p><a href="<?php echo $this->seo($user_id, "profile").$m_type_data[1]."/negotiate?respond=".$user_id; ?>" class="btn purple-bn pd" title='Remove'><i class="fas fa-tags"></i>&nbsp;&nbsp;Respond to Negotiation</a></p><?php } ?>
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
                                    <?php $users->getProfileImage($user_id, "", false); ?> 
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
                                            var msg = '<i class="fa fa-handshake-o" aria-hidden="true"></i><br>You have a new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['region'], "currency_symbol", "ref" ); ?>'+data.data_1+'</strong><br><p><a href="<?php echo $this->seo($user_id, "profile"); ?>'+data.data_2+'<?php echo "/negotiate?respond=".$user_id; ?>" class="btn purple-bn pd" title=\'Remove\'><i class="fas fa-tags"></i>&nbsp;&nbsp;Respond to Negotiation</a></p>';

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
                    </div>
                    
                </div>
            </div>
            <script type="text/javascript" src="<?php echo URL; ?>js/imageUpload.js"></script>
            <script type="text/javascript" src="<?php echo URL; ?>js/places.js"></script>
            
            <script type="text/javascript">
                function initMap() {
                var marker = {lat: <?php echo $data['latitude']; ?>, lng: <?php echo $data['longitude']; ?>};
                var map = new google.maps.Map(
                    document.getElementById('map'), {zoom: 15, center: marker});
                var marker = new google.maps.Marker({position: marker, map: map});
                }
            </script>
            <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GoogleAPI; ?>&callback=initMap"></script>
        <?php
        }

        private function categoryHomePage($id, $type) {
            global $category;
            global $categoryQuestion;
            global $country;
            global $rating;
            global $users;
            global $wallet;
            $data = $category->listOne($id);
            $userData = $users->listOne(trim($_SESSION['users']['ref']));
            $countryData = $country->listOne($data['country'], "ref");
            $usersData = $category->totalUsers($id, $_SESSION['location']);
            $question = $categoryQuestion->getSortedList($id, "category_id");
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <img class="card-img-top my-3" src="<?php echo $category->getIcon( $data['ref'] ); ?>" alt="">
                    <i class="fa fa-clock-o"></i> <?php echo $this->numberPrintFormat(count($usersData)); ?>  
                    <span class="float-right"><i class="fa fa-map-marker"></i> <?php echo $_SESSION['location']['city'].", ".$_SESSION['location']['country']; ?></span>
                    
                    
                    <div class="moba-line my-3"></div>
                    <p><b>Callout Charge:</b> <?php echo $countryData['currency_symbol']." ".number_format($data['call_out_charge'], 2); ?></p>		
                    
                </div>
                <div class="col-lg-8">			
                    <h5><?php echo $data['category_title']; ?></h5>
                    <div class="moba-line mb-3"></div>
                    <?php if ($userData['user_type'] == 1) { ?>
                        <div class="alert alert-warning" role="alert">
                            <strong>You can not request for a service with a service provider account</strong>
                        </div>
                    <?php } else { ?>
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
                            
                            $check = true;
                            if ($check) { ?>
                                <form name="sentMessage" method="post" id="contactForm" action="<?php echo URL; ?>newRequest" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $data['ref']; ?>">
                                    <?php for ($i = 0; $i < count($question); $i++) { ?>
                                    <div class="form-group">
                                        <label for="data<?php echo $i; ?>" class="form-text text-muted"><?php echo $question[$i]['title']; ?></label>
                                        <input type="hidden" name="data[<?php echo $i; ?>][question]" value="<?php echo $question[$i]['title']; ?>">
                                        <?php if ($question[$i]['type'] == 0) {
                                            $d = explode("\n", $question[$i]['data'] ) ?>
                                            <select name="data[<?php echo $i; ?>][answer]" id="data<?php echo $i; ?>" class="form-control editable-select" required>
                                                <?php for ($u = 0; $u < count($d); $u++) { ?>
                                                    <option value="<?php echo $d[$u]; ?>"><?php echo $d[$u]; ?></option>
                                                <?php } ?>
                                            </select>
                                            <small id="autocomplete_help" class="form-text text-muted">Select your prefered answer from the drop down, if your answer is not included, type your answer in the select box.</small>
                                        <?php } else { ?>
                                            <input type="text" name="data[<?php echo $i; ?>][answer]" id="data<?php echo $i; ?>" class="form-control" placeholder="Type Answer" required>
                                        <?php } ?>
                                    </div>
                                    <?php } ?>
                                    <div class="form-group">
                                        <label for="autocomplete" class="form-text text-muted">Enter your address</label>
                                        <input id="autocomplete" name="autocomplete" placeholder="Enter your address" required onfocus="geolocate()" type="text" class="form-control" autocomplete="false" value=""/>
                                        <input type="hidden" name="city" id="locality" value="">
                                        <input type="hidden" name="state" id="administrative_area_level_1" value="">
                                        <input type="hidden" name="postal_code" id="postal_code" value="">
                                        <input type="hidden" name="country" id="country" value="">
                                        <input type="hidden" name="lat" id="lat" value="">
                                        <input type="hidden" name="country_code" id="country_code" value="">
                                        <input type="hidden" name="lng" id="lng" value="">
                                        <small id="autocomplete_help" class="form-text text-muted">This will be the location where the service is required.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="fee" class="form-text text-muted">Price Range</label>
                                        <input type="number" name="fee" id="fee" class="form-control" placeholder="Price Range" min="<?php echo $data['call_out_charge']; ?>" required>
                                        <small id="autocomplete_help" class="form-text text-muted">This amount must be greater than or equal to <?php echo $countryData['currency_symbol']." ".number_format($data['call_out_charge'], 2); ?>.<br>
                                        Please note that the call-out charge is the minimum for each category, charges may vary depending on the service required.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="uploadFile">Include pictures with as much details as you will prefer. You can upload a maximum of 10 media with a maximum file size of 2MB. (Optional)</label>
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
                            <?php } else { ?>
                                <div class="alert alert-danger" role="alert">
                                    <strong>You must have at least one payment card saved to make a request. <a href="<?php echo URL."paymentCards/create"; ?>">Click here to add your payment card</a> then come back to create the request again</strong>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <div class="control-group form-group my-5">
                    <h5>Popular Service Providers</h5>
                    <div class="moba-line mb-3"></div>
                    <div class="row">
                        <?php if (count($usersData) > 0) { ?>
                        <?php for ($i = 0; $i < count($usersData); $i++) { ?>
                            <div class="col-lg-6">
                                <div class="moba-content__img">
                                    <?php $users->getProfileImage($usersData[$i]['user_id'], "mr-3 float-left"); ?>
                                    <small><?php echo $usersData[$i]['screen_name']; ?></small><br>
                                    Reponds in <?php echo $usersData[$i]['average_response_time']; ?><br>
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
            <script src="//rawgithub.com/indrimuska/jquery-editable-select/master/dist/jquery-editable-select.min.js"></script>
            <link href="//rawgithub.com/indrimuska/jquery-editable-select/master/dist/jquery-editable-select.min.css" rel="stylesheet">
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GoogleAPI; ?>&libraries=places&callback=initAutocomplete" async defer></script>
            <script type="text/javascript">
                $('.editable-select').editableSelect();
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
            global $users;
            global $request_accept;
            $data = $request->listOne($id);
            $extraData = unserialize($data['data']);
            $categoryData = $category->listOne($data['category_id']);
            $countryData = $country->listOne($data['region'], "ref");
            $loc = $this->googleGeoLocation($data['longitude'], $data['latitude']);

            $addressData['latitude'] = $data['latitude'];
            $addressData['longitude'] = $data['longitude'];
            $addressData['state_code'] = $loc['province_code'];
            $addressData['state'] = $loc['province'];
            $addressData['code'] = $loc['country_code'];
            $addressData['country'] = $loc['country'];

            $result = $request_accept->getResponse($data['ref']);
            //echo "<pre>";
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <img class="card-img-top my-3" src="<?php echo $category->getIcon( $categoryData['ref'] ); ?>" alt="">
                    <i class="fa fa-map-marker"></i> <?php echo $data['address']; ?>
                    <div class="moba-line my-3"></div>
                    <p><b>Job Category:</b> <?php echo $categoryData['category_title']; ?></p>	
                    <?php for ($u = 0; $u < count($extraData); $u++) { ?>
                    <p><b><?php echo $extraData[$u]['question']; ?><br></b> <?php echo $extraData[$u]['answer']; ?></p>
                    <?php } ?>
                    <p><b>Callout Charge:</b> <?php echo $countryData['currency_symbol']." ".number_format($data['fee'], 2); ?></p>
                    <p><a href="<?php echo URL."ads/all?remove&id=".$id; ?>" class="btn red-bn pd" onClick='return confirm("this action end this request, are you sure you want to continue ?")' title='Remove'><i class="fas fa-trash-alt"></i>&nbsp;&nbsp;Delete</a></p>	
                    
                </div>
                <div class="col-lg-8" id="messageData">			
                    <h5>Request Response for '<strong><?php echo $categoryData['category_title']; ?></strong></h5>
                    <div class="moba-line mb-3"></div>
                    <p>Your request for <strong><?php echo $categoryData['category_title']; ?></strong> at <strong><?php echo $loc['address']; ?></strong> has <strong><?php echo intval(count($result)); ?></strong> <?php echo $this->addS("response", intval(count($result))); ?></p>
                    <div class="row">
                        <?php if (count($result) > 0) { ?>
                            <?php for ($i = 0; $i < count($result); $i++) {
                                $mapData = $this->googleDirection($addressData, $result[$i]); ?>
                                <div class="col-lg-4">
                                    <div class="moba-content__img">
                                    <div class="row">
                                        <div class="col-lg-4">
                                        <?php echo $users->getProfileImage($result[$i]['ref'], "mr-3 float-left", 250); ?>
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

            <script>
            var auto_refresh = setInterval(function () {
                var url = '<?php echo URL; ?>';
                var dataString = "request=<?php echo $data['ref']; ?>&category_title=<?php echo $categoryData['category_title']; ?>&address=<?php echo $loc['address']; ?>&latitude=<?php echo $addressData['latitude']; ?>&longitude=<?php echo $addressData['longitude']; ?>&state_code=<?php echo $addressData['state_code']; ?>&state=<?php echo $addressData['state']; ?>&code=<?php echo $addressData['code']; ?>&country=<?php echo $addressData['country']; ?>";
                $.ajax({
                    type: "POST",
                    url: url+"includes/views/scripts/messageRequest",
                    data: dataString,
                    cache: false,
                    success: function(html){
                        $('#messageData').html('');
                        $(html).appendTo("#messageData");
                    }
                });
            }, 10000);
            </script>
        <?php 
        }

        private function requestProfile($array) {
            global $request;
            global $request_negotiate;
            global $category;
            global $country;
            global $rating;
            global $users;
            global $rating_question;
            global $messages;
            global $media;
            $extraData = array();
            $getAlbum = array();
            $data = $request->listOne($array[2]);
            $extraData = unserialize($data['data']);
            $categoryData = $category->listOne($data['category_id']);
            $countryData = $country->listOne($data['region'], "ref");
            $usersData = $users->listOne($array[0]);
            $userData = $users->listOne(trim($_SESSION['users']['ref']));

            $getAlbum = $media->getAlbum($data['ref']);
            //if ($_SESSION['users']['ref'] == )
            $user_r_id = $usersData['ref'];
            $user_id = trim($_SESSION['users']['ref']);

            $checkRate = $rating_question->getSortedList("vendors", "question_type");
            //respond to a request
            $checkCurrentRequest = $request_negotiate->checkCurrent(array("post_id" => $data['ref'], "user" => $user_r_id, "user_r" => $user_id));
            $currentData = $request_negotiate->checkCurrent(array("post_id" => $data['ref'], "user" => $user_r_id, "user_r" => $user_id), "getRow");
            //sent a request
            $checkRequest = $request_negotiate->checkCurrent(array("post_id" => $data['ref'], "user" => $user_id, "user_r" => $user_r_id));
            $requestData = $request_negotiate->checkCurrent(array("post_id" => $data['ref'], "user" => $user_id, "user_r" => $user_r_id), "getRow");
            
            if (isset ($_REQUEST['respond'])) {
                $requestData = $request_negotiate->listOne($_REQUEST['respond']);
            }
            // echo "<pre>";
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php echo $users->getProfileImage($usersData['ref'], "card-img-top my-3", 250, false); ?>
                    <i class="fa fa-map-marker"></i> <?php echo $data['address']; ?>
                    <div class="moba-line my-3"></div>
                    <?php if (count($getAlbum) > 0) {
                        for ($i = 0; $i < count($getAlbum); $i++) { ?>
                            <a data-fancybox="gallery" href="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>"><img src="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:70px;"></a>
                        <?php }
                    } ?>
                    <p><b>Job Category:</b> <?php echo $categoryData['category_title']; ?></p>	
                    <?php for ($u = 0; $u < count($extraData); $u++) { ?>
                    <p><b><?php echo $extraData[$u]['question']; ?><br></b> <?php echo $extraData[$u]['answer']; ?></p>
                    <?php } ?>
                    <p><b>Callout Charge:</b> <?php echo $countryData['currency_symbol']." ".number_format($data['fee'], 2); ?></p>
                    <?php if ($user_id != $usersData['ref']) { ?>
                        <?php if ($data['status'] == "OPEN") { ?>
                            <?php if ($array[3] != "respond") { ?>
                                <?php if ($checkCurrentRequest == 1) { ?>
                                    <p><a href="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/negotiate?respond=".$currentData['ref']; ?>" class="btn purple-bn pd" title='Remove'><i class="fas fa-tags"></i>&nbsp;&nbsp;Respond to Negotiation</a></p>
                                <?php } else if (($userData['user_type'] == "1") && ($checkRequest == 1)) { ?>
                                    <p><a href="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/negotiate?cancel=".$requestData['ref']; ?>" class="btn red-bn pd"  onClick='return confirm("this action cancel this request, are you sure you want to continue ?")' title='Remove'><i class="fas fa-tags"></i>&nbsp;&nbsp;Cancel Negotiation Request</a></p>
                                <?php } else if ($userData['user_type'] == "1") { ?>
                                    <p><a href="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/negotiate"; ?>" class="btn purple-bn pd" onClick='' title='Remove'><i class="fas fa-tags"></i>&nbsp;&nbsp;Negotiate Fee</a></p>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                        <?php if ($array[3] == "view") { ?>
                            <p><a href="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/message"; ?>" class="btn purple-bn pd" title='Message'><i class="fas fa-comments"></i>&nbsp;&nbsp;Message</a></p>
                        <?php } else { ?>
                            <p><a href="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/view"; ?>" class="btn purple-bn pd" title='Message'><i class="fas fa-eye"></i>&nbsp;&nbsp;View Profile</a></p>
                        <?php } ?>
                        <?php if ($data['status'] == "OPEN") { ?>
                            <?php if ($data['user_id'] == $user_id) { ?>
                                <p><a href="<?php echo URL."newRequestDetails?id=".$data['ref']; ?>" class="btn red-bn pd" title='Go Back'><i class="fas fa-backward"></i>&nbsp;&nbsp;Back To Request Result</a></p>
                                <p><a href="<?php echo URL."ads/all?remove&id=".$data['ref']; ?>" class="btn red-bn pd" onClick='return confirm("this action end this request, are you sure you want to continue ?")' title='Remove'><i class="fas fa-trash-alt"></i>&nbsp;&nbsp;Delete</a></p>
                                <p><a id="approve_ad" class="btn purple-bn pd" title='Message'><i class="fas fa-play"></i>&nbsp;&nbsp;Approve Request</a></p>
                                <small>Click twice. By clicking twice, you inidcate that you have agreed to the terms and conditions as outlined in our terms and conditions document</small>
                            <?php } ?>
                        <?php } ?>
                    <?php } else { ?>
                        <p><a href="<?php echo URL."newRequestDetails?id=".$data['ref']; ?>" class="btn red-bn pd" title='Go Back'><i class="fas fa-backward"></i>&nbsp;&nbsp;Back To Requests</a></p>
                    <?php } ?>
                    
                </div>
                <div class="col-lg-8">
                    <?php if ($array[3] == "negotiate") { ?>
                        <h5>Negotiate Payment</h5>
                        <form  method="post" name="form" action="">
                            <?php if (isset ($_REQUEST['respond'])) { ?>
                                <p><?php echo $users->listOnValue($requestData['user_id'], "screen_name");  ?> wants to review the fees for this service</p>
                                <p>Current Fee<br>
                                <strong><?php echo $countryData['currency_symbol']." ".number_format($data['fee'], 2); ?></strong>
                                </p>
                                <p>Proposed Fee<br>
                                <strong><?php echo $countryData['currency_symbol']." ".number_format($requestData['amount'], 2); ?></strong>
                                </p>
                                <input type="button" value="Approve"  id="negotiate" class="btn purple-bn1" onclick='location="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/negotiate?n_answer=y&approve=".$requestData['ref']; ?>"' name="negotiate"/>
                                <input type="button" value="Reject"  id="negotiate" class="btn red-bn1" onclick='location="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/negotiate?n_answer=n&approve=".$requestData['ref']; ?>"'/>
                            <?php } else { ?>
                                <div class="form-group">
                                    <input type='number' step="0.01" name="negotiated_fee" id="negotiated_fee" class="form-control input-lg" value="<?php echo $request->getFee(array("post_id" => $data['ref'], "user" => $user_id, "user_r" => $user_r_id)); ?>" required min="<?php echo  $categoryData['call_out_charge']; ?>"/>
                                    <input type='hidden' name="user_r_id" id="user_r_id" value="<?php echo $user_r_id; ?>" />
                                    
                                    <input type='hidden' name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
                                    <input type='hidden' name="post_id" id="post_id" value="<?php echo $data['ref']; ?>" />
                                    
                                    <input type='hidden' name="m_type" id="m_type_2" value="negotiate_charges" />
                                </div>
                                <input type="submit" disabled value="Negotiate"  id="negotiate" class="btn purple-bn1" name="negotiate"/>
                            <?php } ?>
                        </form>
                    <?php } elseif ($array[3] == "view") { ?>
                        <h5><?php echo $usersData['screen_name']; ?></h5>
                        <div class="moba-line mb-3"></div>
                        <p><?php echo $usersData['about_me']; ?></p>
                        <?php echo $rating->drawRate(intval($rating->getRate($usersData['ref']))); ?>
                        <p><small>Number of Tasks Completed</small><br>
                        <strong><?php echo $request->taskCompleted($data['ref'], "user_id"); ?></strong>
                        <small> Average Response Time</small><br>
                        <strong><?php echo $usersData['ref']; ?></strong><br>
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
                    <?php } else if ($array[3] == "respond") { ?>
                        <h5>Respond to Request</h5>
                        <p><b>Job Location:</b><br><?php echo $data['address']; ?></p>
                        <div class="moba-line my-3"></div>
                        <?php if (count($getAlbum) > 0) {
                            for ($i = 0; $i < count($getAlbum); $i++) { ?>
                                <a data-fancybox="gallery" href="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>"><img src="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:70px;"></a>
                            <?php } ?>
                            <div class="moba-line my-3"></div>
                        <?php } ?>
                        <?php for ($u = 0; $u < count($extraData); $u++) { ?>
                        <p><b><?php echo $extraData[$u]['question']; ?><br></b> <?php echo $extraData[$u]['answer']; ?></p>
                        <div class="moba-line my-3"></div>
                        <?php } ?>
                        <p><b>Callout Charge</b><br><?php echo $countryData['currency_symbol']." ".number_format($data['fee'], 2); ?></p>
                        <?php if ($data['status'] == "OPEN") { ?>
                        <form name="req" id="req" method="post" action="">
                        <input type="hidden" name="request" value="<?php echo $data['ref']; ?>"/>
                        <input type="hidden" name="user_r_id" value="<?php echo $user_id; ?>"/>
                        <input type="submit" name="accept_req" value="Accept Request" class="btn purple-bn1"/>
                        <input type="submit" name="reject_req" value="Reject" class="btn red-bn1"/>
                        </form>
                        <?php } else { ?>
                            <p class="alert alert-danger">This request has expired</p>
                        <?php } ?>
                    <?php } else {
                        $messages->markRead($_SESSION['users']['ref'], $data['ref']);
                        $initialComment = $messages->getPage($data['ref'], $user_r_id, $user_id); ?>     			
                        <h5><a name="messages"></a>Messages with <?php echo $users->listOnValue($user_r_id, "screen_name"); ?></h5>
                        <small>Average Response Time:</b> <?php echo $users->listOnValue($user_r_id, 'average_response_time'); ?></small>
                        <p><?php echo $usersData['about_me']; ?></p>
                        <div class="moba-line mb-3"></div>
                        <?php echo $rating->drawRate(intval($rating->getRate($usersData['ref']))); ?>
                        <div class="moba-line mb-3"></div>
                        <div class="row">
                            <ol id="update" >
                            <?php for ($i = 0; $i < count($initialComment); $i++) { ?>
                                <li class="media" id='<?php echo $initialComment[$i]['ref']; ?>'>
                                <?php $users->getProfileImage($initialComment[$i]['user_id'], "mr-3", false); ?>
                                <div class="media-body">
                                <small class="time"><i class="fa fa-clock-o"></i> <?php echo $this->get_time_stamp(strtotime($initialComment[$i]['create_time'])); ?></small>
                                <p class="mt-0">
                                <?php if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                                    $m_type_data = explode("_", $initialComment[$i]['m_type_data'] ) ?>
                                    <i class="fa fa-handshake" aria-hidden="true"></i><br><?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>You have a <?php } ?>new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['region'], "currency_symbol", "ref" )." " .$m_type_data[0]; ?></strong>
                                    <?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>
                                    <p><a href="<?php echo $this->seo($usersData['ref'], "profile").$data['ref']."/negotiate?respond=".$currentData['ref']; ?>" class="btn purple-bn pd" title='Remove'><i class="fas fa-tags"></i>&nbsp;&nbsp;Respond to Negotiation</a></p><?php } ?>
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
                                    <?php $users->getProfileImage($user_id, "", false); ?> 
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
                                    if (data.id != "") {
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
                                                var msg = '<i class="fa fa-handshake-o" aria-hidden="true"></i><br>You have a new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['region'], "currency_symbol", "ref" ); ?>'+data.data_1+'</strong><br><p><a href="<?php echo $this->seo($user_id, "profile"); ?>'+data.data_2+'<?php echo "/negotiate?respond=".$user_id; ?>" class="btn purple-bn pd" title=\'Remove\'><i class="fas fa-tags"></i>&nbsp;&nbsp;Respond to Negotiation</a></p>';

                                                var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+msg+"</p></div></li>";
                                            } else {
                                                var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+data.msg+"</p></div></li>";
                                            }
                                            $(div_data).appendTo("ol#update");
                                            }
                                        });
                                    }
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
            <script type="text/javascript">
                $('#negotiated_fee').on('keyup keypress blur change', function() {
                    var current = <?php echo $data['fee']; ?>;
                    if ($(this).val() != current) {
                        $("#negotiate").prop('disabled', false);
                    } else {
                        $("#negotiate").prop('disabled', true);
                    }
                });
                $('#approve_ad').click(function() {
                    $(this).confirm({
                    text: "Once you confirm this action, this request becomes unavailable to others and assigned to you. You are bound by the MOBA terms and conditions. Are you sure you want to continue?",
                    confirm: function(button) {
                        window.location='<?php echo URL."__approveApp?post_id=".$data['ref']."&user_r_id=".$user_r_id."&user_id=".$data['user_id']; ?>';
                    },
                    });
                });
            </script>
        <?php 
        }

        public function profileHome() {
            global $users;
            global $rating;
            global $request;
            global $usersKin;

            $ref = trim($_SESSION['users']['ref']);
            $data = $users->listOne($ref);
            $getCatList = $users->getCatNameList($ref);
            $getKin = $usersKin->listOneKin($ref);
            ?>
            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php echo $users->getProfileImage($ref, "card-img-top my-3", 25, false); ?>
                    <?php echo $rating->drawRate(intval($rating->getRate($data['ref']))); ?>
                    <div class="moba-line my-3"></div>
                    <p><?php echo $data['about_me']; ?></p>
                    <p><b>Rating:</b> <?php echo $rating->textRate(intval($rating->getRate($data['ref']))); ?></p>
                    <?php if ($data['user_type'] == 1) { ?>
                    <p><b>Number of Tasks Completed:</b> <?php echo $request->taskCompleted($data['ref'], "client_id"); ?></p>
                    <?php } else if ($data['user_type'] != 1) { ?>
                    <p><b>Number of Hires:</b> <?php echo $request->taskCompleted($data['ref'], "user_id"); ?></p>	
                    <?php } ?>
                    <p><b>Average Response Time:</b> <?php echo $data['average_response_time']; ?></p>
                    <p><a href="<?php echo URL."edit/Image"; ?>" class="btn purple-bn pd">Modify Display Picture</a></p>
                    <?php if ($data['screen_name_cam_change'] == 0) { ?>
                        <p><a href="<?php echo URL."edit/ScreenName"; ?>" class="btn purple-bn pd">Edit Username</a></p>
                    <?php } ?>
                </div>
                <div class="col-lg-8">
                    <h5><?php echo $data['last_name']." ".$data['other_names']." <strong>'".$data['screen_name']."'</strong>"; ?></h5>
                    <div class="moba-line mb-3"></div>
                    <p>Public Profile URL<br>
                    <a href="<?php echo URL."profile/".$data['screen_name']; ?>" target="_blank"><?php echo URL."profile/".$data['screen_name']; ?></a></p>
                    <p>Email<br>
                    <strong><?php echo $data['email']; ?></strong></p>
                    <?php if ($data['user_type'] == 1) { ?>
                        <p>Phone Number<br>
                        <strong><?php echo $data['mobile_number']; ?></strong></p>
                        <p>Primary Address<br>
                        <strong><?php echo $data['street']." ".$data['city']." ".$data['state']." ".$data['country']; ?></strong></p>
                        <h5>Services</h5>
                        <p><?php echo $getCatList; ?></p>
                        <h5>Next of Kin</h5>
                        <p>Name<br>
                        <strong><?php echo $getKin['kin_name']; ?></strong></p>
                        <p>Email Address<br>
                        <strong><?php echo $getKin['kin_email']; ?></strong></p>
                        <p>Phone Number<br>
                        <strong><?php echo $getKin['kin_phone']; ?></strong></p>
                        <p>Relationship<br>
                        <strong><?php echo $getKin['kin_relationship']; ?></strong></p>
                    <?php } ?>
                    <div class="row">
                        <div class="col-lg-4">
                            <a href="<?php echo URL."edit/Profile"; ?>" class="btn purple-bn pd">Update Profile</a>
                        </div>
                        <div class="col-lg-4">
                            <a href="<?php echo URL."edit/Password"; ?>" class="btn purple-bn pd">Update Password</a>
                        </div>
                        <div class="col-lg-4">
                            <a href="<?php echo URL."edit/IDs"; ?>" class="btn purple-bn pd">Upload IDs</a>
                        </div>
                    </div>
                    <div class="moba-line m-3"></div>
                        
                </div>
            </div>
            </div>
        <?php }

        private function publicProfile($view) {
            global $users;
            global $rating_comment;
            global $rating; 
            global $request;
            $data = $users->listOne($view, "screen_name");
            $ratingDataVendor = $rating->publicPage($data['ref'], "vendors");
            $ratingDataClients = $rating->publicPage($data['ref'], "clients");
            $comments = $rating_comment->getSortedList($data['ref'], "user_id");
            ?>

            <div class="container my-5">
            <div class="row py-5">	
                <div class="col-lg-4">
                    <?php $users->getProfileImage($data['ref'], "card-img-top my-3", "25"); ?>
                    <?php echo $rating->drawRate(intval($rating->getRate($data['ref']))); ?>
                    <div class="moba-line my-3"></div>
                    <p><?php echo $data['about_me']; ?></p>
                    <p><b>Rating:</b> <?php echo $rating->textRate(intval($rating->getRate($data['ref']))); ?></p>
                    <?php if ($data['user_type'] == 1) { ?>
                    <p><b>Number of Tasks Completed:</b> <?php echo $request->taskCompleted($data['ref'], "client_id"); ?></p>
                    <?php } else if ($data['user_type'] != 1) { ?>
                    <p><b>Number of Hires:</b> <?php echo $request->taskCompleted($data['ref'], "user_id"); ?></p>	
                    <?php } ?>
                    <p><b>Average Response Time:</b> <?php echo $data['average_response_time']; ?></p>
                </div>
                <div class="col-lg-8">
                    <h5><?php echo $data['last_name']." ".$data['other_names']." <strong>'".$data['screen_name']."'</strong>"; ?></h5>
                    <div class="moba-line mb-3"></div>
                    <p> User since <?php echo $this->get_time_stamp( strtotime( $data['create_time'] ) ); ?></p>
                    <h4>Ratings for Posted Jobs Offered</h4>
                    <?php if (count($ratingDataClients) > 0) { ?>
                        <p><?php for ($i = 0; $i < count($ratingDataClients); $i++) {
                            echo "<strong>".$ratingDataClients[$i]['question']."</strong>: ".$rating->drawRate($ratingDataClients[$i]['val'])."<br>";
                        } ?></p>
                    <?php } else { ?>
                        <p>Not rated yet</p>
                    <?php } ?>
                    <h5>Ratings for Posted Services Provided</h5>
                    <?php if (count($ratingDataVendor) > 0) { ?>
                        <p><?php for ($i = 0; $i < count($ratingDataVendor); $i++) {
                            echo "<strong>".$ratingDataVendor[$i]['question']."</strong>: ".$rating->drawRate($ratingDataVendor[$i]['val'])."<br>";
                        } ?></p>
                    <?php } else { ?>
                        <p>Not rated yet</p>
                    <?php } ?>
                    <h5>Comments</h5>
                    <?php if (count($comments) > 0) { ?>
                        <p><?php for ($i = 0; $i < count($comments); $i++) {
                            if ($comments[$i]['comment'] != "") {
                                echo $comments[$i]['comment']."<br><small>".$users->listOnValue( $comments[$i]['reviewed_by'], "screen_name").", ".$this->get_time_stamp( strtotime( $comments[$i]['create_time'] ) )."</small><br><br>";
                            }
                        }?></p>
                    <?php } else { ?>
                        <p>Not rated yet</p>
                    <?php } ?>
                    <div class="moba-line m-3"></div>
                        
                </div>
            </div>
            </div>
        <?php }

        private function listNotification($view) {
            if ($view == "all") {
                return $this->notifyList();
            } else if ($view == "inbox") {  
                return $this->messages();
            }
        }

        function messages() {
            global $options;
            global $users;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            global $notifications;
            $notificationList = $notifications->getSortedList($_SESSION['users']['ref'], "user_id", "event", "post_messages", false, false, "ref", "DESC", "AND", $start, $limit); 
            $notificationListCount = $notifications->getSortedList($_SESSION['users']['ref'], "user_id", "event", "post_messages", false, false, "ref", "DESC", "AND", false, false, "count"); 
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
                <td><?php $users->getProfileImage( $notificationList[$i]['user_id'], "mr-3 main_img profile thumbnail" ); ?></td>
                <td><?php echo $this->url( $notificationList[$i]['event'], $notificationList[$i]['event_id'], $notificationList[$i]['message'], $notificationList[$i]['user_r_id']); ?></td>
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
                <th scope="col">Message</th>
                <th scope="col">Created</th>
                <th scope="col">Last Modified</th>
              </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < count($notificationList); $i++) {
                    $notifications->markReadOne($notificationList[$i]['ref']); ?>
                <tr <?php if ($notificationList[$i]['status'] == 0) { ?>class="table-active"<?php } ?>>
                <th scope="row"><?php echo $start+$i+1; ?></th>
                <td><?php echo $this->url( $notificationList[$i]['event'], $notificationList[$i]['event_id'], $notificationList[$i]['message'], $notificationList[$i]['user_r_id']); ?></td>
                <td><?php echo $notificationList[$i]['create_time']; ?></td>
                <td><?php echo $notificationList[$i]['modify_time']; ?></td>
              </tr>
                <?php } ?>
            </tbody>
          </table>
          <?php $this->pagination($page, $notificationListCounut);
        }

        private function url($text, $id, $Label, $user_id=0) {
            if ($text == "system") {
                return '<a href="'.$this->seo($user_id, "profile")."/".$id."/message".'">System Notification: '.$Label.'</a>';
            } else if ($text == "post_messages") {
                return '<a href="'.$this->seo($user_id, "profile").$id."/message".'">'.$Label.'</a>';
            } else if ($text == "negotiate_charges") {
                return '<a href="'.$this->seo($user_id, "profile").$id."/message".'">Negotiation Request</a>';
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
