<?php
    class adminRating extends rating_question {
        public function navigationBar($redirect) { ?>
            <p><i class="fa fa-caret-right mr-3"></i><a href="<?php echo URL.$redirect; ?>"><b>List All</b></p>
            <div class="moba-line my-2"></div>
            <p><i class="fa fa-caret-right mr-3"></i> <a href="<?php echo URL.$redirect."/create"; ?>"><b>Add New</b></a></p>	
        <?php }

        function pageContent($redirect, $view="list", $edit=0) {
            if ($view == "list") {
                $this->listAll($redirect);
            } else {
                $this->createNew($redirect, $edit);
            }
        }

        function createNew($redirect, $edit=0) {
            if ($edit > 0) {
              $data = $this->getOne("country", $edit, "ref");
              $tag = "Edit ".$data['name'];
              $tag2 = "Save Changes";
            } else {
              $data = false;
              $tag = "Create Rating Category";
              $tag2 = "Create Rating Category";
            }
            ?>
            <main class="col-12" role="main">
            <form method="post" action="" enctype="multipart/form-data">
            <h2><?php echo $tag; ?></h2>
            <div class="form-group">
                <label for="question">Question</label>
                <input type="text" class="form-control" name="question" id="question" placeholder="Enter the question to rate a user on" required value="<?php echo $data['question']; ?>">
            </div>
            <div class="form-group">
              <label for="question_type">Question Focus</label>
              <select class="form-control" id="question_type" name="question_type" required>
                <option value="clients"<?php if ($data['question_type'] == "clients") { ?> selected<?php } ?>>Clients (Job Owner)</option>
                <option value="vendors"<?php if ($data['question_type'] == "vendors") { ?> selected<?php } ?>>Vendors (Workers)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <select class="form-control" id="status" name="status" required>
                <option value="ACTIVE"<?php if ($data['status'] == "ACTIVE") { ?> selected<?php } ?>>Active</option>
                <option value="INACTIVE"<?php if ($data['status'] == "INACTIVE") { ?> selected<?php } ?>>In-Active</option>
              </select>
            </div>
            <input type="hidden" name="ref" value="<?php echo $edit; ?>">
            <button type="submit" name="submitRating" class="btn purple-bn1"><?php echo $tag2; ?></button>
            <?php if ($edit > 0) { ?>
            <button type="button" class="btn purple-bn1" onClick="location='<?php echo URL.$redirect; ?>'" >Cancel</button>
            <?php } ?>
            </form>
          </main>
          </div>
        <?php }


        function listAll($redirect) {
            global $options;

            if (isset($_REQUEST['page'])) {
              $page = $_REQUEST['page'];
            } else {
              $page = 0;
            }
            
            $limit = $options->get("result_per_page");
            $start = $page*$limit;
            
            $list = $this->getList($start, $limit);
            $listCount = $this->getList(false, false, "count"); ?>
            <h2>List All Rating Questions</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Question</th>
                        <th scope="col">Type</th>
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
                            <td><?php echo $list[$i]['question']; ?></td>
                            <td><?php echo $list[$i]['question_type']; ?></td>
                            <td><?php echo $list[$i]['status']; ?></td>
                            <td><?php echo $list[$i]['create_time']; ?></td>
                            <td><?php echo $list[$i]['modify_time']; ?></td>
                            <td><a href="<?php echo URL.$redirect."/create?edit=".$list[$i]['ref']; ?>">Edit</a> | <a href="<?php echo URL.$redirect."?statusChange=".$list[$i]['ref']; ?>" onClick="return confirm('this action will <?php echo strtolower($statusTag); ?> this question. are you sure you want to continue ?')"><?php echo strtolower($statusTag); ?></a><?php if (count($list) > 1) { ?> | <a href="<?php echo URL.$redirect."?delete=".$list[$i]['ref']; ?>" onClick="return confirm('this action will remove this question. are you sure you want to continue ?')">Delete</a><?php } ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php $this->pagination($page, $listCount);
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

        function removeRating($id) {
          $add = $this->remove($id);

          if ($add) {
              return true;
          } else {
              return false;
          }
        }
    }
?>