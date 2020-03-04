<?php
  class adminCategory extends category {
    public function navigationBar($redirect) { ?>
      <p><i class="fa fa-caret-right mr-3"></i><a href="<?php echo URL.$redirect; ?>"><b>List All</b></p>
      <div class="moba-line my-2"></div>
      <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/create"; ?>"><b>Add New</b></a></p>	
        <?php }

    function pageContent($redirect, $view="list", $edit=0) {
      if ($view == "createQuestionare") {
        $this->createQuestionare($redirect, $edit);
      } else if ($view == "list") {
        $this->listAll($redirect);
      } else {
          $this->createNew($redirect, $edit);
      }
    }

    private function createQuestionare($redirect, $edit) {
      global $categoryQuestion;
      global $options;
      $data = $this->getOne("category", $edit, "ref");
      if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
      } else {
        $page = 0;
      }

      if (isset($_REQUEST['qRef'])) {
        $qRef = $_REQUEST['qRef'];
        $qData = $categoryQuestion->listOne($qRef);
        $title = "Modify Questionnaire for ".$data['category_title'];
        $tag = "Save Changes";
      } else {
        $qRef = 0;
        $title = "Set up Questionnaire for ".$data['category_title'];
        $tag = "Save Questioniare";
      }
      
      
      $limit = $options->get("result_per_page");
      $start = $page*$limit;
      $responseData = $categoryQuestion->categoryListPages($data['ref'], $start, $limit);
      $list = $responseData['data'];
      $listCount = $responseData['count']; ?>
      <main class="col-12" role="main">
      <form method="post" action="" enctype="multipart/form-data">
      <h2><?php echo $title; ?></h2>
      <div class="form-group">
          <label for="title">Question 1</label>
          <input type="text" class="form-control" name="title" id="title" placeholder="Enter Question" required value="<?php echo $qData['title']; ?>">
      </div>
      <div class="form-group">
        <label for="type">Type</label>
        <select class="form-control" id="type" name="type" required>
          <option value="">Select One</option>
          <option value="0"<?php if ($qData['type'] == "0") { ?> selected<?php } ?>>Multiple Option</option>
          <option value="1"<?php if ($qData['type'] == "1") { ?> selected<?php } ?>>Text Input</option>
        </select>
      </div>
      <div class="form-group">
          <label for="data">Option</label>
          <textarea name="data" id="data" class="form-control"><?php echo $qData['data']; ?></textarea>
          <small>Enter each question options line by line</small>
      </div>
      <input type="hidden" name="category_id" value="<?php echo $edit; ?>">

      <?php if ($qRef > 0) { ?>
      <input type="hidden" name="ref" value="<?php echo $qRef; ?>">
      <?php } ?>
      <button type="submit" name="AddQuestion" id="AddQuestion" class="btn purple-bn1"><?php echo $tag; ?></button>
      <button type="button" class="btn purple-bn1" onClick="location='<?php echo $redirect; ?>'" >Cancel</button>
      </form>
      <table class="table table-striped">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Question</th>
            <th scope="col">Type</th>
            <th scope="col">Options</th>
            <th scope="col">Created</th>
            <th scope="col">Last Modified</th>
            <th scope="col">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]['type'] == 0) {
              $statusTag = "Selection";
            } else if ($list[$i]['type'] == 1) {
              $statusTag = "Text";
            } ?>
          <tr>
            <th scope="row"><?php echo $start+$i+1; ?></th>
            <td><?php echo $list[$i]['title']; ?></td>
            <td><?php echo $statusTag; ?></td>
            <td><?php echo $list[$i]['data']; ?></td>
            <td><?php echo $list[$i]['create_time']; ?></td>
            <td><?php echo $list[$i]['modify_time']; ?></td>
            <td><a href="<?php echo URL.$redirect."/createQuestionare?edit=".$data['ref']."&qRef=".$list[$i]['ref']; ?>">Edit</a> | <a href="<?php echo URL.$redirect."/createQuestionare?edit=".$data['ref']."&deleteQustionare=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this category. are you sure you want to continue ?')">Delete</a></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php $this->pagination($page, $listCount); ?>
    </main>
    </div>
    <script type="text/javascript">
      $(document).ready(function() {
        $('#type').change(function() {
          if ($(this).val() == 1) {
            $('#data').removeAttr("required")
            $('#data').prop('readonly', true);  
          } else {
            $('#data').prop('readonly', false);
            $('#data').attr("required")      
          }
        })

        if ($(this).val() == 1) {
            $('#data').removeAttr("required")
            $('#data').prop('readonly', true);  
          } else {
            $('#data').prop('readonly', false);
            $('#data').attr("required")      
          }
      });
    </script>
    <?php }

    function createNew($redirect, $edit=0) {
      global $country;
      $list = $this->getSortedList("ACTIVE", "status", "parent_id", 0);

      if ($edit > 0) {
          $data = $this->getOne("category", $edit, "ref");
          $title = "Update ".$data['category_title'];
          $tag = "Save Category ";
      } else {
          $data = false;
          $tag = $title = "Create Category";
      }
             ?>
  <style>
    div .thumb-image {
      max-width:150px;
    }
  </style>
  <main class="col-12" role="main">
  <form method="post" action="" enctype="multipart/form-data">
  <h2><?php echo $title; ?></h2>
  <div class="form-group">
      <label for="category_title">Category Name</label>
      <input type="text" class="form-control" name="category_title" id="category_title" placeholder="Enter Category Name" required value="<?php echo $data['category_title']; ?>">
  </div>
  <div class="form-group">
      <label for="call_out_charge">Callout Charge</label>
      <input type="number" class="form-control" name="call_out_charge" id="call_out_charge" placeholder="Enter Callout Charge in <?php echo $country->getCountryData($_SESSION['location']['code'], "currency_symbol", "code"); ?>" step="0.01" required value="<?php echo $data['call_out_charge']; ?>">
  </div>
  <?php if ($data['image_url'] != "") { ?>
    <div class="form-group">
      <label for="fileUpload">Category Icon</label>
      <div id="image-holder" class="thumb-image"><img width="150" src="<?php echo $this->getIcon($data['ref']); ?>"></div>
      <a href="<?php echo URL."admin/category/create?edit=".$data['ref']."&updateImg"; ?>" onClick="return confirm('this action will remove this icon, this action can not be reversed. are you sure you want to continue ?')">remove icon</a>
    </div>
  <?php } else { ?>
    <div class="form-group">
        <label for="fileUpload">Category Icon</label>
        <input id="fileUpload" name="img" type="file" class="form-control" accept="image/png">
        <div id="image-holder" class="thumb-image"></div>
    </div>
  <?php } ?>
  <div class="form-group">
    <label for="parent_id">Parent Category</label>
    <select class="form-control" id="parent_id" name="parent_id" required>
      <option value="0">None</option>
      <?php for ($i = 0; $i < count($list); $i++) { ?>
      <option value="<?php echo $list[$i]['ref']; ?>"<?php if ($data['parent_id'] == $list[$i]['ref']) { ?> selected<?php } ?>><?php echo $list[$i]['category_title']; ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="form-group">
    <label for="status">Status</label>
    <select class="form-control" id="status" name="status" required>
      <option value="ACTIVE"<?php if ($data['status'] == "ACTIVE") { ?> selected<?php } ?>>Active</option>
      <option value="INACTIVE"<?php if ($data['status'] == "INACTIVE") { ?> selected<?php } ?>>In-Active</option>
    </select>
  </div>
  <input type="hidden" name="ref" value="<?php echo $edit; ?>">
  <input type="hidden" name="country" value="<?php echo $country->getCountryData($_SESSION['location']['code'], "ref", "code"); ?>">
  <button type="submit" name="submitCat" id="submitCat" class="btn purple-bn1"><?php echo $tag; ?></button>
  <?php if ($edit > 0) { ?>
  <button type="button" class="btn purple-bn1" onClick="location='<?php echo $redirect; ?>'" >Cancel</button>
  <button type="button" class="btn purple-bn1" onClick="location='<?php echo URL; ?>admin/category/createQuestionare?edit=<?php echo $edit; ?>'" >Manage Questionaire</button>
  <?php } ?>
  </form>
</main>
</div>

<script type="text/javascript">
$("#fileUpload").on('change', function () {

  //Get count of selected files
  var countFiles = $(this)[0].files.length;

  var imgPath = $(this)[0].value;
  var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
  var image_holder = $("#image-holder");
  image_holder.empty();

  if (extn == "png") {
    if (typeof (FileReader) != "undefined") {
      //loop for each file selected for uploaded.
      for (var i = 0; i < countFiles; i++) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $("<img />", {
              "src": e.target.result,
                  "class": "thumb-image"
          }).appendTo(image_holder);
        }

        image_holder.show();
        reader.readAsDataURL($(this)[0].files[i]);
      }
    } else {
      alert("This browser does not support FileReader.");
    }
  } else {
    alert("Pls select only PNG images");
  }
});
</script>
<?php }

    function listAll($redirect) {
      global $country;
      global $options;

      if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
      } else {
        $page = 0;
      }
      
      $limit = $options->get("result_per_page");
      $start = $page*$limit;
      $list = $this->getList($start, $limit);
      $listCount = $this->getList(false, false, "ref", "ASC", "count"); ?>
        <h2>List All Categories</h2>
<table class="table table-striped">
<thead>
<tr>
  <th scope="col">#</th>
  <th scope="col">Category</th>
  <th scope="col">Parent Category</th>
  <th scope="col">Country</th>
  <th scope="col">Status</th>
  <th scope="col">Created</th>
  <th scope="col">Last Modified</th>
  <th scope="col">&nbsp;</th>
</tr>
</thead>
<tbody>
  <?php for ($i = 0; $i < count($list); $i++) {
    if ($list[$i]['status'] == "ACTIVE") {
      $statusTag = "De-activate";
    } else if ($list[$i]['status'] == "INACTIVE") {
      $statusTag = "Activate";
    }
      ?>
<tr>
  <th scope="row"><?php echo $start+$i+1; ?></th>
  <td><img src="<?php echo $this->getIcon($list[$i]['ref']); ?>" height="25" width="25">&nbsp;<?php echo $list[$i]['category_title']; ?></td>
  <td><?php echo $this->getSingle($list[$i]['parent_id']); ?></td>
  <td><?php echo $country->getSingle( $list[$i]['country'] ); ?></td>
  <td><?php echo $list[$i]['status']; ?></td>
  <td><?php echo $list[$i]['create_time']; ?></td>
  <td><?php echo $list[$i]['modify_time']; ?></td>
  <td><a href="<?php echo URL.$redirect."/create?edit=".$list[$i]['ref']; ?>">Edit</a> | <a href="<?php echo URL.$redirect."/createQuestionare?edit=".$list[$i]['ref']; ?>">Manage Questionaire</a> | <a href="<?php echo URL.$redirect."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this category, all sub categories under the category will also be <?php echo $statusTag; ?>d. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a> | <a href="<?php echo URL.$redirect."?delete=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this category. are you sure you want to continue ?')">Delete</a></td>
</tr>
  <?php } ?>
</tbody>
</table>
    <?php $this->pagination($page, $listCount);
    }

    private function postImage($id, $file) {
      global $media;
      
      $upload = $media->uploadIcon($id, $file['img']);
      if ($upload) {
          if ($upload['title'] == "OK") {
              $this->updateOne("category", "image_url", $upload['desc'], $id, "ref");
          }
          
          return $upload;
      } else {
          return false;
      }
    }

    function postMew($array, $file) {
      if ($array['ref'] == 0) {
        unset($array['ref']);
      }

      $add = $this->create($array);

      if ($add) {
        if ($file != false) {
          $this->postImage($add, $file);
        }
        return $add;
      } else {
        return false;
      }
    }

    function removeCate($id) {
      $add = $this->remove($id);

      if ($add) {
          return true;
      } else {
          return false;
      }
    }

    function removeImage($id) {
      $add = $this->updateOne("category", "image_url", "", $id, "ref");

      if ($add) {
          return true;
      } else {
          return false;
      }
    }
  }
?>