<?php
    class userBankAccount extends bank_account {
        function pageContent($redirect, $view="list", $edit=0) {
            if ($view == "list") {
                $this->listAll($redirect);
            } else if ($view == "confirm") {
                $this->reviewBank($edit);
            } else if ($redirect == "wallet") {
                $this->bank($edit, $view);
                $this->listAllWallet($_SESSION['users']['ref'], $view);
            } else {
                $this->createNew($edit);
            }
        }

        function createNew($edit) {
            global $banks;
            $list = $banks->getSortedList("ACTIVE", "status");
            $data = $this->listOne($edit);
            $error = false;
            if ($edit > 0) {
                $tag = "Modify Bank Account";
                $tag2 = "Update Changes";
                $region = $data['region'];

                if ($_SESSION['users']['ref'] != $data['user_id']) {
                    $error = true;
                }
            } else {
                $tag = "Create New Bank Account";
                $tag2 = "Create Bank Account";
                $region = $_SESSION['location']['code'];
            }
            if ($error === false) {
            if ($region == "CA") {
            ?>
                <main class="col-12" role="main">
                    <form method="post" action="" enctype="multipart/form-data" autocomplete="off">
                    <h2><?php echo $tag; ?></h2>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="<?php echo $data['last_name']; ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="<?php echo $data['first_name']; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="financial_institution">Financial Institution</label>
                        <div class="form-row">
                            <div class="col-md-12">
                                <select class="form-control" id="financial_institution" name="financial_institution" required>
                                    <option value="">Select One</option>
                                    <?php for ($i = 0; $i < count($list); $i++) { ?>
                                        <option value="<?php echo $list[$i]['ref']; ?>"<?php if ($data['financial_institution'] == $list[$i]['ref']) { ?> selected<?php } ?>><?php echo $list[$i]['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="transit_number">Bank Transit Number</label>
                            <input type="number" class="form-control" name="transit_number" id="transit_number" placeholder="Transit Number" value="<?php echo $data['transit_number']; ?>" max="99999" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="account_number">Account Number</label>
                            <input type="number" class="form-control" name="account_number" id="account_number" placeholder="Account Number" value="<?php echo $data['account_number']; ?>" required>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['users']['ref']; ?>">
                    <input type="hidden" name="ref" value="<?php echo $edit; ?>">
                    <input type="hidden" name="region" value="<?php echo $region; ?>">
                    <button type="submit" name="getPayment" class="btn btn-primary"><?php echo $tag2; ?></button>
                    </form>
                </main>
            <?php } else if ($region == "US") { ?>
                <main class="col-12" role="main">
                    <form method="post" action="" enctype="multipart/form-data" autocomplete="off">
                    <h2><?php echo $tag; ?></h2>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="<?php echo $data['last_name']; ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="<?php echo $data['first_name']; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="transit_number">Transit Routing Number</label>
                        <div class="form-row">
                            <div class="col-md-12">
                                <input type="number" class="form-control" name="transit_number" id="transit_number" value="<?php echo $data['transit_number']; ?>" placeholder="Transit Routing Number">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="account_code">Account type</label>
                            <select name="account_code" id="account_code" class="form-control">
                                <option value="PC"<?php if ($data['account_code'] == "PC") {?> selected<?php } ?>>Personal Checking</option>
                                <option value="PS"<?php if ($data['account_code'] == "PS") {?> selected<?php } ?>>Personal Savings</option>
                                <option value="CC"<?php if ($data['account_code'] == "CC") {?> selected<?php } ?>>Corporate Checking</option>
                                <option value="CS"<?php if ($data['account_code'] == "CS") {?> selected<?php } ?>>Corporate Savings</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="account_number">Account Number</label>
                            <input type="number" class="form-control" name="account_number" id="account_number" value="<?php echo $data['account_number']; ?>" placeholder="Account Number">
                        </div>
                    </div>
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['users']['ref']; ?>">
                    <input type="hidden" name="ref" value="<?php echo $edit; ?>">
                    <input type="hidden" name="region" value="<?php echo $region; ?>">
                    <button type="submit" name="getPayment" class="btn btn-primary"><?php echo $tag2; ?></button>
                    </form>
                </main>
            <?php } else { ?>
                <div class="alert alert-danger" role="alert">
                    <strong>You can not add a bank account for your current location</strong>
                </div>
            <?php }
            } else { ?>
                <div class="alert alert-danger" role="alert">
                    <strong>You are not permitted to view this page</strong>
                </div>
            <?php }
        }

        public function listAll($redirect) {
            global $options;
            global $banks;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            
            $data = $this->listAllUserData($_SESSION['users']['ref'], $start, $limit);
            $list = $data['list'];
            $listCount = $data['listCount']; ?>
            <h2>List All Bank Account</h2>
            <small>You must have atleast one bank account active and can not remove a default bank account. To remove a default bank account, you must activate another bank account as default</small>
<form method="post" action="" enctype="multipart/form-data">
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Default</th>
      <th scope="col">Name on Account</th>
      <th scope="col">Account Details</th>
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
      <td><?php echo $list[$i]['last_name']." ".$list[$i]['first_name']; ?></td>
      <td><?php echo trim( $banks->getSingle( $list[$i]['financial_institution'] ))." *****".substr($list[$i]['account_number'], -4); ?></td>
      <td><?php echo $list[$i]['status']; ?></td>
      <td><?php echo $list[$i]['create_time']; ?></td>
      <td><?php echo $list[$i]['modify_time']; ?></td>
      <td><a href="<?php echo URL.$redirect."/create?edit=".$list[$i]['ref']; ?>">edit</a> | <a href="<?php echo URL.$redirect."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this card. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a><?php if ((count($list) > 1) && ($list[$i]['is_default'] != 1)) { ?> | <a href="<?php echo URL.$redirect."?delete=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this card. are you sure you want to continue ?')">Delete</a><?php } ?></td>
    </tr>
      <?php } ?>
  </tbody>
</table>
<?php if (count($list) > 1) { ?>
<button type="submit" name="set_is_default" class="btn btn-primary">Set Default Bank Account</button>
<?php } ?>
<?php $this->pagination($page, $listCount); ?>
</form>
        <?php }

        function postMew($array) {
            $add = $this->create($array);
            if ($add) {
                return $add;
            } else {
                return false;
            }
        }

        function removeCard($id) {
            $add = $this->remove($id);

            if ($add) {
                return true;
            } else {
                return false;
            }
        }

        public function navigationBar($redirect) { ?>
            <a href="<?php echo URL.$redirect; ?>">List All</a> | <a href="<?php echo URL.$redirect."/create"; ?>">Add New</a>
       <?php }
    }
?>