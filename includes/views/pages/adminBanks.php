<?php
  class adminBanks extends banks {
    public function navigationBar($redirect) { ?>
      <p><i class="fa fa-caret-right mr-3"></i><a href="<?php echo URL.$redirect; ?>"><b>List All</b></p>
      <div class="moba-line my-2"></div>
      <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/create"; ?>"><b>Add New</b></a></p>	
    <?php }

    function pageContent($redirect, $view="list", $edit=0) {
      if ($view == "list") {
          $this->listAll($redirect);
      } else {
          $this->createNew($redirect, $edit);
      }
    }

    function createNew($redirect, $edit=0) {
        global $country;
      $list = $country->getList();
      if ($edit > 0) {
          $data = $this->getOne("banks", $edit, "ref");
          $tag = "Edit ".$data['name'];
      } else {
          $data = false;
          $tag = "Add Bank";
      }
             ?>
  <main class="col-12" role="main">
  <form method="post" action="" enctype="multipart/form-data">
  <h2><?php echo $tag; ?></h2>
  <div class="form-group">
      <label for="name">Bank Name</label>
      <input type="text" class="form-control" name="name" id="name" placeholder="Enter Bank Name" required value="<?php echo $data['name']; ?>">
  </div>
  <div class="form-group">
      <label for="financial_institution">Bank Institution/Routing Number</label>
      <input type="text" class="form-control" name="financial_institution" id="financial_institution" placeholder="Enter Institution Number" required value="<?php echo $data['financial_institution']; ?>">
  </div>
  <div class="form-group">
    <label for="parent_id">Country</label>
    <select class="form-control" id="country" name="country" required>
      <?php for ($i = 0; $i < count($list); $i++) { ?>
      <option value="<?php echo $list[$i]['ref']; ?>"<?php if ($data['country'] == $list[$i]['ref']) { ?> selected<?php } else if ($_SESSION['location']['country'] == $list[$i]['name']) { ?> selected<?php } ?>><?php echo $list[$i]['name']; ?></option>
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
  <button type="submit" name="submitCat" class="btn purple-bn1"><?php echo $tag; ?></button>
  <?php if ($edit > 0) { ?>
  <button type="button" class="btn purple-bn1" onClick="location='<?php echo $redirect; ?>'" >Cancel</button>
  <?php } ?>
  </form>
</main>
</div>
<?php }

    function listAll($redirect) {
      global $options;
      global $country;

      if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
      } else {
        $page = 0;
      }
      
      $limit = $options->get("result_per_page");
      $start = $page*$limit;
      $list = $this->getList($start, $limit);
      $listCount = $this->getList(false, false, "ref", "ASC", "count"); ?>
        <h2>List All Banks</h2>
<table class="table table-striped">
<thead>
<tr>
  <th scope="col">#</th>
  <th scope="col">Bank Name</th>
  <th scope="col">Institution Number</th>
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
  <td><?php echo $list[$i]['name']; ?></td>
  <td><?php echo $list[$i]['financial_institution']; ?></td>
  <td><?php echo $country->getSingle( $list[$i]['country'] ); ?></td>
  <td><?php echo $list[$i]['status']; ?></td>
  <td><?php echo $list[$i]['create_time']; ?></td>
  <td><?php echo $list[$i]['modify_time']; ?></td>
  <td><a href="<?php echo URL.$redirect."/create?edit=".$list[$i]['ref']; ?>">Edit</a> | <a href="<?php echo URL.$redirect."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this category, all sub categories under the category will also be <?php echo $statusTag; ?>d. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a> | <a href="<?php echo URL.$redirect."?delete=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this category. are you sure you want to continue ?')">Delete</a></td>
</tr>
  <?php } ?>
</tbody>
</table>
    <?php $this->pagination($page, $listCount);
    }

    function postMew($array) {
      if ($array['ref'] == 0) {
        unset($array['ref']);
      }

      $add = $this->create($array);

      if ($add) {
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
  }
?>