<?php
    class userProject extends projects {
        public function pageContent($redirect, $project_type, $edit=0) {
            if ($project_type == "continuation") {
                $this->paymentGateway($edit);
            } else if ($project_type == "confirmation") {
                $this->confirmationPage($edit);
            } else {
                $this->createNew($redirect, $edit);
            }
        }

        public function duplicate($ref) {
            global $media;
            
            $data = $this->listOne($ref);

            unset($data['ref']);
            unset($data['client_id']);
            unset($data['page_visit']);
            unset($data['is_featured']);
            unset($data['is_featured_time']);
            unset($data['start_date']);
            unset($data['end_date']);
            unset($data['status']);
            unset($data['payment_status']);
            unset($data['review_status']);
            unset($data['review_status_time']);
            unset($data['client_rate']);
            unset($data['user_rate']);
            unset($data['create_time']);
            unset($data['modify_time']);

            $add = $this->create($data);

            if ($add) {
                $media_files = $media->getAlbum($ref);

                $mediaArray = array();
                $mediaArray['user_id'] = $data['user_id'];
                $mediaArray['project_id'] = $add;
                for ($i = 0; $i < count($media_files); $i++) {
                    $mediaArray['media_type'] = $media_files[$i]['media_type'];
                    $mediaArray['media_url'] = $media_files[$i]['media_url'];
                    $this->insert("media", $mediaArray);
                }
                return $add;
            } else {
                return false;
            }
        }

        public function postMew($array, $file) {
            global $media;
            global $category;
            if ($array['ref'] == 0) {
                unset($array['ref']);
                $array['project_code'] = $this->getCode();
            }
            $tag = json_decode($array['tag'], true);

            $newTag = array();
            foreach($tag as $value) {
                 $newTag[] = $value['value'];
            }
            unset($array['tag']);
            $array['tag'] = implode(", ", $newTag);

            if ($array['project_type'] == "vendor") {
                $payment_status = 0;
            } else {
                $payment_status = 1;
            }
            $cat_id = $array['category_id'];
            for ($i = 0; $i < count($cat_id); $i++) {
                $parent_id = $category->getSingle($cat_id[$i], "parent_id", "ref");
                if ($parent_id != 0) {
                    $array['category_id'][] = $parent_id;
                }
            }
            $category_id = implode(",", array_unique($array['category_id']));
            unset($array['category_id']);
            $array['category_id'] = $category_id;
            $array['address'] = $array['autocomplete'];
            $array['payment_status'] = $payment_status;
            unset($array['autocomplete']);
            $file = $media->reArrayFiles($file['uploadFile']);
            $add = $this->create($array);

            if ($add) {
                $mediaArray = array();
                $mediaArray['user_id'] = $array['user_id'];
                $mediaArray['project_id'] = $add;
                if (count($file) > 0) {
                   $media->create($file, $mediaArray);
                }
                return $add;
            } else {
                return false;
            }
        }

        public function createNew($redirect, $edit) {
            global $category;
            global $country;
            $regionData = $country->getLoc($_SESSION['location']['code']);
            $list = $category->getSortedList("ACTIVE", "status", "parent_id", 0);

            if ($edit > 0) {
                global $media;
                $data = $this->listOne($edit, "ref");
                $tag = "Edit ".$data['project_name'];
                $getAlbum = $media->getAlbum($data['ref']);
                $project_type = $data['project_type'];
            } else {
                $data = false;
            }

            if ($project_type == "vendor") {
                $line2 = "be paid";
                $line3 = "the service";
            } else if ($project_type == "client") {
                $line2 = "pay";
                $line3 = "the job";
            } else {
                $tag = "Creat New Post";
                $line2 = "pay";
                $line3 = "the post";
            }

             ?>
<main class="col-12" role="main">
<form method="post" action="" enctype="multipart/form-data" autocomplete="off">
  <h2 class="tag"><?php echo $tag; ?></h2>
  <input type="hidden" name="user_id" value="<?php echo $_SESSION['users']['ref']; ?>" />
  <input type="hidden" name="project_type" value="<?php echo $project_type; ?>" />
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Ad Details</h5>
        </div>
        <div id="card-body">
            <div class="form-group">
                <label for="project_type">Service Type</label>
                <span id="spryselect0">
                    <select class="form-control" id="project_type" name="project_type" required>
                        <option value="">Select One</option>
                        <option value="client"<?php if ($data['project_type'] == "client") { ?> selected<?php } ?>>Post a Job</option>
                        <option value="vendor"<?php if ($data['project_type'] == "vendor") { ?> selected<?php } ?>>Post a Service</option>
                    </select>
                    <span class="selectRequiredMsg"><br>Please select an item.</span>
                </span>
                <small id="project_type_help" class="form-text text-muted">This will be the type of post you want to create.</small>
            </div>
            <div class="target" style="display:none">
          <div class="form-group">
                <label for="project_name">Posting Title</label>
                <span id="sprytextfield1">
                <input type="text" class="form-control" name="project_name" id="project_name" placeholder="Enter Posting Title" required value="<?php echo $data['project_name']; ?>" maxlength="70" />
                <span class="textfieldRequiredMsg"><br />
                A value is required.</span> <span class="textfieldMaxCharsMsg">Exceeded maximum number of characters.</span></span>
                <small id="project_name_help" class="form-text text-muted">This will be the title of the advert you are Posting.</small>
            </div>
            <div class="form-group">
                <label for="project_dec">Posting Description</label>
                <span id="sprytextarea1">
                    <textarea class="form-control" name="project_dec" id="project_dec" placeholder="Enter Posting Description" required><?php echo $data['project_dec']; ?></textarea>
                    <span class="textareaRequiredMsg"><br>A value is required.</span>
                </span>
                <small id="project_dec_help" class="form-text text-muted">This will be the detailed description of the advert you are Posting.</small>
            </div>
            <div class="form-group">
                <label for="allow_remote">Location</label>
                <span id="spryselect2">
                    <select class="form-control" id="allow_remote" name="allow_remote" required>
                        <option value="">Select One</option>
                        <option value="0"<?php if ($data['allow_remote'] == "0") { ?> selected<?php } ?>>On-Site</option>
                        <option value="1"<?php if ($data['allow_remote'] == "1") { ?> selected<?php } ?>>Remote</option>
                    </select>
                    <span class="selectRequiredMsg"><br>Please select an item.</span>
                </span>
                <small id="allow_remote_help" class="form-text text-muted">This will allow users know if this service can be rendered remotely.</small>
            </div>
            <div class="form-group">
                <label for="category_id">Ad Category</label>
                <span id="spryselect3">
                    <select class="form-control" id="category_id" name="category_id[]" multiple required>
                            <?php for ($i = 0; $i < count($list); $i++) {
                                $listSub = $category->getSortedList("ACTIVE", "status", "parent_id", $list[$i]['ref']); ?>
                                <option value="<?php echo $list[$i]['ref']; ?>"<?php if ($data['category_id'] == $list[$i]['ref']) { ?> selected<?php } ?>><?php echo ucwords(strtolower($list[$i]['category_title'])); ?></option>
                                <?php for ($j = 0; $j < count($listSub); $j++) { ?>
                                    <option value="<?php echo $listSub[$j]['ref']; ?>"<?php if ($data['category_id'] == $listSub[$j]['ref']) { ?> selected<?php } ?>><?php echo ucwords(strtolower($listSub[$j]['category_title'])); ?></option>
                                <?php } ?>
                            <?php } ?>
                    </select>
                    <span class="selectRequiredMsg"><br>Please select an item.</span>
                </span>
                <small id="category_id_help" class="form-text text-muted">This will be the categories under which this posting will appear, you can select multiple categories.</small>
            </div>
            <div class="form-group">
                <label for="autocomplete">Address</label>
                <span id="sprytextfield2">
                    <input id="autocomplete" name="autocomplete" placeholder="Enter your address" onfocus="geolocate()" type="text" class="form-control" autocomplete="false" value="<?php echo $data['address']; ?>"/>
                    <span class="textfieldRequiredMsg"><br>A value is required.</span>
                </span>
                <input type="hidden" name="city" id="locality" value="<?php echo $data['city']; ?>">
                <input type="hidden" name="state" id="administrative_area_level_1" value="<?php echo $data['state']; ?>">
                <input type="hidden" name="postal_code" id="postal_code" value="<?php echo $data['postal_code']; ?>">
                <input type="hidden" name="country" id="country" value="<?php echo $data['country']; ?>">
                <input type="hidden" name="lat" id="lat" value="<?php echo $data['lat']; ?>">
                <input type="hidden" name="country_code" id="country_code" value="">
                <input type="hidden" name="lng" id="lng" value="<?php echo $data['lng']; ?>">
                <small id="autocomplete_help" class="form-text text-muted">This will be the location where <span class="line3"><?php echo $line3; ?></span> is required.</small>
            </div>
            <div class="form-group">
                <label for="billing_type">Billing Type</label>
              <span id="spryselect1">
                <select class="form-control" id="billing_type" name="billing_type" required>
                  <option value="">Select One</option>
                  <option value="per_hour"<?php if ($data['billing_type'] == "per_hour") { ?> selected<?php } ?>>Per Hour</option>
                  <option value="per_mil"<?php if ($data['billing_type'] == "per_mil") { ?> selected<?php } ?>>Per Milestone</option>
                  <option value="per_job"<?php if ($data['billing_type'] == "per_job") { ?> selected<?php } ?>>Flat Rate</option>
                </select>
                <span class="selectRequiredMsg">Please select an item.</span></span><small id="billing_type_help" class="form-text text-muted">This will be how you want to <span class="line2"><?php echo $line2; ?></span> for <span class="line3"><?php echo $line3; ?></span>.</small>
            </div>
            <div class="form-group">
                <label for="default_fee">Price</label>

                <span id="sprytextfield3">
                    <input type="text" class="form-control" name="default_fee" id="default_fee" placeholder="Enter Your Asking Price" required value="<?php echo $data['default_fee']; ?>" />
                    <span class="textfieldRequiredMsg"><br>A value is required.</span>
                    <span class="textfieldInvalidFormatMsg"><br>Invalid format.</span>
                    <span class="textfieldMinValueMsg"><br>The entered value is less than the minimum required.</span>
                </span>
                <small id="default_fee_help" class="form-text text-muted">This will be how much you are willing to <span class="line2"><?php echo $line2; ?></span> for <span class="line3"><?php echo $line3; ?></span>.</small>
            </div>
            <div class="form-group">
                <label for="tag">Tags (Optional)</label>
                <input type="text" class="form-control" name="tag" id="tag" placeholder="Enter Posting's tags and search keywords" value="<?php echo $data['tag']; ?>" />
                <small id="tag_help" class="form-text text-muted">List of words and tags that will make your post easier to find in searches.</small>
            </div>
        </div>
        </div>
    </div>
    <?php if (count($getAlbum) > 0) { ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Manage Images
            </h5>
        </div>
        <div id="card-body">
        <p>To remove a picture, click on the picture and confirm the action. This action is not reversable</p>
        <?php for ($i = 0; $i < count($getAlbum); $i++) { ?>
            <a href="<?php echo URL.$redirect."/".$data['project_type']."?edit=".$data['ref']."&del=".$getAlbum[$i]['ref']; ?>" onClick="return confirm('this action will remove this picture and can not be undone. are you sure you want to continue ?')"><img src="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:150px;" height="50px"></a>
        <?php } ?>
        </div>
    </div>
    <?php } ?>
    <div class="card">
        <div class="target" style="display:none">
            <div class="card-header">
                <h5 class="mb-0">
                    Upload New Images
                </h5>
            </div>
            <div id="card-body">
                <div class="container">
                <label for="uploadFile">Include pictures with as much details as you will prefer. You can upload a maximum of 10 media with a maximum fike size of 2MB.</label>
                    <div class="row">
                        <div class="col-sm-2 imgUp">
                            <div class="imagePreview"></div>
                            <label class="btn btn-primary">
                            Select File<input type="file" name="uploadFile[]" id="uploadFile" class="uploadFile img" value="Upload Photo" accept="image/*" style="width: 0px;height: 0px;overflow: hidden;" onchange="checkFileSize(event)">
                            </label>
                        </div><!-- col-2 -->
                        <i class="fa fa-plus imgAdd"></i>
                    </div><!-- row -->
                </div><!-- container -->

            </div>
        </div>
    </div>

    <div class="target" style="display:none">
    <input type="hidden" name="ref" value="<?php echo $edit; ?>">
    <input type="hidden" name="region" value="<?php echo $regionData['ref']; ?>">
    <button type="submit" name="submitProject" class="btn btn-primary"><?php echo $tag; ?></button>
    <?php if ($edit > 0) { ?>
    <button type="button" class="btn btn-primary" onClick="location='<?php echo URL."ads"; ?>'" >Cancel</button>
    <?php } ?>
    </div>
    </div>
</form>
</main>
</div>

<script type="text/javascript" src="<?php echo URL; ?>js/imageUpload.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>js/places.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GoogleAPI; ?>&libraries=places&callback=initAutocomplete" async defer></script>
<link rel="stylesheet" href="<?php echo URL; ?>css/tagify.css">
<script src="<?php echo URL; ?>js/jQuery.tagify.min.js"></script>

<script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {validateOn:["blur", "change"], maxChars:70});
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {validateOn:["blur", "change"]});
var spryselect0 = new Spry.Widget.ValidationSelect("spryselect0", {validateOn:["blur", "change"]});
var spryselect2 = new Spry.Widget.ValidationSelect("spryselect2", {validateOn:["blur", "change"]});
var spryselect3 = new Spry.Widget.ValidationSelect("spryselect3", {validateOn:["blur", "change"]});
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1", {validateOn:["change", "blur"]});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {validateOn:["blur", "change"]});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "currency", {validateOn:["blur", "change"], minValue:0});

$('[name=tag]').tagify({duplicates : false});
$( "#project_type" ).change(function() {
  open();
});

function open() {
    var project_type = $( "#project_type" ).val();
    
    if (project_type != "") {
        $( ".target" ).show();
        if (project_type == "vendor") {
            var tag = "Post a Service";
            var line2 = "be paid";
            var line3 = "the service";
        } else if (project_type == "client") {
            var tag = "Post a Job";
            var line2 = "pay";
            var line3 = "the job";
        }
        
        $( ".tag" ).html( tag );
        $( ".line2" ).html( line2 );
        $( ".line3" ).html( line3 );
    } else {
        $( ".target" ).hide();
    }
    $('#category_id').select2({
        placeholder: 'Select multiple options'
    });
}
$(document).ready(function() {
    open();

    setMultiSelectedIndex(document.getElementById("category_id"),"<?php echo $data['category_id']; ?>");

    $('#category_id').select2({
        placeholder: 'Select multiple options'
    });
});

function checkFileSize(e) {
    var element = e.target.id;
    if ((document.getElementById(element).files[0].size/1024/1024 ) > 2) {
        alert('This file size is: ' + (document.getElementById(element).files[0].size/1024/1024).toFixed(2) + "MB");
        document.getElementById(element).value = "";
    }
}

</script>
        <?php }

        function confirmationPage($ref) {
            global $media;
            global $country;
            $data = $this->listOne($ref);
            $getAlbum = $media->getAlbum($ref); ?>
    <h2>Confirm "<?php echo $data['project_name']; ?>"</h2>
    <p><?php echo $data['project_dec']; ?></p>
    <p><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['address']; ?></p>
<table width="100%" border="0">
  <tr>
    <td>Listed Category(s)</td>
    <td><?php echo $this->getTagFromWord($data['category_id'], "category", "blank"); ?></td>
    <td rowspan="10">
        <?php if (count($getAlbum) > 0) {
        for ($i = 0; $i < count($getAlbum); $i++) { ?>
            <a data-fancybox="gallery" href="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>"><img src="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:150px;"></a>
        <?php }
        } else { ?>
            <img src="<?php echo $media->mediaDefault(); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:250px;">
        <?php } ?>

    </td>
  </tr>
  <tr>
    <td>Ad Type</td>
    <td><?php echo $this->cleanText( $data['project_type'] ); ?></td>
  </tr>
  <tr>
    <td>Billing Type</td>
    <td><?php echo $this->cleanText( $data['billing_type'] ); ?></td>
  </tr>
  <tr>
    <td>Default Fee</td>
    <td><?php echo $country->getCountryData( $data['country'] )." ".number_format($data['default_fee'], 2); ?></td>
  </tr>
  <tr>
    <td>Status</td>
    <td><?php echo $data['status']; ?></td>
  </tr>
  <tr>
    <td>Payment Status</td>
    <td><?php echo $this->cleanText( $data['payment_status'] ); ?></td>
  </tr>
  <tr>
    <td>Created</td>
    <td><?php echo $data['create_time']; ?></td>
  </tr>
  <tr>
    <td>Last Modified</td>
    <td><?php echo $data['modify_time']; ?></td>
  </tr>
  <tr>
    <td>Tags</td>
    <td><?php echo $this->getTagFromWord($data['tag'], "tag", "blank"); ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><a href="<?php echo URL."hire/".$data['project_type']."?edit=".$data['ref']; ?>">Edit</a></td>
  </tr>
</table>
<form method="post" action="" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="project_id" id="project_id" value="<?php echo $ref; ?>">
    <label class="form-check-label" for="defaultCheck1">
        <input type="checkbox" value="1" id="defaultCheck1">
        I have accepted the MOBA terms and conditions associated with posting of this ad
    </label>
    <br>
    <br>
    <button disabled="disabled" type="submit" name="approveProject" id="approveProject" class="btn btn-primary">Publish Ad</button>
</form>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js" async defer></script>

<script>
    $("#defaultCheck1").click(function(){
        if($(this).prop("checked") == true){
            $('#approveProject').prop('disabled', false);
        }
        else if($(this).prop("checked") == false){
            $('#approveProject').prop('disabled', true);
        }
    });
</script>
        <?php }

        public function paymentGateway($ref) {
            global $userPayment;
            $data = $this->listOne($ref);
            $list = $userPayment->getSortedList($_SESSION['users']['ref'], "user_id");  ?>
<main class="col-12" role="main">
    <input type="hidden" name="project_id" id="project_id" value="<?php echo $data['ref']; ?>">
    <h2><?php echo $data['project_name']; ?></h2>
    <p><i class="fa fa-sticky-note-o" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['project_dec']; ?></p>
    <p><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['address']; ?></p>
    <p><i class="fa fa-tags" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->getTagFromWord($data['tag'], "tag", "blank"); ?></p>
    <p>You must provide your payment information, you will not be charged at this time until your project is underway</p>
    <?php if (count($list) > 0) {?>
    <h2>Select Payment Profile</h2>
    <div class="form-group">
        <label for="jumpMenu">Payment Cards</label>
        <select name="jumpMenu" id="jumpMenu" onchange="MM_jumpMenu('parent',this,0)" class="form-control">
        <option>Select a Card</option>
        <?php for ($i = 0; $i < count($list); $i++) { ?>
            <option value="?auth_card">**** **** **** <?php echo $list[$i]['pan']; if ($list[$i]['is_default'] == 1) { echo " [Default]"; } ?></option>
        <?php } ?>
        </select>
    </div>
        <?php } else { ?>
            <p>Looks like you don't have a payment profile already created. You need to add one below to continue</p>
        <?php } ?>
    <?php $userPayment->createNew(); ?>
<script type="text/javascript">
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
</main>

<script type="text/javascript" src="<?php echo URL; ?>js/creditCard.js"></script>
        <?php }

        public function savePayment($id) {
            $payment_status = 2;
            $this->updateOne("projects", "payment_status", $payment_status, $id, "ref");
            return true;
        }

        public function approveProject($id) {
            return $this->modifyStatus($id, "ACTIVE");
        }
    }
?>