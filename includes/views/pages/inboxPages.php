<?php
    class inboxPages extends inbox {
        public function pageContent($view, $id=0, $user=0) {
            if ($view == "compose") {
                $this->compose($id, $user);
            } else if ($view == "sent") {
                $this->sent();
            } else if ($view == "read") {
                $this->read($id);
            } else {
                $this->inbox();
            }
        }

        public function navigationBar($redirect) {
            $ref = $_SESSION['users']['ref'];
            $counter = $this->counters($ref)['new']; ?>
            <a href="<?php echo URL.$redirect."/compose"; ?>">Compose</a> | <a href="<?php echo URL.$redirect; ?>">Inbox (<?php echo $counter; ?>)</a> | <a href="<?php echo URL.$redirect."/sent"; ?>">Sent</a> | <a href="<?php echo URL."notifications"; ?>">Notifications</a> | <a href="<?php echo URL."notifications"."/saved"; ?>">Saved Ads</a>
        <?php }
        
        private function read($id) {
            $ref = $_SESSION['users']['ref'];
            if ($id < 1) {
                $this->inbox();
            } else {
                $data = $this->listOne($id);
                $previous_id = $data['previous_id']; ?>
                <h2><?php echo $data['subject']; ?></h2>
                <?php if ($data['to_id'] == $ref) {
                    $this->markReadOne($id);
                    $to_list = $data['from_id']; ?>
                <div class="form-group">
                    <label>From</label>
                    <p><strong><?php echo $this->toList($data['from_id']); ?></strong></p>
                </div>
                <?php } else if ($data['from_id'] == $ref) {
                    $to_list = $data['to_list']; ?>
                <div class="form-group">
                    <label>To</label>
                    <p><strong><?php echo $this->toList($data['to_list']); ?></strong></p>
                </div>
                <?php }?>
                <div class="form-group">
                    <p><?php echo $data['message']; ?><br>
                    <small>Recieved: <?php echo $data['create_time']; ?></small></p>
                </div>
                <?php if ($previous_id > 0) { ?>
                <h5>Other Conversations in This Message</h5>
                <?php } ?>
                <?php while ($previous_id > 0) {
                    $p_data = $this->listOne($previous_id);
                     ?>
                    <div class="form-group">
                        <p><?php echo $p_data['message']; ?><br>
                        <small>Recieved: <?php echo $p_data['create_time']; ?></small></p>
                    </div>
                <?php $previous_id = $p_data['previous_id'];
                } ?>
                <form method="post" action="">
                <button type="submit" value="<?php echo $id; ?>" class="btn purple-bn1" name="forward" id="forward"><i class="fas fa-forward"></i>&nbsp;Forward</button>
                </form>
                <?php if ($data['to_id'] == $ref) { ?>  
                <form method="post" action="">
                <h2>Reply</h2>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" name="subject" id="subject" placeholder="Enter the subject for the mail" readonly value="RE: <?php echo trim($data['subject'], "RE:"); ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea class="form-control" name="message" id="message" placeholder="Message to rrecipient(s)" required></textarea>
                </div>
                <input type="hidden" name="from_id" value="<?php echo $ref; ?>">
                <input type="hidden" name="to_list" value="<?php echo $to_list; ?>">
                <input type="hidden" name="previous_id" value="<?php echo $id; ?>">
                <button type="submit" class="btn purple-bn1" name="sendMail" id="sendMail"><i class="fas fa-paper-plane"></i>&nbsp;Send</button>
                </form>
                <?php } ?>
            <?php }
        }
        private function inbox() {
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;

            global $users;
            $ref = $_SESSION['users']['ref'];

            $data = $this->listAllInboxData("inbox", $ref, $start, $limit);
            $list = $data['list'];
            $listCount = $data['listCount']; ?>
            <h2>Inbox</h2>

            <?php if (count($list) > 0) { ?>
            <form method="post" action="">
                <div class="form-row">
                    <button type="Send" class="btn btn-outline-primary" name="inboxAction" value="deleteMail" id="deleteMail"><i class="fas fa-paper-plane"></i>&nbsp;Delete</button>
                    <button type="Send" class="btn btn-outline-primary" name="inboxAction" value="readMail" id="readMail"><i class="fas fa-envelope-open"></i>&nbsp;Mark as Read</button>
                    <button type="Send" class="btn btn-outline-primary" name="inboxAction" value="unreadMail" id="unreadMail"><i class="fas fa-envelope"></i>&nbsp;Mark as Unread</button>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col"><input type="checkbox" id="checkall"></th>
                            <th scope="col">#</th>
                            <th scope="col">Subject</th>
                            <th scope="col">From</th>
                            <th scope="col">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < count($list); $i++) { ?>
                    <tr <?php if ($list[$i]['read'] == 0) { ?>class="table-active"<?php } ?>>
                            <td><input type="checkbox" name="ref[]" value="<?php echo $list[$i]['ref']; ?>" id="delete"></td>
                            <td><?php echo $start+$i+1; ?></td>
                            <th scope="row"><a href="<?php echo URL."/inbox/read?id=".$list[$i]['ref']; ?>"><?php echo $list[$i]['subject']; ?></a></th>
                            <td><?php echo $users->listOnValue($list[$i]['from_id'], "screen_name"); ?></td>
                            <td><?php echo $list[$i]['create_time']; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php $this->pagination($page, $listCount); ?>
            </form>
            <?php } else { ?>
                <p>No messages in Inbox</p>
            <?php } ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#deleteMail, #readMail, #unreadMail').click(function() {
                    checked = $("input[type=checkbox]:checked").length;

                    if(!checked) {
                        alert("You must check at least one checkbox.");
                        return false;
                    }

                    });
                });
                $('#checkall').click(function () {    
                    $('input:checkbox').prop('checked', this.checked);    
                });

            </script>
        <?php }

        private function sent() {
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;

            $ref = $_SESSION['users']['ref'];
            $data = $this->listAllInboxData("sent", $ref, $start, $limit);
            $list = $data['list'];
            $listCount = $data['listCount']; ?>
            <h2>Sent</h2>

            <?php if (count($list) > 0) { ?>
            <form method="post" action="">
                <div class="form-row">
                    <button type="Send" class="btn btn-outline-primary" name="inboxAction" value="deleteMail" id="deleteMail"><i class="fas fa-paper-plane"></i>&nbsp;Delete</button>
                    <button type="Send" class="btn btn-outline-primary" name="inboxAction" value="readMail" id="readMail"><i class="fas fa-envelope-open"></i>&nbsp;Mark as Read</button>
                    <button type="Send" class="btn btn-outline-primary" name="inboxAction" value="unreadMail" id="unreadMail"><i class="fas fa-envelope"></i>&nbsp;Mark as Unread</button>
                </div>
            `    <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col"><input type="checkbox" id="checkall"></th>
                            <th scope="col">#</th>
                            <th scope="col">Subject</th>
                            <th scope="col">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < count($list); $i++) { ?>
                        <tr>
                            <td><input type="checkbox" name="ref[]" value="<?php echo $list[$i]['ref']; ?>" id="delete"></td>
                            <td><?php echo $start+$i+1; ?></td>
                            <th scope="row"><a href="<?php echo URL."/inbox/read?id=".$list[$i]['ref']; ?>"><?php echo $list[$i]['subject']; ?></a></th>
                            <td><?php echo $list[$i]['create_time']; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php $this->pagination($page, $listCount); ?>
            </form>
            <?php } else { ?>`
                <p>No messages in Sent</p>
            <?php } ?>
            
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#deleteMail, #readMail, #unreadMail').click(function() {
                    checked = $("input[type=checkbox]:checked").length;

                    if(!checked) {
                        alert("You must check at least one checkbox.");
                        return false;
                    }

                    });
                });
                $('#checkall').click(function () {    
                    $('input:checkbox').prop('checked', this.checked);    
                });
            </script>
        <?php }

        private function compose($reply=0, $user=0) {
            global $users;
            $ref = $_SESSION['users']['ref'];
            $previous_id = $reply;
            $data = $users->listOne($ref);
            $messageData = $this->listOne($reply);
            
            if ($messageData) {
                $subject = "FW: ".$messageData['subject'];
                $body = "\n\nOriginal Message\nhi";
            }
            ?>
            <link rel="stylesheet" href="<?php echo URL; ?>css\tagcomplete.css">
            <H2>Compose New Message</H2>
            <form method="post" action="">
                <div class="form-group">
                    <label>From</label>
                    <p><strong><?php echo $data['last_name']." ".$data['other_names']; ?></strong></p>
                </div>
                <?php if ($user == 0) { ?>
                <div class="form-group">
                    <label for="to_list">To</label>
                    <input type="text" class="form-control" name="to_list" id="to_list" placeholder="Enter the names of each recipient" required>
                </div>
                <?php } else { ?>
                <div class="form-group">
                    <label for="to_list">To</label>
                    <input type="hidden" name="to_list" value="<?php echo $user; ?>">
                    <p><strong><?php echo $this->toList($user); ?></strong></p>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" name="subject" id="subject" placeholder="Enter the subject for the mail" required value="<?php echo $subject; ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea class="form-control" name="message" id="message" placeholder="Message to rrecipient(s)" required><?php echo $body; ?></textarea>
                </div>
                <input type="hidden" name="from_id" value="<?php echo $ref; ?>">
                <input type="hidden" name="previous_id" value="<?php echo $previous_id; ?>">
                <button type="Send" class="btn purple-bn1" name="sendMail" id="sendMail"><i class="fas fa-paper-plane"></i>&nbsp;Send</button>
            </form>
            <script src="<?php echo URL; ?>js\tagcomplete.js"></script>
            <script>
                $("#to_list").tagComplete({
                    freeInput:false,
                    autocomplete: {
                        ajaxOpts: {
                            url: "<?php echo URL; ?>includes/views/scripts/users",
                            method: 'GET',
                            dataType: 'json',
                            data: {}
                        },
                        params : function(value){
                            return {q: value};
                        },
                    }
                });

                $( document ).ready(function() {
                    $("#to_list").focus();
                });
            </script>
        <?php }

        private function toList($list) {
            global $users;
            $list = explode(",", $list);
            $return = "";
            for ($i = 0; $i < count($list); $i++) {
                $data = $users->listOne($list[$i]);
                $return .= $data['screen_name'].", ";
            }

            return trim($return, ", ");
        }
    }
?>