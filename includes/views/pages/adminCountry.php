<?php
  class adminCountry extends country {
    public function navigationBar($redirect) { ?>
<a href="<?php echo URL.$redirect; ?>">List All</a> | <a href="<?php echo URL.$redirect."/create"; ?>">Add New</a>
    <?php }

    function pageContent($redirect, $view="list", $edit=0) {
      if ($view == "list") {
        $this->listAll($redirect);
      } else {
        $this->createNew($redirect, $edit);
      }
    }

    function createNew($redirect, $edit=0) {
      if ($edit > 0) {
        $data = $this->getOne("country", $edit, "ref");
        $tag = "Edit ".$data['name'];
        $tag2 = "Save Changes";
      } else {
        $data = false;
        $tag = "Create Country";
        $tag2 = "Create Country";
      }
      ?>
      <main class="col-12" role="main">
      <form method="post" action="" enctype="multipart/form-data">
      <h2><?php echo $tag; ?></h2>
      <div class="form-group">
          <label for="name">Country Name</label>
          <input type="text" class="form-control" name="name" id="name" placeholder="Enter Country Name" required value="<?php echo $data['name']; ?>">
      </div>
      <div class="form-group">
          <label for="code">Country Code</label>
          <input type="text" class="form-control" name="code" id="code" placeholder="Enter Country Code" required value="<?php echo $data['code']; ?>">
      </div>
      <div class="form-group">
          <label for="currency">Country Currency Code</label>
          <input type="text" class="form-control" name="currency" id="currency" placeholder="Enter Country Currency Code" required value="<?php echo $data['currency']; ?>">
      </div>
      <div class="form-group">
          <label for="currency">Country Currency Symbol</label>
          <input type="text" class="form-control" name="currency_symbol" id="currency_symbol" placeholder="Enter Country Currency Symbol" required value="<?php echo $data['currency_symbol']; ?>">
      </div>
      <div class="form-group">
          <label for="dial_code">Country Dial Code</label>
          <input type="text" class="form-control" name="dial_code" id="dial_code" placeholder="Enter Country Dialing Code" required value="<?php echo $data['dial_code']; ?>">
      </div>
      <div class="form-group">
          <label for="featured_ad">Amount Charged Daily for Featured Ad</label>
          <input type="number" class="form-control" name="featured_ad" id="featured_ad" placeholder="Enter Local amount Charged Daily for Featured Ad" required value="<?php echo $data['featured_ad']; ?>">
      </div>
      <div class="form-group">
          <label for="tax">Tax Rate</label>
          <input type="number" class="form-control" name="tax" id="tax" placeholder="Enter tax percentage on all items" required value="<?php echo $data['tax']; ?>">
      </div>
      <div class="form-group">
        <label for="si_unit">SI Unit</label>
        <select class="form-control" id="si_unit" name="si_unit" required>
          <option value="imperial"<?php if ($data['si_unit'] == "imperial") { ?> selected<?php } ?>>Imperial</option>
          <option value="metric"<?php if ($data['si_unit'] == "metric") { ?> selected<?php } ?>>Metric</option>
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
      <button type="submit" name="submitCountry" class="btn purple-bn1"><?php echo $tag2; ?></button>
      <?php if ($edit > 0) { ?>
      <button type="button" class="btn purple-bn1" onClick="location='<?php echo URL.$redirect; ?>'" >Cancel</button>
      <?php } ?>
      </form>
    </main>
    </div>
  <?php }

    function listAll($redirect) {
      global $options;

      if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
      } else {
        $page = 0;
      }
      
      $limit = $options->get("result_per_page");
      $start = $page*$limit;
      $list = $this->getList($start, $limit);
      $listCount = $this->getList(false, false, "count"); ?>
        <h2>List All Countries</h2>
<form method="post" action="" enctype="multipart/form-data">
<table class="table table-striped">
<thead>
<tr>
  <th scope="col">#</th>
  <th scope="col">Default</th>
  <th scope="col">Name</th>
  <th scope="col">Code</th>
  <th scope="col">Currency</th>
  <th scope="col">SI Unit</th>
  <th scope="col">Featured</th>
  <th scope="col">Tax</th>
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
    } ?>
<tr>
  <th scope="row"><?php echo $start+$i+1; ?></th>
  <td><input class="form-check-input" type="radio" name="is_default" value="<?php echo $list[$i]['ref']; ?>"<?php if ($list[$i]['is_default'] == 1) { ?> checked<?php } ?>></td>
  <td><?php echo $list[$i]['name']; ?></td>
  <td><?php echo $list[$i]['code']. " (".$list[$i]['dial_code'].")"; ?></td>
  <td><?php echo $list[$i]['currency']." ".$list[$i]['currency_symbol']; ?></td>
  <td><?php echo $list[$i]['si_unit']; ?></td>
  <td><?php echo $list[$i]['currency_symbol']." ".$list[$i]['featured_ad']; ?></td>
  <td><?php echo $list[$i]['tax']."%"; ?></td>
  <td><?php echo $list[$i]['status']; ?></td>
  <td><?php echo $list[$i]['create_time']; ?></td>
  <td><?php echo $list[$i]['modify_time']; ?></td>
  <td><a href="<?php echo URL.$redirect."/create?edit=".$list[$i]['ref']; ?>">Edit</a> | <a href="<?php echo URL.$redirect."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this country. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a><?php if (count($list) > 1) { ?> | <a href="<?php echo URL.$redirect."?delete=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this country. are you sure you want to continue ?')">Delete</a><?php } ?></td>
</tr>
  <?php } ?>
</tbody>
</table>
<button type="submit" name="set_is_default" class="btn purple-bn1">Set Default Country</button>
<?php $this->pagination($page, $listCount); ?>
</form>
    <?php }

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

    function removeCountry($id) {
      $add = $this->remove($id);

      if ($add) {
          return true;
      } else {
          return false;
      }
    }
  }
?>