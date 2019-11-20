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

	$data = $category->categoryListPages($loc['ref'], $start, $limit);

	$count = $data['count'];
	$list = $data['data'];
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Moba - Find the best artisans</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
	</head>

  <body>
	<section>

    <?php $pageHeader->loginStrip(); ?>
    <?php $pageHeader->navigation(); ?>
		
		
		<div class="moba-sban">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 py-5">
                    
                            <h3>All Job Categories </h3>
                            <p class="text-white">Find and hire the best professional artisans today.</p>
                    </div>
                    <div class="col-lg-6"></div>
                </div>
            </div>
		</div>
		
	</section>
	
	
	
	<section>
		<div class="container my-5">
            	
			<div class="row py-5">
				<?php for ($i = 0; $i < count($list); $i++) { ?>
					<div class="col-lg-3">			
						<div class="card h-100">
							<a href="<?php echo $common->seo($list[$i]['ref']); ?>"><img class="card-img-top" src="<?php echo $category->getIcon($list[$i]['ref']); ?>" alt="<?php echo $list[$i]['category_title']; ?>"></a>
							<br>
							<strong><?php echo $list[$i]['category_title']; ?></strong>
						</div>				
					</div>
				<?php } ?>
			</div>			
			
            <?php $common->pagination($page, $count); ?>
			
		</div>
	</section>
    <?php $pageHeader->footer(); ?>
    <?php $pageHeader->jsFooter(); ?>
  </body>
</html>
