<?php
    $redirect = "allCategories";
	include_once("includes/functions.php");
	if (isset($_REQUEST['page'])) {
		$page = $_REQUEST['page'];
	} else {
		$page = 0;
	}
	
	$limit = 12;
	$start = $page*$limit;

	$loc = $country->getLoc($_SESSION['location']['code']);


	if ($_SESSION['users']['user_type'] == 1) {
		$data = $request->getActiveRequest($_SESSION['users']['ref'], $_SESSION['location']['longitude'], $_SESSION['location']['latitude'], $_SESSION['location']['country'], $start, $limit);

		$count = $data['listCount'];
		$list = $data['list'];
	} else {
		$data = $category->categoryListPages($loc['ref'], $start, $limit);
	
		$count = $data['count'];
		$list = $data['data'];
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
    <title>Moba - Find the best artisans</title>
	</head>

  <body>
	<section>

    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		
		<div class="moba-sban">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 py-5">
                    <?php if ($_SESSION['users']['user_type'] == 1) { ?>
						<h3>All Available Jobs </h3>
						<p class="text-white">Find all available jobs in your category.</p>
					<?php } else { ?>
						<h3>All Job Categories </h3>
						<p class="text-white">Find and hire the best professional artisans today.</p>
					<?php } ?>
                    </div>
                    <div class="col-lg-6"></div>
                </div>
            </div>
		</div>
		
	</section>
	<section>
		<div class="container my-5">
            	
			<div class="row py-5">
				<?php if ($_SESSION['users']['user_type'] == 1) { ?>
					<?php for ($i = 0; $i < count($list); $i++) { ?>
						<div class="col-lg-3">			
							<div class="card h-50">
								<a href="<?php echo URL."profile/request/".$list[$i]['user_id']."/". $users->listOnValue($list[$i]['user_id'], "screen_name")."/".$list[$i]['ref']."/message"; ?>">
								<?php $users->getProfileImage($list[$i]['user_id'], "card-img-top", "50", false); ?></a>
								<br>

								<div class="col-12">
									<strong><?php echo $users->listOnValue($list[$i]['user_id'], "screen_name"); ?></strong><br>
									<?php echo $rating->drawRate(intval($rating->getRate($list[$i]['user_id']))); ?><br>
									Service Requested<br><strong><?php echo $category->getSingle($list[$i]['category_id']); ?></strong><br>
									<a href="<?php echo URL."profile/request/".$list[$i]['user_id']."/". $users->listOnValue($list[$i]['user_id'], "screen_name")."/".$list[$i]['ref']."/message"; ?>" title="Message" class="btn purple-bn pd"><i class="fas fa-comments"></i> Message</a>
								</div>
							</div>				
						</div>
					<?php } ?>
				<?php } else { ?>
					<?php for ($i = 0; $i < count($list); $i++) { ?>
						<div class="col-lg-3">			
							<div class="card h-100">
								<a href="<?php echo $common->seo($list[$i]['ref'], "view"); ?>"><img class="card-img-top" src="<?php echo $category->getIcon($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['category_title']; ?>"></a>
								<br>
								<strong><?php echo $list[$i]['category_title']; ?></strong>
							</div>				
						</div>
					<?php } ?>
				<?php } ?>
			</div>			
			
            <?php $common->pagination($page, $count); ?>
			
		</div>
	</section>
	
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>

  </body>
</html>