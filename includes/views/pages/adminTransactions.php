<?php
    class adminTransactions extends transactions {
        public function pageContent($redirect, $view=false, $ref=false) {
            if ($view == "oneView") {
                $this->details($ref, $redirect);
            } else if ($redirect == "transaction") {
                $this->listUser($view, $ref);
            } else {
                $this->listAdmin($view, $ref);
            }
        }

        public function navigationBar($redirect, $view, $sort, $type=false) {
            global $country;
            $list = $country->getList();
             ?>
            <p><i class="fa fa-caret-right mr-3"></i><a href="<?php echo URL.$redirect."?view=all&sort=".$sort; ?>"
            ><b>List All Transactions</b></p>
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."?view=success&sort=".$sort; ?>"><b>List All Successful Transactions</b></a></p>	
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."?view=failed&sort=".$sort; ?>"><b>List all Failed Transactions</b></a></p>		
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."?view=draft&sort=".$sort; ?>"><b>List All New Transactions</b></a></p>
            <?php if ($type != "user") { ?>
            <?php for ($i = 0; $i < count($list); $i++) { ?>
              <div class="moba-line my-2"></div>
              <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."?view=".$view."&sort=".$list[$i]['code']; ?>"><b><?php echo $list[$i]['name']; ?></b></a></p><?php } ?>
            <?php }
        }

        private function listAdmin($view, $sort) {
            global $users;
            global $country;
            global $options;
            $regionData = $country->getLoc($sort);

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;

             if ($view == "failed") {
              $list = $this->getSortedListTrans("1", "status", "region", $regionData['ref'], false, false,"modify_time", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedListTrans("1", "status", "region", $regionData['ref'], false, false,"modify_time", "DESC", "AND", false, false, "count");
                $tag = "All Failed Transactions in ".$regionData['name'];
            } else if ($view == "success") {
              $list = $this->getSortedListTrans("2", "status", "region", $regionData['ref'], false, false,"modify_time", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedListTrans("2", "status", "region", $regionData['ref'], false, false,"modify_time", "DESC", "AND", false, false, "count");
                $tag = "All Successful Transactions in ".$regionData['name'];
            } else if ($view == "draft") {
              $list = $this->getSortedListTrans("0", "status", "region", $regionData['ref'], false, false,"ref", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedListTrans("0", "status", "region", $regionData['ref'], false, false,"ref", "DESC", "AND", false, false, "count");
                $tag = "All New Transactions in ".$regionData['name'];
            } else {
              $list = $this->getSortedListTrans($regionData['ref'], "region", false, false, false, false, "ref", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedListTrans($regionData['ref'], "region", false, false, false, false, "ref", "DESC", "AND", false, false, "count");
                $tag = "All Transactions in ".$regionData['name'];
            } ?>
            <h2><?php echo $tag; ?></h2>
          <table class="table table-striped">
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
                <td><a href="<?php echo URL."admin/transactions.view?ref=".$list[$i]['ref']; ?>"><?php echo $this->txid( $list[$i]['ref'] ); ?></a></td>
                <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['user_id']; ?>"><?php echo $users->listOnValue( $list[$i]['user_id'], "last_name")." ".$users->listOnValue( $list[$i]['user_id'], "other_names"); ?></a></td>
                <td><?php echo $this->url( $list[$i]['tx_type'], $list[$i]['tx_type_id'] ); ?></td>
                <td><?php echo $regionData['currency_symbol']." ". $list[$i]['net_total']." (".$list[$i]['tx_dir'].")"; ?></td>
                <td><?php echo $regionData['currency_symbol']." ".$list[$i]['tax_total']." (".$list[$i]['tx_dir'].")"; ?></td>
                <td><?php echo $regionData['currency_symbol']." ".$list[$i]['gross_total']." (".$list[$i]['tx_dir'].")"; ?></td>
                <td><?php echo $list[$i]['gateway_status']; ?></td>
                <td><?php echo $list[$i]['create_time']; ?></td>
                <td><?php echo $list[$i]['modify_time']; ?></td>
              </tr>
                <?php } ?>
            </tbody>
          </table>
        <?php $this->pagination($page, $listCount);
        }

        private function listUser($view, $sort) {
            global $country;
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;

             if ($view == "failed") {
              $list = $this->getSortedListTrans("Failed", "status", "user_id", $sort, false, false,"modify_time", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedListTrans("Failed", "status", "user_id", $sort, false, false,"modify_time", "DESC", "AND", false, false, "count");
              $tag = "All Failed Transactions";
            } else if ($view == "success") {
              $list = $this->getSortedListTrans("Approved", "status", "user_id", $sort, false, false,"modify_time", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedListTrans("Approved", "status", "user_id", $sort, false, false,"modify_time", "DESC", "AND", false, false, "count");
              $tag = "All Successful Transactions";
            } else if ($view == "draft") {
              $list = $this->getSortedListTrans("NEW", "status", "user_id", $sort, false, false,"ref", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedListTrans("NEW", "status", "user_id", $sort, false, false,"ref", "DESC", "AND", false, false, "count");
              $tag = "All New Transactions";
            } else {
              $return = $this->listAllTransData($sort, $start, $limit);
              $list = $return['list'];
              $listCount = $return['listCount'];
              $tag = "All Transactions";
            } ?>
            <h2><?php echo $tag; ?></h2>
          <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Ref</th>
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
                <td><a href="<?php echo URL."transaction.view?users&ref=".$list[$i]['ref']; ?>"><?php echo $this->txid( $list[$i]['ref'] ); ?></a></td>
                <td><?php echo $this->url( $list[$i]['tx_type'], $list[$i]['tx_type_id'] ); ?></td>
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
        
          <?php $this->pagination($page, $listCount);
        }

        private function details($ref, $redirect) {
            global $users;
            global $country;
            global $banks;
            global $bank_account;
            $data = $this->listOneTrans($ref);
            $gateway_data = unserialize( $data['gateway_data'] );

            ?>
            <a href="javascript:history.go(-1);">Back</a>
            <h2><?php echo "Transaction: ".$this->txid($data['ref']); ?></h2>
          <table class="table table-striped">
            <tbody>
              <tr>
                <th scope="row">Transaction Ref</th>
                <td><?php echo $this->txid($data['ref']); ?></td>
              </tr>
              <tr>
                <th scope="row">User</th>
                <td><a href="<?php echo URL."admin/users.view?ref=".$data['user_id']; ?>"><?php echo $users->listOnValue( $data['user_id'], "last_name")." ".$users->listOnValue( $data['user_id'], "other_names"); ?></a></td>
              </tr>
              <tr>
                <th scope="row">Transaction Type</th>
                <td><?php echo $this->url( $data['tx_type'], $data['tx_type_id'] ); ?></td>
              </tr>
              <?php if ($data['card'] > 0) { ?>
              <tr>
                <th scope="row">Payment Card</th>
                <td><a href="<?php echo URL."admin/transactions.card?ref=".$data['card']; ?>"><?php echo "**** **** **** ".$this->getSingle( $data['card'], "pan" ); ?></a>
                  <?php echo $data['x']; ?></td>
              </tr>
              <?php } ?>
              <?php if ($data['account'] > 0) { ?>
              <tr>
                <th scope="row">Account Number</th>
                <td><a href="<?php echo URL."admin/transactions.account?ref=".$data['account']; ?>"><?php echo trim( $banks->getSingle( $data['account'] ))." *****".substr(trim( $bank_account->getSingle( $data['account'], "account_number" )), -4); ?></a>
              </td>
              <?php } ?>
              </tr>
              <?php if (!isset($_REQUEST['users'])) { ?>
              <tr>
                <th scope="row">Region</th>
                <td><?php echo $country->getSingle($data['region']); ?></td>
              </tr>
              <?php } ?>
              <tr>
                <th scope="row">Net Amount</th>
                <td><?php echo $country->getSingle($data['region'], "currency_symbol", "ref")." ". $data['net_total']." (".$data['tx_dir'].")"; ?></td>
              </tr>
              <tr>
                <th scope="row">Tax</th>
                <td><?php echo $country->getSingle($data['region'], "currency_symbol", "ref")." ". $data['tax_total']." (".$data['tx_dir'].")"; ?></td>
              </tr>
              <tr>
                <th scope="row">Gross Amount</th>
                <td><?php echo $country->getSingle($data['region'], "currency_symbol", "ref")." ". $data['gross_total']." (".$data['tx_dir'].")"; ?></td>
              </tr>
              <tr>
                <th scope="row">Status</th>
                <td><?php echo $data['gateway_status']; ?></td>
              </tr>
              <tr>
                <th scope="row">Created</th>
                <td><?php echo $data['create_time']; ?></td>
              </tr>
              <tr>
                <th scope="row">Modified</th>
                <td><?php echo $data['modify_time']; ?></td>
              </tr>
              <?php if (!isset($_REQUEST['users'])) { ?>
              <tr>
                <th scope="row" colspan="2">Data from Gateway</th>
              </tr>
              <?php if ($gateway_data != "") {foreach ($gateway_data as $key => $value) { ?>
              <tr>
                <th scope="row"><?php echo $key; ?></th>
                <td><?php if ($key == "card") {
                    foreach ($value as $v => $k) {
                        echo "<strong>".$v."</strong>:<br>".$k."<br>";
                    }
                } else if (($key != "custom") && ($key != "links")) {
                    echo $value;
                } ?></td>
              </tr>
              <?php } ?>
              <?php } else { ?>
              <tr>
                <td colspan="2">N/A</td>
              </tr>
              <?php }
              } ?>
            </tbody>
          </table>
        <?php 
        }
    }
?>