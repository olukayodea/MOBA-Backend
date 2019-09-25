<?php
    class adminCards extends payment_card {
      public function pageContent($redirect, $view=false, $ref=false) {
          if ($view == "oneView") {
            $this->view($ref);
          } else {
              $this->listAll($redirect);
          }
      }

    private function listAll($redirect) {
      global $users;
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
      <h2>Payment Cards</h2>
      <table class="table">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">User</th>
            <th scope="col">Card Number</th>
            <th scope="col">Expiry Date</th>
            <th scope="col">Card Name</th>
            <th scope="col">Default</th>
            <th scope="col">Status</th>
            <th scope="col">Created</th>
            <th scope="col">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < count($list); $i++) { ?>
          <tr>
            <th scope="row"><?php echo $start+$i+1; ?></th>
            <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['user_id']; ?>"><?php echo $users->listOnValue( $list[$i]['user_id'], "last_name")." ".$users->listOnValue( $list[$i]['user_id'], "other_names"); ?></a></td>
            <td><a href="<?php echo URL."admin/transactions.card?ref=".$list[$i]['ref']; ?>"><?php echo "**** **** **** ".$list[$i]['pan']; ?></a></td>
            <td><?php echo $list[$i]['expiry_month']."/".$list[$i]['expiry_year']; ?></td>
            <td><?php echo $list[$i]['card_name']; ?></td>
            <td><?php echo $list[$i]['is_default']; ?></td>
            <td><?php echo $list[$i]['status']; ?></td>
            <td><?php echo $list[$i]['create_time']; ?></td>
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

      if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
      } else {
        $page = 0;
      }
      
      $limit = $options->get("result_per_page");
      $start = $page*$limit;
      $data = $this->listOne($ref);
      $list = $transactions->getSortedListTrans($ref, "card", false, false, false, false, "ref", "DESC", "AND", $start, $limit);
      $listCount = $transactions->getSortedListTrans($ref, "card", false, false, false, false, "ref", "DESC", "AND", false, false, "count"); ?>
      
      <a href="javascript:history.go(-1);">Back</a>
      <h2>Card Details</h2>
      <table class="table">
        <tr>
          <th width="25%">Card Name</th>
          <td><?php echo $data['card_name']; ?></td>
        </tr>
        <tr>
          <th width="25%">Card Number</th>
          <td><?php echo "**** **** **** ".$data['pan']; ?></td>
        </tr>
        <tr>
          <th width="25%">Expiry Date</th>
          <td><?php echo $data['expiry_month']."/".$data['expiry_year']; ?></td>
        </tr>
        <tr>
          <th width="25%">Status</th>
          <td><?php echo $data['status']; ?></td>
        </tr>
        <tr>
          <th width="25%">Gateway Token</th>
          <td><?php echo $data['gateway_token']; ?></td>
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
      <h2>Transactions on Card</h2>
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
      <?php $this->pagination($page, $listCount);
    }
  }
?>
