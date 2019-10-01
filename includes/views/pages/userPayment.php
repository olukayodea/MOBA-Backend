<?php
    class userPayment extends wallet {
        function pageContent($redirect, $view="list", $edit=0) {
            if ($view == "list") {
                $this->listAll($redirect);
            } else if ($view == "confirm") {
                $this->reviewBank($edit);
            } else if ($redirect == "wallet") {
                $this->bank($edit, $view, $redirect);
                $this->listAllWallet($_SESSION['users']['ref'], $view);
            } else {
                $this->createNew();
            }
        }

        public function navigationBarWallet($redirect, $ref) {
            $list = $this->availableRegion($ref);
            for ($i = 0; $i < count($list); $i++) { ?>
            <a href="<?php echo URL.$redirect."/".$list[$i]['code']; ?>"><?php echo $list[$i]['name']." (".$list[$i]['code'].")"; ?></a><?php if ($i < count($list)-1) { echo "|"; } ?>
            <?php } 
        }

        private function bankHeader($ref, $regionData) { ?>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <p><i class="fas fa-piggy-bank"></i></i>&nbsp;Current Balance:&nbsp;<?php echo $regionData['currency_symbol']." ".number_format($this->balance($ref, $regionData['ref'], true), 2); ?></p>
                    <p><i class="fas fa-piggy-bank"></i></i>&nbsp;Available Balance:&nbsp;<?php echo $regionData['currency_symbol']." ".number_format($this->balance($ref, $regionData['ref']), 2); ?></p>
                </div>
            </div>
            
            <script language="javascript">
                var balance = '<?php echo number_format($this->balance($ref, $regionData['ref']), 2); ?>';
            </script>
        <?php }

        public function postWallet($array, $status = 1, $desc=false) {
            unset($array['postWallet']);
            $type = $array['type'];
            unset($array['type']);
            $array['tx_type_id'] = 0;
            $array['tx_type'] = "wallet";
            $array['tax_total'] = 0;
            $array['gross_total'] = $array['net_total'];
            $post = $this->createTx($array);
            if ($array['tx_dir'] == "CR") {
                $makePayment = $this->bambora_pay($array);
                if (($makePayment['approved'] == 1) && ($makePayment['message'] == "Approved")) {
                    $return['code'] = 1;
                    $return['message'] = $makePayment['message'];
                    $this->updateOne("transactions", "status", 2, $post, "ref");

                    $array['status'] = $status;
                    if ($desc == false) {
                        $array['tx_desc'] = $array['tx_dir']." transaction in wallet"; 
                    } else {
                        $array['tx_desc'] = $desc;
                    }
                    $array['amount'] = $array['gross_total'];
                    $array['tx_id'] = $post;
                    unset($array['gross_total']);
                    unset($array['tax_total']);
                    unset($array['net_total']);
                    unset($array['tx_type_id']);
                    unset($array['tx_type']);
                    unset($array['card']);

                    $wallet_id = $this->createWallet($array);
                    $this->updateOne("transactions", "tx_type_id", $wallet_id, $post, "ref");

                } else if ($makePayment['code'] != 1) {
                    $return['code'] = $makePayment['code'];
                    $return['message'] = $makePayment['message'];
                    
                    $this->updateOne("transactions", "status", 1, $post, "ref");
                }
                $this->updateOne("transactions", "gateway_data", serialize($makePayment), $post, "ref");
                $this->updateOne("transactions", "gateway_status", $makePayment['message'], $post, "ref");
            } else if ($array['tx_dir'] == "DR") {
                global $payments;
                global $country;
                $regionData = $country->getLoc($array['region'], "ref");

                $balance = $this->balance($array['user_id'], $regionData['ref']);
                if ($balance >= $array['gross_total']) {
                    $array['type'] = $type;
                    $array['tx_id'] = $post;

                    $pay['tx_id'] = $post;
                    $pay['user_id'] = $array['user_id'];
                    $pay['user_profile_id'] = $array['card'];
                    $pay['user_profile_type'] = $type;
                    $pay['amount'] = $array['net_total'];
                    $pay['region'] = $array['region'];
                    $pay['send_time'] = mktime(23,59, 59, date("m"), date("d"), date("Y"));

                    $queue = $payments->create($pay);
                    if ($queue) {
                        $pay['tx_desc'] = $array['tx_dir']." transaction in wallet"; 
                        $pay['tx_dir'] = $array['tx_dir'];
                        $pay['region'] = $array['region'];
                        $pay['status'] = 2;
                        $pay['amount'] = 0-$array['net_total'];
                        unset($pay['user_profile_id']);
                        unset($pay['user_profile_type']);
                        unset($pay['tx_type']);
                        unset($pay['send_time']);

                        $wallet_id = $this->createWallet($pay);
                        $this->updateOne("transactions", "tx_type_id", $wallet_id, $post, "ref");
                        
                        $return['code'] = 1;
                        $return['message'] = "Withdrawal queued for Processing";
                    } else {
                        $return['code'] = 0;
                        $return['message'] = "Error processing withdrawal";
                    }
                } else {
                    $return['code'] = 0;
                    $return['message'] = "Insufficient wallet Balance";
                }
            }
            return $return;
        }

        private function reviewBank($array) {
            global $country;
            global $bank_account;
            $regionData = $country->getLoc($array['region'], "ref"); ?>            
            <h2>My Wallet (<?php echo $regionData['currency']; ?>)</h2>
            <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Confirm Transaction</h5>
            </div>
            <div id="card-body">
            <form method="post" action="">
            <table class="table table-hover">
                <tbody>
                    <tr>
                        <th scope="row" width=25%>Amount</th>
                        <td><strong><?php echo $regionData['currency_symbol']." ".number_format($array['amount'], 2); ?></strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Transaction Type</th>
                        <td><strong><?php echo $this->clearText($array['tx_dir']); ?></strong></td>
                    </tr>
                    <?php if ($array['tx_dir'] == "CR") { ?>
                    <tr>
                        <th scope="row">Source</th>
                        <td><strong><?php echo "Credit Card **** **** **** ". payment_card::getSingle( $array['card'], "pan"); ?></strong></td>
                    </tr>
                    <?php } else if ($array['tx_dir'] == "DR") { ?>
                    <tr>
                        <th scope="row">Destination</th>
                        <?php if ($array['type'] == "CC") { ?>
                            <td><strong><?php echo "Credit Card **** **** **** ". payment_card::getSingle( $array['card'], "pan"); ?></strong></td>
                        <?php } else if ($array['type'] == "BA") { ?>
                            <td><strong><?php echo "Account Number ".$bank_account->getSingle( $array['card'], "transit_number")." - ".$bank_account->getSingle( $array['card'], "account_number"); ?></strong></td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <input type="hidden" name="net_total" value="<?php echo $array['amount']; ?>" />
            <input type="hidden" name="tx_dir" value="<?php echo $array['tx_dir']; ?>" />
            <input type="hidden" name="card" value="<?php echo $array['card']; ?>" />
            <input type="hidden" name="type" value="<?php echo $array['type']; ?>" />

            <input type="hidden" name="user_id" value="<?php echo $array['ref']; ?>">
            <input type="hidden" name="region" value="<?php echo $array['region']; ?>">
            <button type="submit" name="postWallet" class="btn btn-primary">Confirm and Pay</button>
            </form>
            </div>
            <?php
        }

        private function clearText($text) {
            if ($text == "CR") {
                return "Deposit to Wallet (".$text.")";
            } else if ($text == "DR") {
                return "Withdraw from Wallet (".$text.")";
            } else {
                return $text;
            }
        }
        
        public function listAllWallet($ref, $region) {
            global $options;
            global $country;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            $data = $this->listAllWalletData($ref, $region, $start, $limit);        
            
            $list = $data['list'];
            $listCount = $data['listCount']; ?>
            <br>
            <h2>All Wallet Transactions</h2>
            <table class="table">
            <thead>
                <tr>
                <th scope="col">#</th>
                <th scope="col">Tx ID</th>
                <th scope="col">Amount</th>
                <th scope="col">Description</th>
                <th scope="col">Status</th>
                <th scope="col">Posted</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < count($list); $i++) {   
                    $regionData = $country->getLoc($list[$i], "ref");
                    if ($list[$i]['tx_dir'] == "CR") {
                        $style = "client";
                    } else if ($list[$i]['tx_dir'] == "DR") {
                        $style = "vendor";
                    }
                    if ($list[$i]['status'] == 1) {
                        if ($list[$i]['tx_dir'] == "CR") {
                            $status = "Available";
                        } else if ($list[$i]['tx_dir'] == "DR") {
                            $status = "Completed";
                        }
                    } else if ($list[$i]['status'] == 0) {
                        $status = "Pending";
                    } else if ($list[$i]['status'] == 2) {
                        $status = "Processing";
                    } ?>
                <tr>
                <th scope="row"><?php echo $start+$i+1; ?></th>
                <td><a href="<?php echo URL."transaction.view?ref=".$list[$i]['tx_id']; ?>"><?php echo $this->txid( $list[$i]['tx_id'] ); ?></a></td>
                <td class="<?php echo $style; ?>"><?php echo $regionData['currency_symbol']." ".number_format(abs($list[$i]['amount']), 2); ?></td>
                <td><?php echo $list[$i]['tx_desc']; ?></td>
                <td><?php echo $status; ?></td>
                <td><?php echo $list[$i]['create_time']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
            </table>
            <?php $this->pagination($page, $listCount);
        }

        private function arrayJson($cc, $ba) {
            $a = array();
            $b = array();
            $result = array();
            for ($i = 0; $i < count($cc); $i++) {
                $a[$i]['id'] = $cc[$i]['ref'];
                $a[$i]['name'] = "**** **** **** ".$cc[$i]['pan'];
                $a[$i]['default'] = $cc[$i]['is_default'];
            }

            for ($i = 0; $i < count($ba); $i++) {
                $b[$i]['id'] = $ba[$i]['ref'];
                $b[$i]['name'] = $ba[$i]['transit_number']." - ".$ba[$i]['account_number'];
                $b[$i]['default'] = $ba[$i]['is_default'];
            }

            $result['cards'] = $a;
            $result['account'] = $b;

            return json_encode($result);
        }

        private function bank($ref, $locale, $redirect) {
            global $country;
            global $bank_account;
            $regionData = $country->getLoc($locale);
            if (isset($_REQUEST['bal'])) {
                $amt = $_REQUEST['bal'];
            } else {
                $amt = "10.00";
            }
            
            $list = $this->getSortedList($ref, "user_id", "status", "ACTIVE");
            $list2 = $bank_account->getSortedList($ref, "user_id", "status", "ACTIVE", "region", $regionData['code']);
            $json_data = $this->arrayJson($list, $list2);?>
            <h2>My Wallet (<?php echo $regionData['currency']; ?>)</h2>
            <?php $this->bankHeader($ref, $regionData); ?>
            <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Manage Wallet</h5>
            </div>
            <div id="card-body">
            <form method="post" action="<?php echo $redirect; ?>">
                <?php if (count($list) > 0) {?>
                    <div class="form-group">
                    <label for="tx_dir">Transaction Type</label>
                    <select class="form-control" id="tx_dir" name="tx_dir" required>
                        <option value="CR">Deposit</option>
                        <option value="DR">withdrawal</option>
                    </select>
                    <small id="tx_dir_help" class="form-text text-muted">Please indicate if you are making a withdrawal or a deposit.</small>
                    </div>
                    <div class="form-group">
                    <label for="card" id="label">Source</label>
                        <select name="card" id="card" class="form-control input-lg" required>
                            <?php for ($i = 0; $i < count($list); $i++) { ?>
                                <option data-type="CC" value="<?php echo $list[$i]['ref']; ?>"<?php if ($list[$i]['is_default'] == 1) {?> selected<?php } ?>>**** **** **** <?php echo $list[$i]['pan']; if ($list[$i]['is_default'] == 1) { echo " [Default]"; } ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount" placeholder="Enter Wallet amount" required value="<?php echo $amt; ?>" min="<?php echo $amt; ?>" max="" />
                        <small id="amount_help" class="form-text text-muted">This will be the amount you are depositing to your wallet.</small>
                    </div>
                    <input type="hidden" name="ref" value="<?php echo $ref; ?>">
                    <input type="hidden" name="type" id="type" value="CC">
                    <input type="hidden" name="region" value="<?php echo $regionData['ref']; ?>">
                    <button type="submit" name="post" class="btn btn-primary">Review Payment</button>
                <?php } else { ?>
                    <i class='fas fa-exclamation-circle'></i>  You must add a payment card first. <a href="<?php echo URL."paymentCards/create"; ?>">Click here</a> to add one now
                <?php } ?>
            </form>
            </div>
            <script language="javascript">
                var data = JSON.parse('<?php echo $json_data; ?>');
                var amt = '<?php echo $amt; ?>';
            </script>
            <script type="text/javascript" src="<?php echo URL; ?>js/wallet.js"></script>
        <?php }

        function createNew() {
                $tag = "Create New Payment Card";
                $tag2 = "Create Payment Card";
            ?>
<main class="col-12" role="main">
    <form method="post" action="" enctype="multipart/form-data" autocomplete="off">
    <h2><?php echo $tag; ?></h2>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="cc_last_name">Last Name</label>
            <input type="text" class="form-control" name="cc_last_name" id="cc_last_name" placeholder="Last Name" required>
        </div>
        <div class="form-group col-md-6">
            <label for="cc_first_name">First Name</label>
            <input type="text" class="form-control" name="cc_first_name" id="cc_first_name" placeholder="First Name" required>
        </div>
    </div>
    <div class="form-group">
        <label for="tag">Card Number</label>
        <div class="form-row">
            <div class="col-md-11">
                <input type="text" name="cardno" id="cardno" class="form-control" placeholder="XXXX XXXX XXXX XXXX" onKeyUp="displayCardType(this.value)">
            </div>
            <div class="col-md-1">
                <span id="cardLogo"></span>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="tag">Expiry Month</label>
            <input type="number" class="form-control" maxlength="2" size="2" name="mm" id="mm" placeholder="MM" pattern="[0-9.]+" onKeyUp="monthCheck()" max="12">
        </div>
        <div class="form-group col-md-6">
            <label for="tag">Expiry Year</label>
            <input type="number" class="form-control" maxlength="2" size="2" name="yy" id="yy" placeholder="YY" pattern="[0-9.]+" onKeyUp="yearCheck()" max="99">
        </div>
    </div>
    <div class="form-group">
        <label for="tag">CVV</label>
        <input type="text" class="form-control" name="cvv" id="cvv" placeholder="CVV">
    </div>
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['users']['ref']; ?>">
    <button type="submit" name="getPayment" class="btn btn-primary"><?php echo $tag2; ?></button>
    </form>
</main>
<script type="text/javascript" src="<?php echo URL; ?>js/creditCard.js"></script>
        <?php }

        public function listAll($redirect) {
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            
            $data = $this->listAllUserData($_SESSION['users']['ref'], $start, $limit);
            $list = $data['list'];
            $listCount = $data['listCount'];

            ?>
            <h2>List All Payment Cards</h2>
            <small>You must have atleast one card active and can not remove a default card. To remove a default card, you must activate another card as default</small>
<form method="post" action="" enctype="multipart/form-data">
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
      <td><?php echo "**** **** **** ".$list[$i]['pan']; ?></td>
      <td><?php echo $list[$i]['expiry_month']."/".$list[$i]['expiry_year']; ?></td>
      <td><?php echo $list[$i]['status']; ?></td>
      <td><?php echo $list[$i]['create_time']; ?></td>
      <td><?php echo $list[$i]['modify_time']; ?></td>
      <td><a href="<?php echo URL.$redirect."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this card. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a><?php if ((count($list) > 1) && ($list[$i]['is_default'] != 1)) { ?> | <a href="<?php echo URL.$redirect."?delete=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this card. are you sure you want to continue ?')">Delete</a><?php } ?></td>
    </tr>
      <?php } ?>
  </tbody>
</table>
<?php $this->pagination($page, $listCount);
if (count($list) > 1) { ?>
<button type="submit" name="set_is_default" class="btn btn-primary">Set Default Card</button>
<?php } ?>
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