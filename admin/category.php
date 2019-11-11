<?php
    $redirect = "admin/category";
    include_once("../includes/functions.php");
    include_once("../includes/sessions.php");

    if (isset($_REQUEST['view'])) {
        $view = $_REQUEST['view'];
    } else {
        $view = "list";
    }
    if (isset($_REQUEST['edit'])) {
        $edit = $_REQUEST['edit'];
    } else {
        $edit = 0;
    }

    if (isset($_POST['submitCat'])) {
        unset($_POST['submitCat']);
        
        if (isset($_FILES['img'])) {
            $file = $_FILES;
        } else {
            $file = false;
        }
        $add = $adminCategory->postMew($_POST, $file);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Category List Updated"));
        } else {
            header("location: ?error=".urlencode("Category not created"));
        }
    }
    if (isset($_REQUEST['updateImg'])) {
        $add = $adminCategory->removeImage($_REQUEST['edit']);

        if ($add) {
            header("location: ".URL.$redirect."/create?edit=".$_REQUEST['edit']."&done=".urlencode("Category icon removed"));
        } else {
            header("location: ".URL.$redirect."/create?edit=".$_REQUEST['edit']."&error=".urlencode("Category icon not removed"));
        }
    } else if (isset($_REQUEST['delete'])) {
        $add = $adminCategory->removeCate($_REQUEST['delete']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Category removed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Category not removed"));
        }
    } else if (isset($_REQUEST['statusChange'])) {
        $add = $adminCategory->toggleStatus($_REQUEST['statusChange']);

        if ($add) {
            header("location: ".URL.$redirect."?done=".urlencode("Category Status changed"));
        } else {
            header("location: ".URL.$redirect."?error=".urlencode("Category Status not changed"));
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <?php $pageHeader->headerFiles(); ?>
    <title>Administrator | Categories</title>
	</head>

  <body>
	<section>
    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		<div class="moba-sban1">
		<div class="container">
			<div class="row">
			
				<div class="col-lg-6 py-5">
						<h6><a href="<?php echo URL; ?>">Moba</a>  /  Administrator / Categories</h6>
				</div>
				<div class="col-lg-6"></div>
				
			</div>
		</div>
		</div>
		
	</section>
	<section class="moba-details">
		<div class="container my-5">
            <div class="row py-5">
                <div class="col-lg-3">
                    <?php $adminCategory->navigationBar($redirect); ?>
                </div>
                <div class="col-lg-9">
                    <?php $adminCategory->pageContent($redirect, $view, $edit); ?>
                </div>
            </div>
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>

</html>