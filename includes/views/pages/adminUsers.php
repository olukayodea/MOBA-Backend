<?php
  class adminUsers extends users {
    function pageContent($redirect, $view="users", $ref=false) {
      if ($view == "oneView") {
        $this->viewDetails($ref);
      } else if ($view == "users") {
        $this->listUsers($redirect, $view);
      } else if ($view == "verification") {
        $this->verification();
      } else {
        $this->listAdmin($redirect, $view);
      }
    }

    private function verification() {
      global $options;
  
      if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
      } else {
        $page = 0;
      }
      
      $limit = $options->get("result_per_page");
      $start = $page*$limit;

      $list = $this->getSortedList("1", "verified", false, false, false, false, "ref", "ASC", "AND", $start, $limit);
      $listCount = $this->getSortedList("1", "verified", false, false, false, false, "ref", "DESC", "AND", false, false, "count"); ?>
      <h2>List All Pending Verification (<?php echo count($list); ?>)</h2>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Last Name</th>
      <th scope="col">Other Names</th>
      <th scope="col">Screen Name</th>
      <th scope="col">Email</th>
      <th scope="col">Status</th>
      <th scope="col">Created</th>
      <th scope="col">Last Modified</th>
      <th scope="col">&nbsp;</th>
    </tr>
  </thead>
  <tbody>
      <?php for ($i = 0; $i < count($list); $i++) { ?>
    <tr>
      <th scope="row"><?php echo $start+$i+1; ?></th>
      <td><?php echo $list[$i]['last_name']; ?></td>
      <td><?php echo $list[$i]['other_names']; ?></td>
      <td><?php echo $list[$i]['screen_name']; ?></td>
      <td><?php echo $list[$i]['email']; ?></td>
      <td><?php echo $list[$i]['status']; ?></td>
      <td><?php echo $list[$i]['create_time']; ?></td>
      <td><?php echo $list[$i]['modify_time']; ?></td>
      <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['ref']; ?>">View User</a></td>
    </tr>
      <?php } ?>
  </tbody>
</table>
<?php $this->pagination($page, $listCount);
}

  private function viewDetails($ref) {
    global $users;
    global $projects;
    global $media;
    global $transactions;
    global $country;
    global $options;

    if (isset($_REQUEST['page'])) {
      $page = $_REQUEST['page'];
    } else {
      $page = 0;
    }
    
    $limit = $options->get("result_per_page");
    $start = $page*$limit;
    
    $data = $users->listOne($ref);

    $jobs = $projects->getSortedList("ACTIVE", "status", "user_id", $ref, false, false, "ref", "DESC", "AND", 0, 10);

    $ongoing = $projects->getList(false, false, "ref", "DESC", "`status` = 'ON-GOING' AND (`user_id` = ".$ref." OR `client_id` = ".$ref.")");
    $activeTask = $projects->getList(false, false, "ref", "DESC", "`status` = 'ON-GOING' AND ((`user_id` = ".$ref." AND `project_type` = 'vendor') OR (`client_id` = ".$ref." AND `project_type` = 'client'))");

    if ($data['status'] == "ACTIVE") {
      $statusTag = "De-activate";
    } else if ($data['status'] == "INACTIVE") {
      $statusTag = "Activate";
    }
    if ($data['user_type'] == "1") {
      $view = "admin";
      $tag = "Make User";
    } else if ($data['user_type'] == "0") {
      $view = "users";
      $tag = "Make Admin";
    } ?>
    <a href="javascript:history.go(-1);">Back</a>
    <div class="row">
      <div class="card col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <?php $users->getProfileImage($ref, "card-img-top", "50"); ?>
      </div>
      <div class="card col-xs-12 col-sm-12 col-md-9 col-lg-9">
      <div class="card-body">
        <h5 class="card-title"><?php echo $data['last_name']." ".$data['other_names']; ?></h5>
        <h6 class="card-subtitle mb-2 text-muted"><?php echo $data['screen_name']; ?></h6>
        <p class="card-text">Email<br>
        <strong><?php echo $data['email']; ?></strong></p>

        <?php if ($_SESSION['users']['ref'] != $data['ref']) { ?>
          <a href="<?php echo URL."admin/users/".$view."/users?edit=".$data['ref']; ?>" onClick="return confirm('this action will mnake this user an admin. are you sure you want to continue ?')"><?php echo $tag; ?></a>
          <?php } ?>
          <a href="<?php echo URL."inbox/compose?user=".$data['ref']; ?>">Send Message</a>
          <?php if ($_SESSION['users']['ref'] != $data['ref']) { ?>
            <a href="<?php echo URL."admin/users/".$view."?statusChange=".$data['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this user. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php if ($data['verified'] == 1) {
      $extension = $this->getExtension($data['gov_id_url']); ?>
      <h4>Pending Verification</h4>
      <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
          <?php if ($extension == "pdf") { ?>
            <embed src="<?php echo URL.$data['gov_id_url']; ?>" width="600" height="500" alt="pdf" pluginspage="http://www.adobe.com/products/acrobat/readstep2.html">
          <?php } else { ?>
            <img src="<?php echo URL.$data['gov_id_url']; ?>" width="500">
          <?php } ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <form method="post" action="">
          <input type="hidden" name="ref" value="<?php echo $ref; ?>">
          <button type="submit" name="approve" class="btn btn-primary">Validate</button>
          <button type="submit" name="reject" class="btn btn-secondary">Reject</button>
        </div>
      </div>
    <?php } ?>
    <?php    
    
    $list = $transactions->getSortedList($ref, "user_id", false, false, false, false, "card_name", "ASC", "AND", $start, $limit); 
    $listCount = $transactions->getSortedList($ref, "user_id", false, false, false, false, "card_name", "ASC", "AND", false, false, "count"); ?>
    <h4>Cards</h4>
    <table class="table">
    <thead>
    <tr>
    <th scope="col">#</th>
    <th scope="col">Default</th>
    <th scope="col">PAN</th>
    <th scope="col">Expiry Date</th>
    <th scope="col">Status</th>
    <th scope="col">Created</th>
    <th scope="col">Last Modified</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i = 0; $i < count($list); $i++) { ?>
    <tr>
    <th scope="row"><?php echo $start+$i+1; ?></th>
    <td><input class="form-check-input" type="radio" name="is_default" value="<?php echo $list[$i]['ref']; ?>"<?php if ($list[$i]['is_default'] == 1) { ?> checked<?php } ?>></td>
    <td><?php echo "**** **** **** ".$list[$i]['pan']; ?></td>
    <td><?php echo $list[$i]['expiry_month']."/".$list[$i]['expiry_year']; ?></td>
    <td><?php echo $list[$i]['status']; ?></td>
    <td><?php echo $list[$i]['create_time']; ?></td>
    <td><?php echo $list[$i]['modify_time']; ?></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
    <?php $this->pagination($page, $listCount);
    if (isset($_REQUEST['page_num'])) {
      $page_num = $_REQUEST['page_num'];
    } else {
      $page_num = 0;
    }
    
    $limit = $options->get("result_per_page");
    $start = $page_num*$limit;
    $list = $transactions->getSortedListTrans($ref, "user_id", false, false, false, false, "ref", "DESC", "AND", $start, $limit); 
    $listCount = $transactions->getSortedListTrans($ref, "user_id", false, false, false, false, "ref", "DESC", "AND", false, false, "count"); ?>
    <h4>Transaction</h4>
    <table class="table">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Ref</th>
          <th scope="col">Owner</th>
          <th scope="col">Tx Type</th>
          <th scope="col">Net</th>
          <th scope="col">Tax</th>
          <th scope="col">Gros</th>
          <th scope="col">Status</th>
          <th scope="col">Created</th>
          <th scope="col">Last Modified</th>
        </tr>
      </thead>
      <tbody>
          <?php for ($i = 0; $i < count($list); $i++) { ?>
        <tr>
          <th scope="row"><?php echo $start+$i+1; ?></th>
          <td><a href="<?php echo URL."admin/transactions.view?ref=".$list[$i]['ref']; ?>"><?php echo $transactions->txid( $list[$i]['ref'] ); ?></a></td>
          <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['user_id']; ?>"><?php echo $users->listOnValue( $list[$i]['user_id'], "last_name")." ".$users->listOnValue( $list[$i]['user_id'], "other_names"); ?></a></td>
          <td><?php echo $transactions->url( $list[$i]['tx_type'], $list[$i]['tx_type_id'] ); ?></td>
          <td><?php echo $country->getSingle($list[$i]['region'], "currency_symbol", "ref")." ". $list[$i]['net_total']." (".$list[$i]['tx_dir'].")"; ?></td>
          <td><?php echo $country->getSingle($list[$i]['region'], "currency_symbol", "ref")." ".$list[$i]['tax_total']." (".$list[$i]['tx_dir'].")"; ?></td>
          <td><?php echo $country->getSingle($list[$i]['region'], "currency_symbol", "ref")." ".$list[$i]['gross_total']." (".$list[$i]['tx_dir'].")"; ?></td>
          <td><?php echo $list[$i]['gateway_status']; ?></td>
          <td><?php echo $list[$i]['create_time']; ?></td>
          <td><?php echo $list[$i]['modify_time']; ?></td>
        </tr>
          <?php } ?>
      </tbody>
    </table>
    <?php $this->pagination($page_num, $listCount, "page_num"); ?>
    <h4>Active Assigned Tasks</h4>
    <div class="row">
        <?php for ($i = 0; $i < count($activeTask); $i++) { ?>
    <div class="card" style="width: 20rem;">
    <img class="card-img-top" src="<?php echo $media->getCover($activeTask[$i]['ref']); ?>" alt="<?php echo $activeTask[$i]['project_name']; ?>">
    
    <div class="card-body">
        <h5 class="card-title"><?php echo $activeTask[$i]['project_name']; ?></h5>
    </div>
    <div class="card-footer">
        <a href="<?php echo $this->seo($activeTask[$i]['ref'], "profile"); ?>" class="btn btn-primary">Open</a>
        
    </div>
    </div>
        <?php } ?>

    </div>
    <h4>Actively Listed Jobs</h4>
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
    <h4>Ongoing Jobs</h4>
    <div class="row">
        <?php for ($i = 0; $i < count($ongoing); $i++) { ?>
    <div class="card" style="width: 20rem;">
    <img class="card-img-top" src="<?php echo $media->getCover($ongoing[$i]['ref']); ?>" alt="<?php echo $ongoing[$i]['project_name']; ?>">
    
    <div class="card-body">
        <h5 class="card-title"><?php echo $ongoing[$i]['project_name']; ?></h5>
    </div>
    <div class="card-footer">
        <a href="<?php echo $this->seo($ongoing[$i]['ref'], "profile"); ?>" class="btn btn-primary">Open</a>
        
    </div>
    </div>
        <?php } ?>

    </div>
<?php }

  private function listUsers($redirect, $view) {
    global $options;

    if (isset($_REQUEST['page'])) {
      $page = $_REQUEST['page'];
    } else {
      $page = 0;
    }
    
    $limit = $options->get("result_per_page");
    $start = $page*$limit;
    $list = $this->getSortedList("0", "user_type", false, false, false, false, "ref", "ASC", "AND", $start, $limit); 
    $listCount = $this->getSortedList("0", "user_type", false, false, false, false, "ref", "ASC", "AND", false, false, "count"); ?>
    <h2>List All System Users</h2>
<table class="table">
<thead>
  <tr>
    <th scope="col">#</th>
    <th scope="col">Last Name</th>
    <th scope="col">Other Names</th>
    <th scope="col">Screen Name</th>
    <th scope="col">Email</th>
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
    <td><?php echo $list[$i]['last_name']; ?></td>
    <td><?php echo $list[$i]['other_names']; ?></td>
    <td><?php echo $list[$i]['screen_name']; ?></td>
    <td><?php echo $list[$i]['email']; ?></td>
    <td><?php echo $list[$i]['status']; ?></td>
    <td><?php echo $list[$i]['create_time']; ?></td>
    <td><?php echo $list[$i]['modify_time']; ?></td>
    <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['ref']; ?>">View User</a> | <?php if ($_SESSION['users']['ref'] != $list[$i]['ref']) { ?><a href="<?php echo URL.$redirect."/".$view."/users?edit=".$list[$i]['ref']; ?>" onClick="return confirm('this action will mnake this user an admin. are you sure you want to continue ?')">Make Admin</a> | <?php } ?><a href="<?php echo URL."inbox/compose?user=".$list[$i]['ref']; ?>">Send Message</a><?php if ($_SESSION['users']['ref'] != $list[$i]['ref']) { ?> | <a href="<?php echo URL.$redirect."/".$view."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this user. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a><?php } ?></td>
  </tr>
    <?php } ?>
</tbody>
</table>
<?php $this->pagination($page, $listCount);
  }

  function listAdmin($redirect, $view) {
    global $options;

    if (isset($_REQUEST['page'])) {
      $page = $_REQUEST['page'];
    } else {
      $page = 0;
    }
    
    $limit = $options->get("result_per_page");
    $start = $page*$limit;
    $list = $this->getSortedList("1", "user_type", false, false, false, false, "ref", "ASC", "AND", $start, $limit); 
    $listCount = $this->getSortedList("1", "user_type", false, false, false, false, "ref", "ASC", "AND", false, false, "count"); ?>
      <h2>List All System Administrators</h2>
<table class="table">
<thead>
<tr>
  <th scope="col">#</th>
  <th scope="col">Last Name</th>
  <th scope="col">Other Names</th>
  <th scope="col">Screen Name</th>
  <th scope="col">Email</th>
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
  <td><?php echo $list[$i]['last_name']; ?></td>
  <td><?php echo $list[$i]['other_names']; ?></td>
  <td><?php echo $list[$i]['screen_name']; ?></td>
  <td><?php echo $list[$i]['email']; ?></td>
  <td><?php echo $list[$i]['status']; ?></td>
  <td><?php echo $list[$i]['create_time']; ?></td>
  <td><?php echo $list[$i]['modify_time']; ?></td>
  <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['ref']; ?>">View User</a> | <?php if ($_SESSION['users']['ref'] != $list[$i]['ref']) { ?><a href="<?php echo $redirect."/".$view."/admin?edit=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this user as an admin. are you sure you want to continue ?')">Revoke Admin</a> | <?php } ?><a href="<?php echo URL."inbox/compose?user=".$list[$i]['ref']; ?>">Send Message</a><?php if ($_SESSION['users']['ref'] != $list[$i]['ref']) { ?> | <a href="<?php echo URL.$redirect."/".$view."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this user. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a><?php } ?></td>
</tr>
  <?php } ?>
</tbody>
</table>
<?php $this->pagination($page, $listCount);
    }

  }
?>