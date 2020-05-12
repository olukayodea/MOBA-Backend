<?php
    class adminAdvert extends request {
        public function navigationBar($redirect) { ?>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect; ?>/all"><b>List All</b></a></p>
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/open"; ?>"><b>Open Request</b></a></p>
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/running"; ?>"><b>Active Request</b></a></p>
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/past"; ?>"><b>Past Job Request</b></a></p>
        <?php }

        public function pageContent($view, $redirect) {
            $this->showAll($view, $redirect);
        }
  
        public function showAll( $view, $redirect ) {
            global $request;
            global $options;
            global $country;
            global $category;
            global $users;
 
            if (isset($_REQUEST['page'])) {
                $page = $_REQUEST['page'];
            } else {
                $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            $data = $request->listAllAdminData($view, $start, $limit);

            $list = $data['list'];
            $listCount = $data['listCount'];
            $tag = $data['tag']; ?>

            <h2><?php echo $tag; ?></h2>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Service</th>
                    <th scope="col">Fee</th>
                    <th scope="col">User</th>
                    <?php if ($view != "open") { ?>
                    <th scope="col">Service Provider</th>
                    <?php } ?>
                    <?php if ($view == "running") { ?>
                    <th scope="col">Start Time</th>
                    <?php } else { ?>
                    <th scope="col">Request Time</th>
                    <?php } ?>
                    <?php if ($view == "past") { ?>
                    <th scope="col">End Time</th>
                    <?php } ?>
                    <th scope="col">Location</th>
                    <th scope="col">Created</th>
                    <th scope="col">Last Modified</th>
                    <th scope="col">&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < count($list); $i++) { ?>
                        <tr>
                            <th scope="row"><?php echo $start+$i+1; ?></th>
                            <td><?php echo $category->getSingle( $list[$i]['category_id'] ); ?></td>
                            <td><?php echo $country->getSingle( $list[$i]['region'], "currency_symbol").number_format($list[$i]['fee'], 2); ?></td>
                            <td><?php echo $users->listOnValue($list[$i]['user_id'], "screen_name"); ?></td>
                            <?php if ($view != "open") { ?>
                            <td><?php echo $users->listOnValue($list[$i]['client_id'], "screen_name"); ?></td>
                            <?php } ?>
                            <?php if ($view == "running") { ?>
                            <td><?php echo date(' j-m-Y h:i A', $list[$i]['start_date']); ?></td>
                            <?php } else { ?>
                            <td><?php echo date(' j-m-Y h:i A', $list[$i]['time']); ?></td>
                            <?php } ?>
                            <?php if ($view == "past") { ?>
                            <td><?php echo date(' j-m-Y h:i A', $list[$i]['end_date']); ?></td>
                            <?php } ?>
                            <td><a href="<?php echo "http://maps.google.com/maps?saddr=".$list[$i]['latitude'].",".$list[$i]['longitude']; ?>" target="_blank">Open in Maps</a></td>
                            <td><?php echo $this->get_time_stamp(strtotime($list[$i]['create_time'])); ?></td>
                            <td><?php echo $this->get_time_stamp(strtotime($list[$i]['modify_time'])); ?></td>
                            <th scope="col"><?php echo $this->urlLink($list[$i]['ref'], $list[$i]['status'], $view, $redirect, $$list[$i]['review_status']); ?></th>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php $this->pagination($page, $listCount);
        }

        private function urlLink($id, $status, $review_status=false) {
            if ($status == "ACTIVE") {
                return "<a href='".URL."requestDetails?admin&id=".$id."' title='Review'><i class='fas fa-eye'></i></a>". ($review_status == 1 ? '<i class="fas fa-exclamation-triangle" style="color:#ffa500"></i>' : '');
            } else if ($status == "COMPLETED") {
                return "<a href='".URL."requestDetails?admin&id=".$id."' title='Review'><i class='fas fa-eye'></i></a>". ($review_status == 1 ? '<i class="fas fa-exclamation-triangle" style="color:#ffa500"></i>' : '');
            }
        }
    }
?>