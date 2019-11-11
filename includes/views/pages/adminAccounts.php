<?php
    class adminAccounts extends bank_account {
        public function pageContent($redirect, $view=false, $ref=false) {
            if ($view == "oneView") {
              $this->view($ref);
            } else {
                $this->listAll($redirect);
            }
        }

        private function listAll($redirect) {
            global $options;
            global $banks;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            global $users;
            $list = $this->getList($start, $limit);
            $listCount = $this->getList(false, false, "last_name", "ASC", "count"); ?>
            <h2>Account Numbers</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">User</th>
                        <th scope="col">Name on Account</th>
                        <th scope="col">Account Details</th>
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
                            <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['user_id']; ?>"><?php echo $users->listOnValue( $list[$i]['user_id'], "last_name")." ".$users->listOnValue( $list[$i]['user_id'], "other_names"); ?></a></td>
                            <td><?php echo $list[$i]['last_name']." ".$list[$i]['first_name']; ?></td>
                            <td><a href="<?php echo URL."admin/transactions.account?ref=".$list[$i]['ref']; ?>"><?php echo trim( $banks->getSingle( $list[$i]['financial_institution'] ))." *****".substr($list[$i]['account_number'], -4); ?></a></td>
                            <td><?php echo $list[$i]['status']; ?></td>
                            <td><?php echo $list[$i]['create_time']; ?></td>
                            <td><?php echo $list[$i]['modify_time']; ?></td>
                            <td><a href="<?php echo URL.$redirect."?delete=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this card. are you sure you want to continue ?')">Delete</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
          <?php $this->pagination($page, $listCount);
        }

        private function view($ref) {
            global $transactions;
            global $users;
            global $country;
            global $options;
            global $banks;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            $data = $this->listOne($ref);
            $list = $transactions->getSortedListTrans($ref, "account", false, false, false, false, "ref", "DESC", "AND", $start, $limit);
            $listCount = $transactions->getSortedListTrans($ref, "account", false, false, false, false, "ref", "DESC", "AND", false, false, "count"); ?>
            <a href="javascript:history.go(-1);">Back</a>
            <h2>Account Details</h2>
            <table class="table table-striped">
                <tr>
                    <th width="25%">Account Name</th>
                    <td><?php echo $data['last_name']." ".$data['first_name']; ?></td>
                </tr>
                <?php if ($data['region'] == "US") { ?>
                    <tr>
                        <th width="25%">Account Code</th>
                        <td><?php echo $data['account_code']; ?></td>
                    </tr>
                <?php } else if ($data['region'] == "CA") { ?>
                    <tr>
                        <th width="25%">Financial Institution</th>
                        <td><?php echo $banks->getSingle( $data['financial_institution'] ); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <th width="25%">Transit Code</th>
                    <td><?php echo $data['transit_number']; ?></td>
                </tr>
                <tr>
                    <th width="25%">Account Number</th>
                    <td><?php echo $data['account_number']; ?></td>
                </tr>
                <tr>
                    <th width="25%">Status</th>
                    <td><?php echo $data['status']; ?></td>
                </tr>
                <tr>
                    <th width="25%">Created</th>
                    <td><?php echo $data['create_time']; ?></td>
                </tr>
                <tr>
                    <th width="25%">Last Modified</th>
                    <td><?php echo $data['modify_time']; ?></td>
                </tr>
            </table>
            <h2>Transactions on Account</h2>
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
        
            <?php $this->pagination($page, $listCount);
        }

        public function navigationBar() { ?>
            <p><i class="fa fa-caret-right mr-3"></i><a href="<?php echo URL; ?>"><b>Home</b></a></p>
       <?php }
    }
?>
