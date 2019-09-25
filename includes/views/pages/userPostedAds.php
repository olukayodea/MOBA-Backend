<?php
  class userPostedAds extends projects {
    public function navigationBar($redirect) { ?>
      <a href="<?php echo URL.$redirect; ?>/all">List All</a> | <a href="<?php echo URL.$redirect."/active"; ?>">Posted Ads</a> | <a href="<?php echo URL.$redirect."/conversation"; ?>">Current Interest</a> | <a href="<?php echo URL.$redirect."/on-going"; ?>">On-Going Ads</a> | <a href="<?php echo URL.$redirect."/running"; ?>">Active Task</a> | <a href="<?php echo URL.$redirect."/past"; ?>">Past Ads</a> | <a href="<?php echo URL.$redirect."/archive"; ?>">Archived Ads</a> | <a href="<?php echo URL.$redirect."/draft"; ?>">Drafts</a> | <a href="<?php echo URL.$redirect."/saved"; ?>">Saved Ads</a>
    <?php }

    public function pageContent($ref, $view, $redirect, $data=false) {
      if ($view == "view_page") {
        $this->viewPage($ref);
      } else if ($view == "view_current") {
        $this->viewProfile($ref);
      } else if ($view == "review_milstone") {
        $this->reviewMilestone($data);
      } else if ($view == "review_hours") {
        $this->reviewHour($data);
      } else if ($view == "milestone") {
        $this->confirmMilestone($data);
      } else if ($view == "featured_ad") {
        $this->confirmationFeatured($data);
      } else {
        $this->listAll($ref, $view, $redirect);
      }
    }

    public function postTransaction($array) {
      global $transactions;
      $post = $transactions->createTx($array);
      $makePayment = $transactions->bambora_pay($array);
      if (($makePayment['approved'] == 1) && ($makePayment['message'] == "Approved")) {
        $return['code'] = 1;
        $return['message'] = $makePayment['message'];
        $this->updateOne("transactions", "status", 2, $post, "ref");
      } else if ($makePayment['code'] != 1) {
        $return['code'] = $makePayment['code'];
        $return['message'] = $makePayment['message'];
        $this->updateOne("transactions", "status", 1, $post, "ref");
      }
      $this->updateOne("transactions", "gateway_data", serialize($makePayment), $post, "ref");
      $this->updateOne("transactions", "gateway_status", $makePayment['message'], $post, "ref");
      
      return $return;
    }

    public function checkNegotiate($array) {
      global $projects_negotiate;
      return $projects_negotiate->checkCurrent($array);
    }

    public function checkMilestone($array, $view="count", $status=0) {
      global $projects_data;
      return $projects_data->checkCurrent($array, $view, $status);
    }

    public function negotiatePrize($array) {
      global $projects_negotiate;
      global $messages;
      $data['project_id'] = $array['project_id'];
      $data['user_id'] = $array['user_id'];
      $data['user_r_id'] = $array['user_r_id'];
      $data['amount'] = $array['negotiated_fee'];
      unset($array['negotiated_fee']);
      unset($array['negotiate']);
      $create = $projects_negotiate->create($data);

      if ($create) {
        $array['message']  = "[none]";
        $array['user_r_id']  = $array['user_r_id'];
        $array['user_id']  = $array['user_id'];
        $array['project_id']  = $array['project_id'];
        $array['m_type']  = $array['m_type'];
        $array['m_type_data']  = $data['amount']."_".$create;
        $messages->add($array);

        return true;
      } else {
        return false;
      }
    }

    public function negotiateResponse($array) {
      global $projects_negotiate;
      global $messages;
      $projects_negotiate->updateOneRow("status", $array['status'], $array['ref']);
      $messages->updateOneRow("message", "You have a new fee negotiation request.", $array['message']);
      $messages->updateOneRow("m_type", "text", $array['message']);
      $messages->updateOneRow("m_type_data", "", $array['message']);
      
      $messageData = $messages->listOne($array['message']);
      if ($array['status'] == 2) {
        $msg = "Your fee negotiation request was approved";
      } else {
        $msg = "Your fee negotiation request was declined";
      }

      $msgArray['message']  = $msg;
      $msgArray['user_r_id']  = $messageData['user_id'];
      $msgArray['user_id']  = $messageData['user_r_id'];
      $msgArray['project_id']  = $messageData['project_id'];
      $msgArray['m_type']  = "system";
      $messages->add($msgArray);

      //send email after

      $msg .= ". <a href='".$this->seo($messageData['project_id'], "view")."'>Click here</a> to continue review this request";

      $data = $this->listOne($messageData['user_id'], "ref");
      
      $client = $data['last_name']." ".$data['other_names'];
      $subjectToClient = "Negotiation Request Update";
      $contact = "MOBA <".replyMail.">";
      
      $fields = 'subject='.urlencode($subjectToClient).
          '&last_name='.urlencode($data['last_name']).
          '&other_names='.urlencode($data['other_names']).
          '&email='.urlencode($data['email']).
          '&tag='.urlencode($msg);
      $mailUrl = URL."includes/views/emails/notification.php?".$fields;
      $messageToClient = $this->curl_file_get_contents($mailUrl);
      
      $mail['from'] = $contact;
      $mail['to'] = $client." <".$data['email'].">";
      $mail['subject'] = $subjectToClient;
      $mail['body'] = $messageToClient;
      
      global $alerts;
      $alerts->sendEmail($mail);

      return true;
    }

    private function confirmationFeatured($array) {
      global $transactions;
      global $country;
      global $users;
      global $rating;

      $ref = $array['project'];
      $data = $this->listOne($ref, "ref");
      $locale = $_SESSION['location']['code'];
      $regionData = $country->getLoc($locale);
      $featured = $array['featured'];
      $featured_ad = $regionData['featured_ad'];
      $net_total = $featured*$featured_ad;
      $tax = $regionData['tax'];
      $tax_total = ($tax/100)*$net_total;
      $gross_total = $net_total+$tax_total;
      $paymentData = $transactions->listOne($array['card']); ?>

      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h2><?php echo $data['project_name']; ?></h2>
          <p><?php echo $data['project_dec']; ?></p>
          <p><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['address']; ?></p>
          <p><i class="fa fa-eye" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->visitors($ref); ?></p>
          <p><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($data['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($data['user_id'], "screen_name"); ?></a>&nbsp;<?php echo $rating->drawRate($rating->getRate($data['user_id'])); ?></p>
        </div>
      </div>
      <div class="row">
        <h3>Purchase Confirmation</h3>
        <table class="table table-striped" width="100%" border="0">
          <tr>
            <th width="25%" scope="row">Purchase Type</th>
            <td>Feautured Ad. Promotion</td>
          </tr>
          <tr>
            <th scope="row">Number of Days</th>
            <td><?php echo number_format($featured); ?></td>
          </tr>
          <tr>
            <th scope="row">Amount Per Day</th>
            <td><?php echo $regionData['currency_symbol']." ".number_format($featured_ad, 2); ?></td>
          </tr>
          <tr>
            <th scope="row">Net Total <sup>*</sup></th>
            <td><?php echo $regionData['currency_symbol']." ".number_format($net_total, 2); ?></td>
          </tr>
          <tr>
            <th scope="row">Tax (<?php echo number_format($tax)."%"; ?>) <sup>*</sup></th>
            <td><?php echo $regionData['currency_symbol']." ".number_format($tax_total, 2); ?></td>
          </tr>
          <tr>
            <th scope="row">Total <sup>*</sup></th>
            <td><?php echo $regionData['currency_symbol']." ".number_format($gross_total, 2); ?></td>
          </tr>
          <tr>
            <th scope="row">Bill To <sup>*</sup></th>
            <td><?php echo "**** **** ****  ".$paymentData['pan']; ?></td>
          </tr>
          <tr>
            <th scope="row" colspan="2">
              <div class="row with-margin">
                  <div class="col-lg-12">
                    <form  method="post" name="form" action="">
                        <div class="input-group input-group-lg">
                  <input type='hidden' name="num_days" id="num_days" value="<?php echo $featured; ?>" />
                  <input type='hidden' name="net_total" id="net_total" value="<?php echo $net_total; ?>" />
                  <input type='hidden' name="tax_total" id="tax_total" value="<?php echo $tax_total; ?>" />
                  <input type='hidden' name="gross_total" id="gross_total" value="<?php echo $gross_total; ?>" />
                  <input type='hidden' name="project" id="project" value="<?php echo $ref; ?>" />
                  <input type='hidden' name="tx_type_id" id="tx_type_id" value="<?php echo $ref; ?>" />
                  <?php if (isset($_REQUEST['viewResponse'])) { ?>
                  <input type='hidden' name="viewResponse" id="viewResponse" value="<?php echo $_REQUEST['viewResponse']; ?>" />
                  <?php } ?>
                  <input type='hidden' name="user_id" id="user_id" value="<?php echo $_SESSION['users']['ref'] ; ?>" />
                  <input type='hidden' name="type" id="type" value="<?php echo $array['type']; ?>" />
                  <input type='hidden' name="tx_type" id="tx_type" value="<?php echo $array['type']; ?>" />
                  <input type='hidden' name="region" id="region" value="<?php echo $regionData['ref']; ?>" />
                  <input type='hidden' name="card" id="card" value="<?php echo $array['card']; ?>" />
                  <input type="submit"  value="Approve Purchase"  id="approve_tx" class="btn btn-primary" name="approve_tx"/>
                  <input type="button"  value="Cancel"  id="cancel_tx" class="btn btn-secondary" onclick="location='<?php echo $this->seo($ref, 'view'); ?>';" name="cancel_tx"/>
                        </div>
                    </form>
                  </div>
              </div>
            </th>
          </tr>
        </table>
        <small><i>*</i>&nbsp;All charges are in <?php echo $regionData['currency']; ?></small>
      </div>
    <?php }

    public function processHours($array) {
      global $projects_data;
      global $messages;
      global $users;
      $array['project_id'] = $array['project'];
      $array['data']['data_type'] = "hours";
      $array['data']['content'] = $array['hour_count'];
      $array['data']['status'] = "Pending";
      unset($array['setup_hours']);
      unset($array['project']);
      unset($array['type']);
      unset($array['viewResponse']);
      unset($array['hour_count']);
      $data_m = serialize( $array['data'] );
      unset($array['data']);
      $array['data_field'] = $data_m;

      $create = $projects_data->create($array);

      if ($create) {
        $msg_array['message'] = $msg = "project duration has been defined, please review and approve";
        $msg_array['user_r_id']  = $array['user_r_id'];
        $msg_array['user_id']  = $array['user_id'];
        $msg_array['project_id']  = $array['project_id'];
        $msg_array['m_type']  = "system";
        $messages->add($msg_array);

        //send email after

        $msg .= ". <a href='".$this->seo($array['project_id'], "view")."'>Click here</a> to continue review this request";

        $data = $users->listOne($array['user_r_id'], "ref");
        
        $client = $data['last_name']." ".$data['other_names'];
        $subjectToClient = "Duration Request";
        $contact = "MOBA <".replyMail.">";
        
        $fields = 'subject='.urlencode($subjectToClient).
            '&last_name='.urlencode($data['last_name']).
            '&other_names='.urlencode($data['other_names']).
            '&email='.urlencode($data['email']).
            '&tag='.urlencode($msg);
        $mailUrl = URL."includes/views/emails/notification.php?".$fields;
        $messageToClient = $this->curl_file_get_contents($mailUrl);
        
        $mail['from'] = $contact;
        $mail['to'] = $client." <".$data['email'].">";
        $mail['subject'] = $subjectToClient;
        $mail['body'] = $messageToClient;
        
        global $alerts;
        $alerts->sendEmail($mail);

        return true;
      } else {
        return false;
      }
      
    }

    public function processMilestone($array) {
      global $projects_data;
      global $messages;
      global $users;
      $array['project_id'] = $array['project'];
      unset($array['SubmitProposal']);
      unset($array['project']);
      unset($array['type']);
      unset($array['viewResponse']);
      $array['data']['status'] = "Pending";
      $data_m = serialize( $array['data'] );
      unset($array['data']);
      $array['data_field'] = $data_m;

      $create = $projects_data->create($array);

      if ($create) {
        $msg_array['message'] = $msg = "project milestone has been defined, please review and approve";
        $msg_array['user_r_id']  = $array['user_r_id'];
        $msg_array['user_id']  = $array['user_id'];
        $msg_array['project_id']  = $array['project_id'];
        $msg_array['m_type']  = "system";
        $messages->add($msg_array);

        //send email after

        $msg .= ". <a href='".$this->seo($array['project_id'], "view")."'>Click here</a> to continue review this request";

        $data = $users->listOne($array['user_r_id'], "ref");
        
        $client = $data['last_name']." ".$data['other_names'];
        $subjectToClient = "Milestone Request";
        $contact = "MOBA <".replyMail.">";
        
        $fields = 'subject='.urlencode($subjectToClient).
            '&last_name='.urlencode($data['last_name']).
            '&other_names='.urlencode($data['other_names']).
            '&email='.urlencode($data['email']).
            '&tag='.urlencode($msg);
        $mailUrl = URL."includes/views/emails/notification.php?".$fields;
        $messageToClient = $this->curl_file_get_contents($mailUrl);
        
        $mail['from'] = $contact;
        $mail['to'] = $client." <".$data['email'].">";
        $mail['subject'] = $subjectToClient;
        $mail['body'] = $messageToClient;
        
        global $alerts;
        $alerts->sendEmail($mail);

        return true;
      } else {
        return false;
      }
      
    }

    public function approveMilestone($array, $type="milestone") {
      global $projects_data;
      global $messages;
      global $users;

      $delData = $projects_data->listOne($array['ref']);

      if ($projects_data->modifyOne("status", "1", $array['ref'])) {
        $msg_array['message'] = $msg = "project ".$type." has been approved.";
        $msg_array['user_r_id']  = $delData['user_id'];
        $msg_array['user_id']  = $delData['user_r_id'];
        $msg_array['project_id']  = $delData['project_id'];
        $msg_array['m_type']  = "system";
        $messages->add($msg_array);

        //send email after

        $msg .= ". <a href='".$this->seo($array['project_id'], "view")."'>Click here</a> to continue review this request";

        $data = $users->listOne($delData['user_id'], "ref");
        
        $client = $data['last_name']." ".$data['other_names'];
        $subjectToClient = ucwords(strtolower($type))." Request Update";
        $contact = "MOBA <".replyMail.">";
        
        $fields = 'subject='.urlencode($subjectToClient).
            '&last_name='.urlencode($data['last_name']).
            '&other_names='.urlencode($data['other_names']).
            '&email='.urlencode($data['email']).
            '&tag='.urlencode($msg);
        $mailUrl = URL."includes/views/emails/notification.php?".$fields;
        $messageToClient = $this->curl_file_get_contents($mailUrl);
        
        $mail['from'] = $contact;
        $mail['to'] = $client." <".$data['email'].">";
        $mail['subject'] = $subjectToClient;
        $mail['body'] = $messageToClient;
        
        global $alerts;
        $alerts->sendEmail($mail);

        return true;
      } else {
        return false;
      }
    }

    public function rejectMilestone($array, $type="milestone") {
      global $projects_data;
      global $messages;
      global $users;
      $delData = $projects_data->listOne($array['milstoneData']);
      $reject = $projects_data->remove($array['milstoneData']);
      if ($reject) {
        $msg_array['message'] = $msg = "project ".$type." was not approved. Reason: ".$array['comment'];
        $msg_array['user_r_id']  = $delData['user_id'];
        $msg_array['user_id']  = $delData['user_r_id'];
        $msg_array['project_id']  = $delData['project_id'];
        $msg_array['m_type']  = "system";
        $messages->add($msg_array);

        //send email after

        $msg .= ". <a href='".$this->seo($array['project_id'], "view")."'>Click here</a> to continue review this request";

        $data = $users->listOne($delData['user_id'], "ref");
        
        $client = $data['last_name']." ".$data['other_names'];
        $subjectToClient = ucwords(strtolower($type))." Request Update";
        $contact = "MOBA <".replyMail.">";
        
        $fields = 'subject='.urlencode($subjectToClient).
            '&last_name='.urlencode($data['last_name']).
            '&other_names='.urlencode($data['other_names']).
            '&email='.urlencode($data['email']).
            '&tag='.urlencode($msg);
        $mailUrl = URL."includes/views/emails/notification.php?".$fields;
        $messageToClient = $this->curl_file_get_contents($mailUrl);
        
        $mail['from'] = $contact;
        $mail['to'] = $client." <".$data['email'].">";
        $mail['subject'] = $subjectToClient;
        $mail['body'] = $messageToClient;
        
        global $alerts;
        $alerts->sendEmail($mail);

        return true;
      } else {
        return false;
      }
    }

    private function reviewHour($array) {
      global $users;
      global $rating;
      global $projects_data;
      $ref = $array['project'];
      if (isset($_REQUEST['view'])) {
        $view = 1;
      } else {
        $view = 0;
      }
      $data = $this->listOne($ref, "ref");
      $milstoneData = $projects_data->checkCurrent($array, "getRow", $view);
      $rawData = unserialize($milstoneData['data_field']);
      ?>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h2><?php echo $data['project_name']; ?></h2>
          <p><?php echo $data['project_dec']; ?></p>
          <p><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['address']; ?></p>
          <p><i class="fa fa-eye" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->visitors($ref); ?></p>
          <p><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($data['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($data['user_id'], "screen_name"); ?></a>&nbsp;<?php echo $rating->drawRate($rating->getRate($data['user_id'])); ?></p>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h3>Review Duration Proposal</h3>
          <?php if ($view == 1) {
              if (isset($_REQUEST['viewResponse'])) {
              $and = "?viewResponse=".$_REQUEST['viewResponse'];
              } ?>
          <input type="button"  value="Done"  id="button" class="btn btn-primary btn-lg" name="button" onclick="location='<?php echo $this->seo($data['ref'], 'view').$and; ?>'"/>
          <?php } ?>
          <form  method="post" name="form" action="<?php echo URL; ?>jobs.confirmation">
            <table class="table">
              <thead>
                <tr>
                  <th colspan="4" scope="col">Duration</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="4"><?php echo $rawData['content']; ?></td>
                </tr>
                <?php if (($milstoneData['user_r_id'] == $_SESSION['users']['ref']) && (!isset($_REQUEST['view']))) { ?>
                <tr>
                  <th scope="col">Comment</th>
                  <td colspan="3"><textarea name="comment" class="form-control" placeholder="Enter your comments here. comments will be sent via project message" required></textarea> </th>
                </tr>
                <tr>
                  <th scope="col">&nbsp;</th>
                  <td colspan="3">
                    <input type="button" value="Approve Proposal"  id="Submit" class="btn btn-primary btn-lg" name="ap_h_proposal" onclick="location='<?php echo URL; ?>jobs.confirmation?project=<?php echo $array['project']; ?>&type=review_hours&ref=<?php echo $milstoneData['ref']; ?>&ap_h_proposal=1<?php if (isset($array['viewResponse'])) { echo '&viewResponse='.$array['viewResponse'];} ?>'"/>
                    <input type="Submit"  value="Reject Proposal"  id="Submit" class="btn btn-secondary btn-lg" name="rj_h_proposal"/>

                    <input type='hidden' name="project" id="project" value="<?php echo $array['project']; ?>" />
                    <?php if (isset($array['viewResponse'])) { ?>
                    <input type='hidden' name="viewResponse" value="<?php echo $array['viewResponse']; ?>" />
                    <?php } ?>
                    <input type='hidden' name="type" id="type" value="<?php echo $array['type']; ?>" />
                    <input type='hidden' name="milstoneData" id="milstoneData" value="<?php echo $milstoneData['ref']; ?>" />
                  </th>
                </tr>
                    <?php } ?>
              </tbody>
            </table>
          </form>
        </div>
      </div>
      <?php
    }

    private function reviewMilestone($array) {
      global $users;
      global $rating;
      global $projects_data;
      $ref = $array['project'];
      if (isset($_REQUEST['view'])) {
        $view = 1;
      } else {
        $view = 0;
      }
      $data = $this->listOne($ref, "ref");
      $milstoneData = $projects_data->checkCurrent($array, "getRow", $view);
      $rawData = unserialize($milstoneData['data_field']);
      $milstoneData['milestone_count'] = count($rawData['content']);
      $milstoneData['is_edit'] = $rawData['content'];
      ?>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h2><?php echo $data['project_name']; ?></h2>
          <p><?php echo $data['project_dec']; ?></p>
          <p><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['address']; ?></p>
          <p><i class="fa fa-eye" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->visitors($ref); ?></p>
          <p><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($data['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($data['user_id'], "screen_name"); ?></a>&nbsp;<?php echo $rating->drawRate($rating->getRate($data['user_id'])); ?></p>
        </div>
      </div>
      <?php if (($view == 1) || ( $array['user'] != $milstoneData['user_id'] )) {
        $this->confirmMilestone($milstoneData, false);
      } else { ?>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h3>Review Milestoone Proposal</h3>
          <form  method="post" name="form" action="<?php echo URL; ?>jobs.confirmation">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Milestone</th>
                  <th scope="col">Duration</th>
                </tr>
              </thead>
              <tbody>
                  <?php for ($i = 0; $i < count($rawData['content']); $i++) { ?>
                <tr>
                  <th scope="row"><?php echo $i+1; ?></th>
                  <td><?php echo $rawData['content'][$i]['data']; ?></td>
                  <td><?php echo $rawData['content'][$i]['duration']." ".$this->addS($rawData['content'][$i]['duration_lenght'], $rawData['content'][$i]['duration']); ?></td>
                </tr>
                  <?php } ?>
                  
                <tr>
                  <th scope="col">Comment</th>
                  <td colspan="3"><textarea name="comment" class="form-control" placeholder="Enter your comments here. comments will be sent via project message" required></textarea> </th>
                </tr>
                <tr>
                  <th scope="col">&nbsp;</th>
                  <td colspan="3">
                    <input type="button" value="Approve Proposal"  id="Submit" class="btn btn-primary btn-lg" name="ap_proposal" onclick="location='<?php echo URL; ?>jobs.confirmation?project=<?php echo $array['project']; ?>&type=review_milstone&ref=<?php echo $milstoneData['ref']; ?>&ap_proposal=1<?php if (isset($array['viewResponse'])) { echo '&viewResponse='.$array['viewResponse'];} ?>'"/>
                    <input type="Submit"  value="Reject Proposal"  id="Submit" class="btn btn-secondary btn-lg" name="rj_proposal"/>

                    <input type='hidden' name="project" id="project" value="<?php echo $array['project']; ?>" />
                    <?php if (isset($array['viewResponse'])) { ?>
                    <input type='hidden' name="viewResponse" value="<?php echo $array['viewResponse']; ?>" />
                    <?php } ?>
                    <input type='hidden' name="type" id="type" value="<?php echo $array['type']; ?>" />
                    <input type='hidden' name="milstoneData" id="milstoneData" value="<?php echo $milstoneData['ref']; ?>" />
                  </th>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
      </div>
      <?php } ?>
      <?php
    }

    public function confirmMilestone($array, $showHeader=true) {
      global $users;
      global $rating;
      $ref = $array['project'];
      $data = $this->listOne($ref, "ref");
      ?>
      <?php if ($showHeader == true) { ?>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h2><?php echo $data['project_name']; ?></h2>
            <p><?php echo $data['project_dec']; ?></p>
            <p><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['address']; ?></p>
            <p><i class="fa fa-eye" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->visitors($ref); ?></p>
            <p><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($data['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($data['user_id'], "screen_name"); ?></a>&nbsp;<?php echo $rating->drawRate($rating->getRate($data['user_id'])); ?></p>
          </div>
        </div>
      <?php } ?>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h3>Proposed Milestone Defination</h3>
          <form  method="post" name="form" action="<?php echo URL; ?>jobs.confirmation">
            <table class="table table-striped" width="100%" border="0">
              <tr>
                <th width="25%" scope="row">Number of Milestones</th>
                <td><?php echo $array['milestone_count']; ?></td>
              </tr>
              <input type="hidden" name="data[data_type]" value="milestone">
              <?php for ($i = 0; $i < $array['milestone_count']; $i++) {
                $sn = $i+1; ?>
              <tr>
                <th colspan="2" width="25%" scope="row">Milestone <?php echo $sn; ?></th>
              </tr>
              <tr>
                <th width="25%" scope="row">Milestones</th>
                <td><textarea name="data[content][<?php echo $i; ?>][data]" class="form-control" placeholder="Enter the description or deliverables for milestone <?php echo $sn; ?>" required<?php if (isset($array['is_edit'])) { ?> readonly<?php } ?>><?php echo $array['is_edit'][$i]['data']; ?></textarea></td>
              </tr>
              <tr>
                <th width="25%" scope="row">Duration</th>
                <td>
                <div class="row">
                  <div class="col">
                    <input name="data[content][<?php echo $i; ?>][duration]" required type="number"  class="form-control" value="<?php echo $array['is_edit'][$i]['duration']; ?>"<?php if (isset($array['is_edit'])) { ?> readonly<?php } ?>>
                  </div>
                  <div class="col">
              <select name="data[content][<?php echo $i; ?>][duration_lenght]" class="form-control input-lg" required<?php if (isset($array['is_edit'])) { ?> readonly<?php } ?>>
                      <option value="Minute">Minute</option>
              <option value="Hour"<?php if ($array['is_edit'][$i]['duration_lenght'] == "Hour"){?> selected<?php } ?>>Hours</option>
                      <option value="Day"<?php if ($array['is_edit'][$i]['duration_lenght'] == "Day"){?> selected<?php } ?>>Days</option>
                      <option value="Week"<?php if ($array['is_edit'][$i]['duration_lenght'] == "Week"){?> selected<?php } ?>>Weeks</option>
                      <option value="Month"<?php if ($array['is_edit'][$i]['duration_lenght'] == "Month"){?> selected<?php } ?>>Months</option>
                    </select>
                  </div>
                </div>
              </td>
              </tr>
              <?php } ?>
              <?php if (!isset($array['is_edit'])) { ?>
              <tr>
                <th width="25%" scope="row">&nbsp;</th>
                <td><input type="Submit"  value="Submit Proposal"  id="Submit" class="btn btn-primary btn-lg" name="SubmitProposal"/>
                
                <input type='hidden' name="type" id="type" value="milestone" />
                <input type='hidden' name="project" id="project" value="<?php echo $array['project']; ?>" />
                <?php if (isset($array['viewResponse'])) {
                  $and = "&viewResponse=".$array['viewResponse']; ?>
                <input type='hidden' name="viewResponse" value="<?php echo $array['viewResponse']; ?>" />
                <?php } ?>
                <input type='hidden' name="user_r_id" id="user_r_id_2" value="<?php echo $array['user_r_id']; ?>" />
                <input type='hidden' name="user_id" id="user_id_2" value="<?php echo $array['user_id']; ?>" />
              </td>
              </tr>
                <?php } else {
                    if (isset($_REQUEST['viewResponse'])) {
                    $and = "?viewResponse=".$_REQUEST['viewResponse'];
                    } ?>
                  <input type="button"  value="Done"  id="button" class="btn btn-primary btn-lg" name="button" onclick="location='<?php echo $this->seo($array['project_id'], 'view').$and; ?>'"/>
                <?php } ?>
            </table>
          </form>
        </div>
      </div>
      <?php
    }

    public function listAll($ref, $view, $redirect) {
      global $media;
      global $options;

      if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
      } else {
        $page = 0;
      }
      
      $limit = $options->get("result_per_page");
      $start = $page*$limit;

      $data = $this->listAllWalletData($ref, $view, $start, $limit);

      $list = $data['list'];
      $listCount = $data['listCount'];
      $tag = $data['tag']; ?>
        <h2><?php echo $tag; ?></h2>
      <table class="table">
        <thead>
          <tr>
            <th scope="col">#</th>
            <?php if ($view != "saved") { ?>
            <th scope="col">&nbsp;</th>
            <th scope="col">Name</th>
            <th scope="col">Code</th>
            <th scope="col">Address</th>
            <?php if (($view == "on-going")  || ($view == "running")){ ?>
            <th scope="col">Start Date</th>
            <th scope="col">Estimated End Date</th>
            <?php } else { ?>
            <th scope="col">Created</th>
            <th scope="col">Last Modified</th>
            <?php } ?>
            <?php } else { ?>
            <th scope="col">&nbsp;</th>
            <th scope="col">Name</th>
            <th scope="col">Saved</th>
            <?php } ?>
            <th scope="col">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < count($list); $i++) { ?>
          <tr>
            <th scope="row"><?php echo $start+$i+1; ?></th>
            <?php if ($view != "saved") { ?>
            <td><img src="<?php echo $media->getCover($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['project_name']; ?>" class="img-thumbnail" style="width:auto;
        height:75px;" height="50px"></td>
            <td><?php echo $list[$i]['project_name']; ?></td>
            <td><?php echo $list[$i]['project_code']; ?></td>
            <td><?php echo $list[$i]['address']; ?></td>
            <?php if (($view == "on-going")  || ($view == "running")){ ?>
            <td><?php echo $this->print_time( $list[$i]['start_date'] ); ?></td>
            <td><?php echo $this->print_time($list[$i]['end_date'] ); ?></td>
            <?php } else { ?>
            <td><?php echo $list[$i]['create_time']; ?></td>
            <td><?php echo $list[$i]['modify_time']; ?></td>
            <?php } ?>
            <td><?php echo $this->getLink($list[$i]['ref'], $list[$i]['status'], $redirect, $view); ?></td>
            <?php } else { ?>
            <td><img src="<?php echo $media->getCover($list[$i]['project_id']); ?>" alt="<?php echo $this->getSingle( $list[$i]['project_id'] ); ?>" class="img-thumbnail" style="width:auto;
        height:75px;" height="50px"></td>
            <td><?php echo $this->getSingle( $list[$i]['project_id'] ); ?></td>
            <td><?php echo $list[$i]['create_time']; ?></td>
            <td><?php echo $this->getLink($list[$i]['ref'], "saved", $redirect, $view); ?></td>
            <?php } ?>
          </tr>
            <?php } ?>
        </tbody>
      </table>
      <?php $this->pagination($page, $listCount);
    }

    public function getLink($ref, $status, $redirect, $view) {
      global $project_save;
      $data = $this->listOne($ref, "ref");
      if ($status == "saved") {
        $saveData = $project_save->listOne($ref);
        $data = $this->listOne($saveData['project_id'], "ref");

        if ($data['status'] != "ACTIVE") {
          $tag = '<spam class="">Expired</span>';
        } else {
          $tag = '<a href="'.$this->seo($data['ref'], "view").'">View</a>';
        }

        $tag .= ' | <a href="'.URL.$redirect.'/saved?removeSaved='.$ref.'" onClick="return confirm(\'this action will remove this ad from your saved list. are you sure you want to continue ?\')"">Delete</a>';

        return $tag;
      } else if ($status == "NEW") {
        return '<a href="'.URL.'confirmHire?ref='.$ref.'">Continue</a> | <a href="'.URL.$redirect.'/draft?remove='.$ref.'" onClick="return confirm(\'this action will remove this ad. are you sure you want to continue ?\')"">Delete</a>';
      } else if ($status == "ON-GOING") {
        return '<a href="'.$this->seo($ref, "profile").'">View</a>';
      } else if ($status == "COMPLETED") {
        $link =  '<a href="'.$this->seo($ref, "profile").'">View</a>';
        if ($data['user_id'] == $_SESSION['users']['ref']) {
          $link .= ' | <a href="'.URL.'hire?relist='.$ref.'">List Again</a>';
        }
        return $link;
      } else if ($status == "ACTIVE") {
          $return = '<a href="'.$this->seo($ref, "view").'">View</a>';
          if ($view != "conversation") {
            $return .= ' | <a href="'.URL.$redirect.'/?remove='.$ref.'" onClick="return confirm(\'this action will remove this ad. are you sure you want to continue ?\')"">Delete</a>';
          }

          return $return;
      }
    }

    function removeDraft($id) {
      $rem = $this->remove($id);

      if ($rem) {
          return true;
      } else {
          return false;
      }
    }

    private function commonHeader($ref, $arrayData=false) {
      global $media;
      global $users;
      global $rating;
      global $country;
      global $project_save;

      $data = $this->listOne($ref, "ref");
      $getAlbum = $media->getAlbum($ref);

      $feeDataArray = array("id"=>$ref, "wallet_user"=>$data['user_id'], "wallet_collector"=>$data['client_id']);
      $feeData = $this->getFeeTotal($feeDataArray);
      ?>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h2><?php echo $data['project_name']; ?></h2>
          <p><?php echo $data['project_dec']; ?></p>
          <p><i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $data['address']; ?></p>
          <?php if ($data['status'] == "ACTIVE") { ?>
            <p><i class="fa fa-eye" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $this->visitors($ref); ?></p>
          <?php } else { ?>
            <p><i class="fas fa-clock"></i>&nbsp;&nbsp;<?php echo $this->print_time( $data['start_date'] ); ?></p>
            <p><i class="far fa-clock"></i>&nbsp;&nbsp;<?php echo $this->print_time( $data['end_date'] ); ?></p>
          <?php } ?>          
          <p><i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($data['user_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($data['user_id'], "screen_name"); ?></a>&nbsp;<?php echo $rating->drawRate($rating->getRate($data['user_id'])); ?>
          <?php if ($data['status'] == "ON-GOING") { ?>&nbsp;&nbsp;&nbsp;<i class="fa fa-user" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo URL."profile/".$users->listOnValue($data['client_id'], "screen_name"); ?>" target="_blank"><?php echo $users->listOnValue($data['client_id'], "screen_name"); ?></a>&nbsp;<?php echo $rating->drawRate($rating->getRate($data['client_id'])); } ?></p>
          <span id="showSave"><?php $project_save->getStatus($ref); ?></span>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
          <?php if (count($getAlbum) > 0) {
          for ($i = 0; $i < count($getAlbum); $i++) { ?>
            <a data-fancybox="gallery" href="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>"><img src="<?php echo $media->getCover($getAlbum[$i]['ref'], "ref"); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:150px;"></a>
          <?php }
          } else { ?>
            <img src="<?php echo $media->mediaDefault(); ?>" alt="<?php echo $data['project_name']; ?>" class="img-thumbnail" style="width:auto; height:250px;">
          <?php } ?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
          <table class="table table-striped" width="100%" border="0">
            <tr>
              <th scope="row">Listed Category(s)</th>
              <td><?php echo $this->getTagFromWord($data['category_id'], "category", "blank"); ?></td>
            </tr>
            <tr>
              <th scope="row">Ad Type</th>
              <td><?php echo $this->cleanText( $data['project_type'] ); ?></td>
            </tr>
            <tr>
              <th scope="row">Billing Type</th>
              <td><?php echo $this->cleanText( $data['billing_type'] ); ?></td>
            </tr>
            <tr>
              <th scope="row">Default Fee</th>
              <td><?php if ($this->getFee($arrayData) != $data['default_fee'] ) { ?>
              <s><?php echo $country->getCountryData( $data['country'] )." ".number_format($data['default_fee'], 2); ?></s>&nbsp;<?php echo $country->getCountryData( $data['country'] )." ".number_format($this->getFee($arrayData), 2); ?>
              <?php } else {echo $country->getCountryData( $data['country'] )." ".number_format($data['default_fee'], 2);} ?></td>
            </tr>
            <?php if (($data['status'] == "ON-GOING") && ($data['billing_type'] == "per_mil")) { ?>
            <tr>
              <th scope="row">Total Milestones and Deliverables</th>
              <td><?php echo number_format($feeData['count']); ?></td>
            </tr>
            <tr>
              <th scope="row">Total Amount based on Negotiated Milestones</th>
              <td><?php echo $country->getCountryData( $data['country'] )." ".number_format($feeData['fee'], 2); ?></td>
            </tr>
            <?php } else if (($data['status'] == "ON-GOING") && ($data['billing_type'] == "per_hour")) { ?>
            <tr>
              <th scope="row">Total Hours</th>
              <td><?php echo number_format($feeData['count']); ?></td>
            </tr>
            <tr>
              <th scope="row">Total Amount based on Negotiated Hours</th>
              <td><?php echo $country->getCountryData( $data['country'] )." ".number_format($feeData['fee'], 2); ?></td>
            </tr>
            <?php } ?>
            <?php if (($data['user_id'] == $_SESSION['users']['ref']) && ($data['is_featured'] == 1)) { ?>
            <tr>
              <th scope="row">Featured Till</th>
              <td><?php echo $this->get_time_stamp( $data['is_featured_time'] ); ?></td>
            </tr>
            <?php } ?>
            <tr>
              <th scope="row">Created</th>
              <td><?php echo $data['create_time']; ?></td>
            </tr>
            <tr>
              <th scope="row">Last Modified</th>
              <td><?php echo $data['modify_time']; ?></td>
            </tr>
            <tr>
              <th scope="row">Tags</th>
              <td><?php echo $this->getTagFromWord($data['tag'], "tag", "blank"); ?></td>
            </tr>
            <?php if ($data['user_id'] == trim($_SESSION['users']['ref'])) { ?>
            <tr>
              <td>&nbsp;</td>
              <td><a href="<?php echo URL."hire/".$data['project_type']."?edit=".$data['ref']; ?>">Edit</a></td>
            </tr>
            <?php } ?>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
            <div id="map" style="height:200px;width:100%;"></div>
        </div>
      </div>
      
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
      <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js" async defer></script>

      <script type="text/javascript">
        $( "#save_icon" ).click(function() {
          $("#showSave").html('');
          var id = $(this).data("id");
          var ref = $(this).data("ref");
          var action = $(this).data("action");
          var dataString = 'id='+ id+"&ref="+ref+"&action="+action;
          $.ajax({
            type: "POST",
            url: "<?php echo URL; ?>includes/views/scripts/saveButton",
            data: dataString,
            cache: false,
            success: function(html){
              $("#showSave").html(html);
            }
          });
        });
        // Initialize and add the map

        function initMap() {
          var marker = {lat: <?php echo $data['lat']; ?>, lng: <?php echo $data['lng']; ?>};
          var map = new google.maps.Map(
              document.getElementById('map'), {zoom: 15, center: marker});
          var marker = new google.maps.Marker({position: marker, map: map});
        }
      </script>
      <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GoogleAPI; ?>&callback=initMap"></script>
    <?php }

    private function viewProfileAction($ref) {
      global $users;
      global $projects_data;
      $data = $this->listOne($ref, "ref");
      
      if ($data['user_id'] == $_SESSION['users']['ref']) {
        $user_id = $data['user_id'];
        $user_r_id = $data['client_id'];
      } else {
        $user_id = $data['client_id'];
        $user_r_id = $data['user_id'];
      }
      
      if ($data['project_type'] == "client") {
        $approveer = $data['user_id'];
        $not_approver = $data['client_id'];
      } else {
        $approveer = $data['client_id'];
        $not_approver = $data['user_id'];
      }
      if ($data['billing_type'] != "per_job") {
        $d_array['project'] = $ref;
        $d_array['user'] = $user_id;
        $d_array['user_r'] = $user_r_id;
        $get_data = $projects_data->checkCurrent($d_array, "getRow", 1);
        $raw_get_data = unserialize($get_data['data_field']);
      }
      ?>
      <?php if (($data['status'] != "COMPLETED") || ($data['billing_type'] != "per_job")) { ?> 
      <h2><a name="deliverables"></a>Work Plan with <?php echo $users->listOnValue($user_r_id, "screen_name"); ?></h2>
      <?php } ?>
      <?php if ($raw_get_data['data_type'] == "hours") { ?>
        <form method="post" action="">
          <table class="table">
            <tbody>
              <tr>
                <td width="25%">Hours Completed</td>
                <td><?php echo $raw_get_data['complete']." ".$this->addS("Hour", $raw_get_data['complete']); ?></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td width="25%">Hours Remaining</td>
                <td><?php echo $raw_get_data['content']." ".$this->addS("Hour", $raw_get_data['content']); ?></td>
                <td>&nbsp;</td>
              </tr>
              <?php if ($raw_get_data['content'] > 0) { ?>
              <tr>
                <td>Log Hours</td>
                <td>
                <select class="form-control" name="duration" id="duration">
                  <?php for ($i = 1; $i <= $raw_get_data['content']; $i++) { ?>
                  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                  <?php } ?>
                </select>
                </td>
                <td><button type="submit" name="log" value="log_hours" class="btn btn-primary">Log Hours</button></td>
              </tr>
              <?php } else {
                if (($approveer == $_SESSION['users']['ref']) && ($data['status'] != "COMPLETED")) { ?>
              <tr>
                <td>&nbsp;</td>
                <td><button type="submit" name="approve" class="btn btn-primary">Mark as Completed</button></td>
                <td>&nbsp;</td>
              </tr>
              <?php }
              }
              if (($not_approver == $_SESSION['users']['ref']) && ($data['status'] != "COMPLETED")) { ?>
              <tr>
                <td>&nbsp;</td>
                <td><button type="submit" name="request_approve" class="btn btn-primary">Request Final Review</button></td>
                <td>&nbsp;</td>
              </tr>
              <?php } ?>
            </tbody>
            <input type="hidden" name="project_data_id" value="<?php echo $get_data['ref']; ?>">
            <input type="hidden" name="project_id" value="<?php echo $data['ref']; ?>">
          </table>
        </form>
      <?php } else if ($raw_get_data['data_type'] == "milestone") { ?>
        <table class="table">
          <thead class="thead-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Milestone</th>
              <th scope="col">Duration</th>
              <th scope="col">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
            <?php $complete = 0;
            unset($raw_get_data['content']['status']);
            for ($i = 0; $i < count($raw_get_data['content']); $i++) { ?>
              <form method="post" name="form<?php echo $i; ?>" action="">
              <input type="hidden" name="project_data_id" value="<?php echo $get_data['ref']; ?>">
              <input type="hidden" name="milestone_data_id" value="<?php echo $i; ?>">
              <input type="hidden" name="project_id" value="<?php echo $data['ref']; ?>">
              <tr>
                <th scope="row"><?php echo $i+1; ?></th>
                <td><?php echo $raw_get_data['content'][$i]['data']; ?></td>
                <td><?php echo $raw_get_data['content'][$i]['duration']." ".$this->addS($raw_get_data['content'][$i]['duration_lenght'], $raw_get_data['content'][$i]['duration']); ?></td>
                <?php if (intval($raw_get_data['content'][$i]['complete']) == 1) {
                  $complete = $complete+1; ?>
                <td>Completed</td>
                <?php } else {
                  if ($approveer == $_SESSION['users']['ref']) { ?>
                <td><button type="submit" name="log" value="log_mil"<?php if (intval($raw_get_data['content'][$i]['attention']) == 1) { ?> class="btn btn-warning"<?php } else { ?> class="btn btn-primary"<?php } ?>><?php if (intval($raw_get_data['content'][$i]['attention']) == 1) { ?><i class="fa fa-exclamation-triangle" aria-hidden="true"></i><?php } ?> Log Milestone</button></td>
                <?php } else { ?>
                <td><button type="submit" name="log" value="log_mil_review" class="btn btn-primary"><?php if (intval($raw_get_data['content'][$i]['attention']) == 1) { ?>Resend <?php } ?>Request Review</button></td>
                <?php } } ?>
              </tr>
              <?php if (($complete == count($raw_get_data['content'])) && (($data['client_id'] == $_SESSION['users']['ref'])  || ($data['user_id'] == $_SESSION['users']['ref']))){
                if (($approveer == $_SESSION['users']['ref']) && ($data['status'] != "COMPLETED")) {?>
              <tr>
                <td>&nbsp;</td>
                <td><button type="submit" name="approve" class="btn btn-primary">Mark as Completed</button></td>
                <td colspan="2">&nbsp;</td>
              </tr>
              <?php } else if (($not_approver == $_SESSION['users']['ref']) && ($data['status'] != "COMPLETED")) { ?>
              <tr>
                <td>&nbsp;</td>
                <td><button type="submit" name="request_approve" class="btn btn-primary">Request Final Review</button></td>
                <td colspan="2">&nbsp;</td>
              </tr>
              <?php }
              } ?>
              </form>
            <?php } ?>
          </tbody>
        </table>
      <?php } else { ?>
        <form method="post" action="">
          <input type="hidden" name="project_id" value="<?php echo $data['ref']; ?>">
          <table class="table">
            <tbody>
              <?php if (($approveer == $_SESSION['users']['ref']) && ($data['status'] != "COMPLETED")) { ?>
                <tr>
                  <td>&nbsp;</td>
                  <td><button type="submit" name="approve" class="btn btn-primary">Mark as Completed</button></td>
                  <td>&nbsp;</td>
                </tr>
              <?php } else if (($not_approver == $_SESSION['users']['ref']) && ($data['status'] != "COMPLETED")) { ?>
                <tr>
                  <td>&nbsp;</td>
                  <td><button type="submit" name="request_approve" class="btn btn-primary">Request Final Review</button></td>
                  <td>&nbsp;</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </form>
      <?php }
      $this->review($ref);
    }

    private function review($ref) {
      global $users;
      global $rating_question;
      global $rating_comment;
      global $rating;
      $data = $this->listOne($ref, "ref");
      
      if ($data['user_id'] == $_SESSION['users']['ref']) {
        $tagline = "user_id";
        $user_id = $data['client_id'];
        $user_r_id = $data['user_id'];
        if ($data['project_type'] == "client") {
          $rateType = "vendors";
        } else {
          $rateType = "clients";
        }
      } else {
        $tagline = "client_id";
        $user_id = $data['user_id'];
        $user_r_id = $data['client_id'];
        if ($data['project_type'] == "client") {
          $rateType = "clients";
        } else {
          $rateType = "vendors";
        }
      }

      $checkRate = $rating->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "project_id", $ref);
      $checkComment = $rating_comment->getSortedList($user_r_id, "reviewed_by", "user_id", $user_id, "project_id", $ref, "ref", "ASC", "AND", false, false, "getRow");

      $rateQuestion = $rating_question->getSortedList($rateType, "question_type");

      if ($data['status'] == "COMPLETED") { ?>
        <h2>Review And Comment</h2>
        <?php if (count($checkRate) > 0) { ?>
          <p>You have rated <?php echo $users->listOnValue($user_id, "screen_name"); ?> on this task</p>
          <?php for ($i = 0; $i < count($checkRate); $i++) { ?>
            <strong><?php echo $rating_question->getSingle( $checkRate[$i]['question_id'] ); ?></strong><br>
            <?php echo $rating->drawRate($checkRate[$i]['review']); ?><br><br>
          <?php } ?>
          <p><strong>Comments</strong><br>
          <?php echo $checkComment['comment']; ?><p>

        <?php } else { ?>
        <p>Kindly rate <?php echo $users->listOnValue($user_id, "screen_name"); ?> performance on the following headings:</p>
        <form method="post" action="">
          <input name="user_id" type="hidden" value="<?php echo $user_id; ?>">
          <input name="reviewed_by" type="hidden" value="<?php echo $user_r_id; ?>">
          <input name="project_id" type="hidden" value="<?php echo $ref; ?>">
        <?php for ($i = 0; $i < count($rateQuestion); $i++) { ?>
        <p>
        <strong><?php echo $rateQuestion[$i]['question']; ?></strong><br>
        <fieldset class="rating form-group">
          <input type="radio" id="star5<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="5" /><label for="star5<?php echo $rateQuestion[$i]['ref']; ?>" title="Rocks!">5 stars</label>
          <input type="radio" id="star4<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="4" /><label for="star4<?php echo $rateQuestion[$i]['ref']; ?>" title="Pretty good">4 stars</label>
          <input type="radio" id="star3<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="3" /><label for="star3<?php echo $rateQuestion[$i]['ref']; ?>" title="Meh">3 stars</label>
          <input type="radio" id="star2<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="2" /><label for="star2<?php echo $rateQuestion[$i]['ref']; ?>" title="Kinda bad">2 stars</label>
          <input type="radio" id="star1<?php echo $rateQuestion[$i]['ref']; ?>" name="rating[<?php echo $rateQuestion[$i]['ref']; ?>]" value="1" /><label for="star1<?php echo $rateQuestion[$i]['ref']; ?>" title="Sucks big time">1 star</label>
        </fieldset>
        <br><br>
        </p>
        <?php } ?>
        <div class="form-group">
          <label for="comment">Comments</label>
          <small>Optional</small>
          <textarea name="comment" id="comment" class="form-control" placeholder="Optional comment"></textarea>
        </div>
        <input type="hidden" name="type" value="<?php echo $tagline; ?>">
        <button type="submit" name="saveRate" class="btn btn-primary">Rate</button>
      </form>
      <?php }
      }
    }

    private function viewProfile($ref) {
      global $messages;
      global $users;
      global $country;
      $data = $this->listOne($ref, "ref");

      if ($data['user_id'] == $_SESSION['users']['ref']) {
        $user_id = $data['user_id'];
        $user_r_id = $data['client_id'];
      } else {
        $user_id = $data['client_id'];
        $user_r_id = $data['user_id'];
      }
      
      $messages->markRead($_SESSION['users']['ref'], $ref);
      $initialComment = $messages->getPage($ref, $data['client_id'], $data['user_id']);
      
      $this->commonHeader($ref, array("project" => $ref, "user" => $user_id, "user_r" => $user_r_id ));
      $this->viewProfileAction($ref); ?>
      <h2><a name="messages"></a>Messages with <?php echo $users->listOnValue($user_r_id, "screen_name"); ?></h2>
      <div class="row">
        <ol id="update" >
          <?php for ($i = 0; $i < count($initialComment); $i++) { ?>
            <li class="media" id='<?php echo $initialComment[$i]['ref']; ?>'>
            <?php $users->getProfileImage($initialComment[$i]['user_id'], "mr-3", "50"); ?>
            <div class="media-body">
              <small class="pull-right time"><i class="fa fa-clock-o"></i> <?php echo $this->get_time_stamp(strtotime($initialComment[$i]['create_time'])); ?></small>
              <p class="mt-0">
              <?php if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                $m_type_data = explode("_", $initialComment[$i]['m_type_data'] ) ?>
                <i class="fa fa-handshake" aria-hidden="true"></i><br><?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>You have a <?php } ?>new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['country'] )." " .$m_type_data[0]; ?></strong>
              <?php } else if ($initialComment[$i]['m_type'] == "system" ) {
                echo "<i class='fa fa-exclamation' aria-hidden='true'></i>     ".$initialComment[$i]['message'];
              } else {
                echo $initialComment[$i]['message'];
              } ?>
              </p>
            </div>
            </li>
          <?php } ?>
        </ol>
        <div id="flash"></div>
      </div>
      <?php if ($data['status'] != "COMPLETED") { ?>
      <div class="row with-margin">
        <div class="col-lg-12">
          <form  method="post" name="form" action="">
            <div class="input-group input-group-lg">
              <?php $users->getProfileImage($user_id, "50"); ?> 
              <input type='text' name="content" id="content" class="form-control input-lg" placeholder="Enter your message here..." />
              <input type='hidden' name="user_r_id" id="user_r_id" value="<?php echo $user_r_id; ?>" />
              <input type='hidden' name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
              <input type='hidden' name="project_id" id="project_id" value="<?php echo $ref; ?>" />
              <input type='hidden' name="m_type" id="m_type" value="text" />
              <span class="input-group-btn">

              <input type="button"  value="Post"  id="post" class="btn btn-primary btn-lg" name="post"/>
              </span>
            </div><!-- /input-group -->
          </form>
        </div><!-- /.col-lg-6 -->
      </div><!-- /.row -->
      <?php } ?>
      
      <script type="text/javascript">
        var ref='<?php echo $ref;?>';
        var user_id='<?php echo $user_id;?>';
        var user_r_id='<?php echo $user_r_id;?>';
        var auto_refresh = setInterval(function () {
          var b=$("ol#update li:last").attr("id");
          $.getJSON("<?php echo URL; ?>includes/views/scripts/chat_json?project_id="+ref+"&user_id="+user_id+"&user_r_id="+user_r_id,function(data) {
            $.each(data.posts, function(i,data) {
              if(b != data.id) {
                var dataString = 'id='+ data.user;
                $.ajax({
                    type: "POST",
                    url: "<?php echo URL; ?>includes/views/scripts/draw",
                    data: dataString,
                    cache: false,
                    success: function(html){
                      if (data.m_type == "system") {
                        var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='pull-right time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'><i class='fa fa-exclamation' aria-hidden='true'></i>     "+data.msg+"</p></div></li>";
                      } else if (data.m_type == "negotiate_charges") {
                        var msg = '<i class="fa fa-handshake-o" aria-hidden="true"></i><br>You have a new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['country'] ); ?>'+data.data_1+'</strong>'

                        var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='pull-right time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+msg+"</p></div></li>";
                      } else {
                        var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='pull-right time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+data.msg+"</p></div></li>";
                      }
                      $(div_data).appendTo("ol#update");
                    }
                  });
              }
            });
          });
          $('.main_img').initial({
            width:50,
            charCount: 1,
            fontSize: 40,
            fontWeight: 400,
            height:50
          });

        }, 2000);	

        $(document).ready(function() {
          $('#post').click(function() {
            post();
          });
          $('#content').focus(function() {
            var user_id = $("#user_id").val();
            var project_id = $("#project_id").val();
            var dataString = 'user_id='+ user_id+"&project_id="+project_id;
            $.ajax({
              type: "POST",
              url: "<?php echo URL; ?>includes/views/scripts/markRead",
              data: dataString,
              cache: false
            });
          });
        });

        function post() {
          var boxval = $("#content").val();
          var user_r_id = $("#user_r_id").val();
          var user_id = $("#user_id").val();
          var project_id = $("#project_id").val();
          var m_type = $("#m_type").val();
          var dataString = 'user_id='+ user_id + '&user_r_id=' + user_r_id + '&project_id=' + project_id + '&m_type=' + m_type + '&content=' + boxval;

          if(boxval.length > 0) {
            $("#flash").show();
            $("#flash").fadeIn(400).html('<img src="<?php echo URL."img/loading.gif"; ?>" align="absmiddle">&nbsp;<span class="loading">Loading Update...</span>');
            $.ajax({
              type: "POST",
              url: "<?php echo URL; ?>includes/views/scripts/chatajax",
              data: dataString,
              cache: false,
              success: function(html){
                $("ol#update").append(html);

                $('#content').val('');
                $('#content').focus();
                $("#flash").hide();
              }

            });
          }
          return false;
        }

        $(document).on('keypress', 'form input[type="text"]', function(e) {
          if(e.which == 13) {
            e.preventDefault();
            post();
            return false;
          }
        });
      </script>
    <?php  
    }

    private function viewPage($ref) {
      global $users;
      global $country;
      global $messages;
      global $userPayment;
      global $projects_data;
      global $wallet;
      $data = $this->listOne($ref, "ref");
      
      if (isset($_REQUEST['viewResponse'])) {
        $responderRef = $respondsRef = $_REQUEST['viewResponse'];
        $and = "&viewResponse=".$_REQUEST['viewResponse'];
      } else {
        $responderRef = $_SESSION['users']['ref'];
        $respondsRef = $data['user_id'];
        $and = "";
      }

      if ($data['project_type'] == "vendor") {
        $wallet_user = $responderRef;
        $wallet_collector = $data['user_id'];
      } else {
        $wallet_user = $data['user_id'];
        $wallet_collector = $responderRef;
      }


      $verify_me = $users->listOnValue($_SESSION['users']['ref'], "verified");
      $verify_other = $users->listOnValue($respondsRef, "verified");
      //get wallet balnce
      $wallet_balance = floatval($wallet->balance($wallet_user, $data['region']));
      $feeData = $this->getFeeTotal( array("id"=>$ref, "wallet_user"=>$wallet_user, "wallet_collector"=>$wallet_collector) );
      $fee = $feeData['fee'];

      if ( ($_SESSION['users']['ref'] == $data['user_id']) && (isset($_REQUEST['viewResponse']))) {
        $messages->markReadOne($_SESSION['users']['ref'], $_REQUEST['viewResponse'], $ref);
      } else if ($_SESSION['users']['ref'] != $data['user_id'])  {
        $messages->markRead($_SESSION['users']['ref'], $ref);
      }

      $list = $userPayment->getSortedList($_SESSION['users']['ref'], "user_id");
      $initialComment = $messages->getPage($ref, $responderRef, $data['user_id']);
      $getResponse = $messages->getResponse($ref, $_SESSION['users']['ref']);
      $approve = false;

      if ($data['billing_type'] == "per_job") {
        $approve = true;
      }
      
      $this->commonHeader($ref, array("project" => $ref, "user" => $_SESSION['users']['ref'], "user_r" => $respondsRef ));
      
      if ((isset($_SESSION['users']['ref'])) && ($_SESSION['users']['status'] != "NEW")) {?>
      <h2>Options</h2>
      <div class="row">
        <table class="table table-striped" width="100%" border="0">
          <!-- check if the conversation has started -->
          <?php if ((count($initialComment) == 0) && (($_SESSION['users']['ref'] != $data['user_id']))) { ?>
            <!-- start conversation -->
            <tr>
              <th width=20% scope="row">Show Interest</th>
              <td>
                <input type='hidden' name="content" id="content" value="Hello, i am interested in this Advert" />
                <input type='hidden' name="user_r_id" id="user_r_id" value="<?php echo $respondsRef; ?>" />
                <input type='hidden' name="user_id" id="user_id" value="<?php echo $_SESSION['users']['ref']; ?>" />
                <input type='hidden' name="project_id" id="project_id" value="<?php echo $ref; ?>" />
                <input type='hidden' name="m_type" id="m_type" value="text" />
                <input type="button"  value="I am Interested"  id="post" class="btn btn-primary btn-lg interested" name="post"/>
              </td>
            </tr>
            <!-- end of start conversation -->
            <?php } else { ?>
              <?php if (
            ($_SESSION['users']['ref'] != $data['user_id']) ||  (($_SESSION['users']['ref'] == $data['user_id']) && (isset($_REQUEST['viewResponse'])))) {
              if (($this->checkNegotiate(array("project" => $ref, "user" => $_SESSION['users']['ref'], "user_r" => $respondsRef )) == 0) && ($this->checkNegotiate(array("project" => $ref, "user" => $respondsRef, "user_r" => $_SESSION['users']['ref'])) == 0) && (count($initialComment) > 0) ) {  ?>
            <tr>
              <th scope="row">Negotiate Charges</th>
              <td>
              <div class="row with-margin">
                  <div class="col-lg-12">
                    <form  method="post" name="form" action="">
                      <div class="input-group input-group-lg">
                      <input type='number' step="0.01" name="negotiated_fee" id="negotiated_fee" class="form-control input-lg" value="<?php echo number_format($this->getFee(array("project" => $ref, "user" => $_SESSION['users']['ref'], "user_r" => $respondsRef)), 2); ?>" />
                      <input type='hidden' name="user_r_id" id="user_r_id" value="<?php echo $respondsRef; ?>" />
                      
                      <?php if (isset($_REQUEST['viewResponse'])) { ?>
                        <input type='hidden' name="viewResponse" id="viewResponse" value="<?php echo $_REQUEST['viewResponse']; ?>" />
                      <?php } ?>
                      <input type='hidden' name="user_id" id="user_id" value="<?php echo $_SESSION['users']['ref']; ?>" />
                      <input type='hidden' name="project_id" id="project_id" value="<?php echo $ref; ?>" />
                      <input type='hidden' name="m_type" id="m_type_2" value="negotiate_charges" />
                        <input type="submit" disabled value="Negotiate"  id="negotiate" class="btn btn-primary btn-lg" name="negotiate"/>
                      </div><!-- /input-group -->
                    </form>
                  </div><!-- /.col-lg-6 -->
                </div><!-- /.row -->
              </td>
            </tr>
            <?php } else { ?>
            <tr>
              <th scope="row">Negotiate Charges</th>
              <td><i class="fas fa-exclamation-triangle"></i> Price Negotiation request already sent, check your messages for more information on this notification</td>
            </tr>
            <?php } ?>
            <?php if ($data['billing_type'] == "per_mil") { ?>
            <tr>
              <th scope="row">Propose Milestones</th>
              <td>
                <?php if ($this->checkMilestone(array("project" => $ref, "user" => $_SESSION['users']['ref'], "user_r" => $respondsRef ), "count", 1) == 1) {
                  $approve = true;
                  echo "<i class='fas fa-exclamation-circle'></i>  A milestone has been proposed and approved. <a href='".URL."jobs.confirmation?project=".$ref."&type=review_milstone&user_r=".$_SESSION['users']['ref']."&view&user=".$respondsRef.$and."'>Click here</a> to go see this milestone";
                } else if ($this->checkMilestone(array("project" => $ref, "user" => $_SESSION['users']['ref'], "user_r" => $respondsRef )) == 0) { ?>
                
                <div class="row with-margin">
                  <div class="col-lg-12">
                    <?php
                    
      ?>
                  <?php if ($wallet_user != $_SESSION['users']['ref']) { ?>
                    <form  method="post" name="form" action="<?php echo URL; ?>jobs.confirmation">
                      <div class="input-group input-group-lg">
                        <input type='hidden' name="type" id="type" value="milestone" />
                        <input type='hidden' name="project" id="project" value="<?php echo $ref; ?>" />
                        <input type='number' name="milestone_count" id="milestone_count" class="form-control input-lg" placeholder="Number of proposed milestones to achieve" required />
                        <?php if (isset($_REQUEST['viewResponse'])) { ?>
                        <input type='hidden' name="viewResponse" id="viewResponse" value="<?php echo $_REQUEST['viewResponse']; ?>" />
                        <?php } ?>
                        <input type='hidden' name="user_r_id" id="user_r_id_2" value="<?php echo $respondsRef; ?>" />
                        <input type='hidden' name="user_id" id="user_id_2" value="<?php echo $_SESSION['users']['ref']; ?>" />
                        <input type="submit"  value="Setup Milestone and Deliverables" id="setup_milestone" class="btn btn-primary btn-lg" name="setup_milestone"/>
                      </div><!-- /input-group -->
                    </form>
                    <?php } else { ?>
                      <p><i class='fas fa-exclamation-circle'></i>  You can not propose a milestone for this ad. since you are the one requesting the service. once a milestone has been proposed, you will be able to review and approve the proposal here.</p>
                    <?php } ?>
                  </div><!-- /.col-lg-6 -->
                </div><!-- /.row -->
                <?php } else {                    
                  if ($projects_data->findMe(array("project" => $ref, "user" => $_SESSION['users']['ref'] )) == 0) {
                    echo "<i class='fas fa-exclamation-circle'></i>  You have a milestone proposal to approve. <a href='".URL."jobs.confirmation?project=".$ref."&type=review_milstone&user_r=".$_SESSION['users']['ref']."&user=".$respondsRef.$and."'>Click here</a> to go see this request";
                  } else {
                    echo "<i class='fas fa-exclamation-circle'></i>  Your sent milestone proposal is waiting for approval. <a href='".URL."jobs.confirmation?project=".$ref."&type=review_milstone&user_r=".$_SESSION['users']['ref']."&user=".$respondsRef.$and."'>Click here</a> to go see this request";
                  }
                } ?>
              </td>
            </tr>
            <?php } else if ($data['billing_type'] == "per_hour") { ?>
            <tr>
              <th scope="row">Propose Hours</th>
              <td>

              <?php if ($this->checkMilestone(array("project" => $ref, "user" => $_SESSION['users']['ref'], "user_r" => $respondsRef ), "count", 1) == 1) {
                $approve = true;
                  echo "<i class='fas fa-exclamation-circle'></i>  A duration has been proposed and approved. <a href='".URL."jobs.confirmation?project=".$ref."&type=review_hours&user_r=".$_SESSION['users']['ref']."&view&user=".$respondsRef.$and."'>Click here</a> to go see the duration";
                } else if ($this->checkMilestone(array("project" => $ref, "user" => $_SESSION['users']['ref'], "user_r" => $respondsRef )) == 0) { ?>
                
                <div class="row with-margin">
                  <div class="col-lg-12">
                  <?php if ($_SESSION['users']['ref'] != $wallet_user) { ?>
                    <form  method="post" name="form" action="<?php echo URL; ?>jobs.confirmation">
                      <div class="input-group input-group-lg">
                        <input type='hidden' name="type" id="type" value="hours" />
                        <input type='hidden' name="project" id="project" value="<?php echo $ref; ?>" />
                        <input type='number' name="hour_count" id="hour_count" class="form-control input-lg" placeholder="Number of proposed housrs" required />
                        <?php if (isset($_REQUEST['viewResponse'])) { ?>
                        <input type='hidden' name="viewResponse" id="viewResponse" value="<?php echo $_REQUEST['viewResponse']; ?>" />
                        <?php } ?>
                        <input type='hidden' name="user_r_id" id="user_r_id_2" value="<?php echo $respondsRef; ?>" />
                        <input type='hidden' name="user_id" id="user_id_2" value="<?php echo $_SESSION['users']['ref']; ?>" />
                        <input type="submit"  value="Propose Duration in Hours" id="setup_hours" class="btn btn-primary btn-lg" name="setup_hours"/>
                      </div><!-- /input-group -->
                    </form>
                    <?php } else { ?>
                      <p><i class='fas fa-exclamation-circle'></i>  You can not propose a duration in hours for this ad since you are the one requesting the service. once a duration has been proposed, you will be able to review and approve the proposal here.</p>
                    <?php } ?>
                  </div><!-- /.col-lg-6 -->
                </div><!-- /.row -->
                <?php } else {                    
                  if ($projects_data->findMe(array("project" => $ref, "user" => $_SESSION['users']['ref'] )) == 0) {
                    echo "<i class='fas fa-exclamation-circle'></i>  You have a duration proposal to approve. <a href='".URL."jobs.confirmation?project=".$ref."&type=review_hours&user_r=".$_SESSION['users']['ref']."&user=".$respondsRef.$and."'>Click here</a> to go see this request";
                  } else {
                    echo "<i class='fas fa-exclamation-circle'></i>  Your sent duration proposal is waiting for approval. <a href='".URL."jobs.confirmation?project=".$ref."&type=review_hours&user_r=".$_SESSION['users']['ref']."&user=".$respondsRef.$and."'>Click here</a> to go see this request";
                  }
                } ?>

              </td>
            </tr>
            <?php } } ?>
            <!-- if user is owner of ad -->
            <?php if ($_SESSION['users']['ref'] == $data['user_id']) { ?>
              <!-- featured advert -->
              <?php if ($data['is_featured'] == 0) { ?>
                <tr>
                  <th scope="row">Feature Advert</th>
                  <td>
                    <!-- if payment card is present -->
                    <?php if (count($list) > 0) {?>
                      <div class="row with-margin">
                        <div class="col-lg-12">
                          <form  method="post" name="form" action="<?php echo URL; ?>jobs.confirmation">
                            <div class="input-group input-group-lg">
                              <input type='hidden' name="type" id="type" value="featured_ad" />
                              <input type='hidden' name="project" id="project" value="<?php echo $ref; ?>" />
                              <input type='number' name="featured" id="featured" class="form-control input-lg" placeholder="Number of days to run advert as featured" required />
                              <select name="card" id="card" class="form-control input-lg" required>
                                <?php for ($i = 0; $i < count($list); $i++) { ?>
                                <option value="<?php echo $list[$i]['ref']; ?>"<?php if ($list[$i]['is_default'] == 1) {?> selected<?php } ?>>**** **** **** <?php echo $list[$i]['pan']; if ($list[$i]['is_default'] == 1) { echo " [Default]"; } ?></option>
                                <?php } ?>
                              </select>
                              <input type="submit"  value="Pay" id="pay_featured" class="btn btn-primary btn-lg" name="pay_featured"/>
                            </div><!-- /input-group -->
                          </form>
                        </div><!-- /.col-lg-6 -->
                      </div><!-- /.row -->
                    <?php } else { ?>
                      <!-- if payment card is not present -->
                      <i class='fas fa-exclamation-circle'></i>  You must add a payment card first. <a href="<?php echo URL."paymentCards"; ?>">Click here</a> to add one now
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
              <!-- end featured ad  -->
            <?php } ?>
            <!-- end if user is owner of ad. -->
            <!-- if advert is ready for approval -->
            <?php if (($approve == true) && ($data['status'] == "ACTIVE")) { ?>
              <!-- if user is the one to pay -->
              <?php if ($wallet_user == $_SESSION['users']['ref']) { ?>
                <tr>
                  <th scope="row">Wallet Balance</th>
                  <td><?php echo  $country->getCountryData( $data['country'] )." ".number_format($wallet_balance, 2); ?></td>
                </tr>
                <tr>
                  <th scope="row">Amount Needed for this Ad.</th>
                  <td><?php echo  $country->getCountryData( $data['country'] )." ".number_format($fee, 2); ?></td>
                </tr>
                <!-- if enough wallet balance -->
                <?php
                if ($wallet_balance >= $fee) { ?>
                  <tr>
                    <th scope="row">Balance after for this Ad.</th>
                    <td><?php echo  $country->getCountryData( $data['country'] )." ".number_format($wallet_balance-$fee, 2); ?></td>
                  </tr>
                  <!--- if user is the one paying -->
                  <?php if (( ($_SESSION['users']['ref'] == $wallet_user))) {
                    //check gov ID verification
                    if ($verify_me+$verify_other >= 4) { ?>
                    <tr>
                      <th scope="row">Manage Advert</th>
                      <td><button class="btn btn-danger" id="approve_ad">Approve</button><br><small>Click twice. By clicking twice, you inidcate that you have agreed to the terms and conditions as outlined in our terms and conditions document</small></td>
                    </tr>
                  <?php }
                  } ?>
                  <!-- end if user is the one paying -->
                <?php } else { ?>
                  <tr>
                    <th scope="row">Balance after for this Ad.</th>
                    <td class="alert alert-danger"><i class='fas fa-exclamation-circle'></i>   You do not have enough money in your wallet. You must add a minimum of <?php echo  $country->getCountryData( $data['country'] )." ".number_format(abs($wallet_balance-$fee), 2); ?> to your wallet balance to be able to authorize this ad to this user<br>
                      <a href="<?php echo URL."wallet?bal=".abs(ceil($wallet_balance-$fee)); ?>">Click here to load your wallet now</a>
                    </td>
                  </tr>
                <?php } ?>
                <!-- end enough wallet balamce -->
              <?php } else { ?>
                <?php if ($wallet_balance < $fee) { ?>
                  <tr>
                    <td colspan="2" class="alert alert-danger"><i class='fas fa-exclamation-circle'></i>   This ad. can not be approved until <?php echo $users->listOnValue($respondsRef, "screen_name"); ?>'s wallet is funded. We have notified <?php echo $users->listOnValue($respondsRef, "screen_name"); ?>. </a>
                    </td>
                  </tr>
                <?php } else { ?>
                  <!--- if user is not the one paying -->
                  <?php if (( ($_SESSION['users']['ref'] != $wallet_user))) {
                    //check gov ID verification
                    if ($verify_me+$verify_other >= 4) { ?>
                    <tr>
                      <th scope="row" colspan="2"><i class='fas fa-exclamation-circle'></i> Waiting for <?php echo $users->listOnValue($respondsRef, "screen_name"); ?> to approve this Job</th>
                    </tr>
                  <?php }
                  } ?>
                  <!-- end if user is not the one paying -->
                <?php } ?>
              <?php } ?>
              <!-- end if user is the one paying -->
            <?php } ?>
            <?php if ($verify_me+$verify_other < 4) {
              if ($verify_me < 2) {
                if ($verify_me == 0) {
                  $warning = "You must upload a valid government ID. <a href='".URL."/edit/IDs'>Click here</a> to upload now";
                } else if ($verify_me == 1) {
                  $warning = "We are verifying your uploaded ID";
                } ?>
              
                <tr>
                  <td colspan="2" class="alert alert-warning"><i class='fas fa-exclamation-circle'></i>   This ad. can not be approved, <?php echo $warning; ?>. </a>
                  </td>
                </tr>
              <?php } else { ?>
                <tr>
                  <td colspan="2" class="alert alert-warning"><i class='fas fa-exclamation-circle'></i>   This ad. can not be approved because <?php echo $users->listOnValue($respondsRef, "screen_name"); ?>'s account has not been verified. If you want to continue with this user, their account must be verified. </a>
                  </td>
                </tr>

              <?php } ?>
            <?php } ?>
            <!-- end if user is ready to pay -->
          <?php } ?>
          <!-- end of check if conversation has started -->
        </table>
      </div>
      <?php if ($_SESSION['users']['ref'] == $data['user_id']) { ?>
      <h2>Response to Advert</h2>
      <div class="row">
        <?php if (count($getResponse) > 0) {
        for ($j = 0; $j < count($getResponse); $j++) {
          $badge_c = $messages->getNewMessage($ref, $getResponse[$j]['user_id']) ?>
        <div class="col-1">
        <a href="?viewResponse=<?php echo $getResponse[$j]['user_id']; ?>#messages"><?php $users->getProfileImage($getResponse[$j]['user_id'], "25"); ?><br><?php echo $users->listOnValue($getResponse[$j]['user_id'], "screen_name"); ?><?php if ($badge_c > 0) { ?><span class="badge badge-danger"><?php echo $badge_c; ?></span><?php } ?></a>
        </div>
        <?php }
        } else { ?>
          <p>No response to this advert yet</p>
        <?php } ?>
      </div>
        <?php } ?>
        <?php if (($_SESSION['users']['ref'] != $data['user_id']) ||  (($_SESSION['users']['ref'] == $data['user_id']) && (isset($_REQUEST['viewResponse'])))) { ?>
        <h2><a name="messages"></a>Messages with <?php echo $users->listOnValue($respondsRef, "screen_name"); ?></h2>
        <div class="row">
            <ol id="update" >
              <?php for ($i = 0; $i < count($initialComment); $i++) { ?>
              <li class="media" id='<?php echo $initialComment[$i]['ref']; ?>'>
                <?php $users->getProfileImage($initialComment[$i]['user_id'], "mr-3", "50"); ?>
                <div class="media-body">
                    <small class="pull-right time"><i class="fa fa-clock-o"></i> <?php echo $this->get_time_stamp(strtotime($initialComment[$i]['create_time'])); ?></small>
                    <p class="mt-0">
                      <?php if ($initialComment[$i]['m_type'] == "negotiate_charges" ) {
                      $m_type_data = explode("_", $initialComment[$i]['m_type_data'] ) ?>
                    <i class="fa fa-handshake" aria-hidden="true"></i><br><?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>You have a <?php } ?>new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['country'] )." " .$m_type_data[0]; ?></strong>
                    <?php if ($initialComment[$i]['user_id'] != $_SESSION['users']['ref']) { ?>
                    <br><br><a href="<?php echo $this->seo($ref, "view"); ?>?n_answer=y&neg_id=<?php echo $m_type_data[1]; ?>&msg_id=<?php echo $initialComment[$i]['ref'].$and; ?>"><i class="fa fa-thumbs-up" aria-hidden="true"></i>   Accept</a> | <a href="<?php echo $this->seo($ref, "view"); ?>?n_answer=n&neg_id=<?php echo $m_type_data[1]; ?>&msg_id=<?php echo $initialComment[$i]['ref'].$and; ?>"><i class="fa fa-thumbs-down" aria-hidden="true"></i>   Reject</a>
                    <?php } ?>
                    <?php } else if ($initialComment[$i]['m_type'] == "system" ) {
                      echo "<i class='fa fa-exclamation' aria-hidden='true'></i>     ".$initialComment[$i]['message'];
                      } else {
                      echo $initialComment[$i]['message'];} ?></p>
                </div>
              </li>
              <?php } ?>
            </ol>
            <div id="flash"></div>
        </div>
        <?php if ($data['status'] != "COMPLETED") { ?>
          <div class="row with-margin">
            <div class="col-lg-12">
              <form  method="post" name="form" action="">
                <div class="input-group input-group-lg">
                  <?php $users->getProfileImage($_SESSION['users']['ref'], "50"); ?> 
                  <input type='text' name="content" id="content" class="form-control input-lg" placeholder="Enter your message here..." />
                  <input type='hidden' name="user_r_id" id="user_r_id" value="<?php echo $respondsRef; ?>" />
                  <input type='hidden' name="user_id" id="user_id" value="<?php echo $_SESSION['users']['ref']; ?>" />
                  <input type='hidden' name="project_id" id="project_id" value="<?php echo $ref; ?>" />
                  <input type='hidden' name="m_type" id="m_type" value="text" />
                  <span class="input-group-btn">

                  <input type="button"  value="Post"  id="post" class="btn btn-primary btn-lg" name="post"/>
                  </span>
                </div><!-- /input-group -->
              </form>
            </div><!-- /.col-lg-6 -->
          </div><!-- /.row -->
        <?php } ?>
      <?php } ?>
      <?php } else { ?>
          <p>You must <a href="<?php echo URL."login?redirect=".$this->seo($ref, "view"); ?>" title="Login">login</a> or <a href="<?php echo URL."login?join?redirect=".$this->seo($ref, "view"); ?> title="Register">register</a> to respond to this posting</p>
      <?php } ?>

      <script type="text/javascript">
        $('#negotiated_fee').on('keyup keypress blur change', function() {
          var current = <?php echo $data['default_fee']; ?>;
          if ($(this).val() != current) {
            $("#negotiate").prop('disabled', false);
          } else {
            $("#negotiate").prop('disabled', true);
          }
        });

        var ref='<?php echo $ref;?>';
        var user_id='<?php echo $_SESSION['users']['ref'];?>';
        var user_r_id='<?php echo $respondsRef;?>';
        var auto_refresh = setInterval(function () {
          var b=$("ol#update li:last").attr("id");
          $.getJSON("<?php echo URL; ?>includes/views/scripts/chat_json?project_id="+ref+"&user_id="+user_id+"&user_r_id="+user_r_id,function(data) {
            $.each(data.posts, function(i,data) {
              if(b != data.id) {
                var dataString = 'id='+ data.user;
                $.ajax({
                    type: "POST",
                    url: "<?php echo URL; ?>includes/views/scripts/draw",
                    data: dataString,
                    cache: false,
                    success: function(html){
                      if (data.m_type == "system") {
                        var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='pull-right time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'><i class='fa fa-exclamation' aria-hidden='true'></i>     "+data.msg+"</p></div></li>";
                      } else if (data.m_type == "negotiate_charges") {
                        var msg = '<i class="fa fa-handshake-o" aria-hidden="true"></i><br>You have a new fee negotiation request.<br><br>New Fee: <strong><?php echo $country->getCountryData( $data['country'] ); ?>'+data.data_1+'</strong><br><br><a href="'+data.url+'?n_answer=y&neg_id='+data.data_2+'&msg_id='+data.id+'<?php echo $and; ?>"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>   Accept</a> | <a href="'+data.url+'?n_answer=n&neg_id='+data.data_2+'&msg_id='+data.id+'<?php echo $and; ?>"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i>   Reject</a>'

                        var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='pull-right time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+msg+"</p></div></li>";
                      } else {
                        var div_data = "<li class='media' id='"+data.id+"'>"+html+"<div class='media-body'><small class='pull-right time'><i class='fa fa-clock-o'></i>"+data.time+"</small><p class='mt-0'>"+data.msg+"</p></div></li>";
                      }
                      $(div_data).appendTo("ol#update");
                    }
                  });
              }
            });
          });
          $('.main_img').initial({
            width:50,
            charCount: 1,
            fontSize: 40,
            fontWeight: 400,
            height:50
          });

        }, 2000);	

        $(document).ready(function() {
          $('#post').click(function() {
            post();
          });
          
          $('#approve_ad').click(function() {
            $(this).confirm({
              text: "Once you confirm this action, this ad becomes unavailable and will be assigned to this user. Are you sure you want to continue?",
              confirm: function(button) {
                  window.location='<?php echo URL."__approveApp?id=".$data['ref']."&responder=".$responderRef; ?>';
              },
            });
          });
          $('.interested').click(function() {
            $(".interested").hide();
            $('#content').focus();

            setTimeout(function(){
              location.reload();}, 2000);
            });

          $('#content').focus(function() {
            var user_id = $("#user_id").val();
            var project_id = $("#project_id").val();
            var dataString = 'user_id='+ user_id+"&project_id="+project_id;
            $.ajax({
              type: "POST",
              url: "<?php echo URL; ?>includes/views/scripts/markRead",
              data: dataString,
              cache: false
            });
          });
        });

        function post() {
          var boxval = $("#content").val();
          var user_r_id = $("#user_r_id").val();
          var project_id = $("#project_id").val();
          var m_type = $("#m_type").val();
          var user = '<?php echo $_SESSION['users']['ref'];?>';
          var dataString = 'user_id='+ user + '&user_r_id=' + user_r_id + '&project_id=' + project_id + '&m_type=' + m_type + '&content=' + boxval;

          if(boxval.length > 0) {
            $("#flash").show();
            $("#flash").fadeIn(400).html('<img src="<?php echo URL."img/loading.gif"; ?>" align="absmiddle">&nbsp;<span class="loading">Loading Update...</span>');
            $.ajax({
              type: "POST",
              url: "<?php echo URL; ?>includes/views/scripts/chatajax",
              data: dataString,
              cache: false,
              success: function(html){
                $("ol#update").append(html);

                $('#content').val('');
                $('#content').focus();
                $("#flash").hide();
              }

            });
          }
          return false;
        }

        $(document).on('keypress', 'form input[type="text"]', function(e) {
          if(e.which == 13) {
            e.preventDefault();
            post();
            return false;
          }
        });
      </script>
    <?php }

    public function headMeta($ref) {
      $data = $this->listOne($ref, "ref"); ?>
      <title><?php echo $data['project_name']; ?></title>
      <meta name="Description" content="<?php echo $this->truncate($data['project_dec'], "140"); ?>">
      <meta name="Keywords" content="<?php echo $data['tag']; ?>">
      <link rel="canonical" href="<?php echo $this->seo($ref, "view"); ?>" />
      <meta property="og:title" content="<?php echo $data['project_name']; ?>" />
      <meta property="og:url" content="<?php echo $this->seo($ref, "view"); ?>" />
    <?php }
  }
?>