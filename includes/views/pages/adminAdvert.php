<?php
    class adminAdvert extends projects {
        public function navigationBar($redirect) { ?>
          <a href="<?php echo URL.$redirect; ?>?view=all">List All</a> | <a href="<?php echo URL.$redirect."?view=active"; ?>">Posted Ads</a> | <a href="<?php echo URL.$redirect."?view=on-going"; ?>">On-Going Ads</a> | <a href="<?php echo URL.$redirect."?view=past"; ?>">Past Ads</a> | <a href="<?php echo URL.$redirect."?view=draft"; ?>">Drafts</a>
        <?php }

        public function pageContent($view, $redirect) {
            $this->listAll($view, $redirect);
        }

        private function listAll($view, $redirect) {
            global $media;
            global $users;
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
             if ($view == "on-going") {
              $list = $this->getSortedList("ON-GOING", "status", false, false, false, false,"modify_time", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedList("ON-GOING", "status", false, false, false, false,"modify_time", "DESC", "AND", false, false, "count");
              $tag = "All On-going Jobs";
            } else if ($view == "past") {
              $list = $this->getSortedList("COMPLETED", "status", false, false, false, false,"modify_time", "DESC", "AND", $start, $limit);
              $listCount = $this->getSortedList("COMPLETED", "status", false, false, false, false,"modify_time", "DESC", "AND", false, false, "count");
              $tag = "All Previous Ads";
            } else if ($view == "draft") {
              $list = $this->getSortedList("NEW", "status", false, false, false, false,"ref", "ASC", "AND", $start, $limit);
              $listCount = $this->getSortedList("NEW", "status", false, false, false, false,"ref", "ASC", "AND", false, false, "count");
              $tag = "All Ads in Draft";
            } else if ($view == "all") {
              $list = $this->getList($start, $limit);
              $listCount = $this->getList(false, false, "project_name", "ASC", false, "count");
              $tag = "All Ads";
            } else {
              $list = $this->getSortedList("ACTIVE", "status", false, false, false, false,"ref", "ASC", "AND", $start, $limit);
              $listCount = $this->getSortedList("ACTIVE", "status", false, false, false, false,"ref", "ASC", "AND", false, false, "count");
              $tag = "All Active Ads";
            } ?>
            <h2><?php echo $tag; ?></h2>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">&nbsp;</th>
                <th scope="col">Owner</th>
                <th scope="col">Name</th>
                <th scope="col">Code</th>
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
                <td><img src="<?php echo $media->getCover($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['project_name']; ?>" class="img-thumbnail" style="width:auto;
            height:75px;" height="50px"></td>
                <td><?php echo $users->getProfileImage($list[$i]['user_id']); ?></td>
                <td><?php echo $list[$i]['project_name']; ?></td>
                <td><?php echo $list[$i]['project_code']; ?></td>
                <td><?php echo $list[$i]['status']; ?></td>
                <td><?php echo $list[$i]['create_time']; ?></td>
                <td><?php echo $list[$i]['modify_time']; ?></td>
                <td><?php echo $this->getLink($list[$i]['ref'], $list[$i]['status'], $redirect); ?></td>
              </tr>
                <?php } ?>
            </tbody>
          </table>
          <?php $this->pagination($page, $listCount);
        }
        
        private function getLink($ref, $status, $redirect) {
            if ($status == "NEW") {
                return '<a href="'.$this->seo($ref, "view").'">View</a>| <a href="'.URL.$redirect.'?remove='.$ref.'" onClick="return confirm(\'this action will remove this ad. are you sure you want to continue ?\')"">Delete</a>';
            } else if ($status == "ACTIVE") {
                return '<a href="'.$this->seo($ref, "view").'">View</a> | Deactivate | <a href="'.URL.$redirect.'?remove='.$ref.'" onClick="return confirm(\'this action will remove this ad. are you sure you want to continue ?\')"">Delete</a>';
            }
        }
    }
?>