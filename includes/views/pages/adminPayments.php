<?php
    class adminPayments extends payments {
        function pageContent() {
            $this->listAll();
        }

        function listAll() {
            global $bank_account;
            global $transactions;
            global $country;
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
            $listCount = $this->getList(false, false, "ref", "DESC", "count") ?>
            <h2>Pending Payments</h2>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">User</th>
      <th scope="col">Transaction Type</th>
      <th scope="col">Channel</th>
      <th scope="col">Amount</th>
      <th scope="col">Processing Time</th>
      <th scope="col">Created</th>
      <th scope="col">Last Modified</th>
    </tr>
  </thead>
  <tbody>
      <?php for ($i = 0; $i < count($list); $i++) {
          
        if ($list[$i]['user_profile_type'] == "BA") {
            $channel = '<a href="'. URL.'admin/transactions.account?ref='.$list[$i]['user_profile_id'].'">'.$bank_account->getSingle($list[$i]['user_profile_id'], "transit_number")." - ".$bank_account->getSingle($list[$i]['user_profile_id'], "account_number")."</a>";
        } else if ($list[$i]['user_profile_type'] == "CC") {
            $channel = '<a href="'. URL.'admin/transactions.card?ref='.$list[$i]['user_profile_id'].'">**** **** **** '.$transactions->getSingle($list[$i]['user_profile_id'], "pan")."</a>";
        }
        if ($list[$i]['tx_type'] == "E") {
            $tx_type = "EFT (Canada)";
        } else if ($list[$i]['tx_type'] == "A") {
            $tx_type = "EFT (US)";
        } else if ($list[$i]['tx_type'] == "C") {
            $tx_type = "Credit Card";
        }
         ?>
    <tr>
      <th scope="row"><?php echo $start+$i+1; ?></th>
      <td><a href="<?php echo URL."admin/users.view?ref=".$list[$i]['user_id']; ?>"><?php echo $users->listOnValue( $list[$i]['user_id'], "last_name")." ".$users->listOnValue( $list[$i]['user_id'], "other_names"); ?></a></td>
      <td><?php echo $tx_type; ?></td>
      <td><?php echo $channel; ?></td>
      <td><?php echo $country->getSingle( $list[$i]['region'], "currency_symbol" )." ".number_format( $list[$i]['amount'], 2 ); ?></td>
      <td><?php echo $this->get_time_stamp( $list[$i]['send_time'] ); ?></td>
      <td><?php echo $list[$i]['create_time']; ?></td>
      <td><?php echo $list[$i]['modify_time']; ?></td>
    </tr>
      <?php } ?>
  </tbody>
</table><?php $this->pagination($page, $listCount);
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