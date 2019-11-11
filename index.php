<?php
    $redirect = "index";
	include_once("includes/functions.php");
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Moba - Find the best artisans</title>
    <?php $pageHeader->headerFiles(); ?>
  </head>

  <body>
	<section>
    	<?php $pageHeader->loginStrip(true); ?>
		<div class="container-fluid p-0">
			<div class="row jcontent no-gutters">
			
				<div class="col-lg-6 left-bg py-5">
				
					<div class="pdd">
						<h2>FIND AND HIRE THE BEST PROFESSIONAL ARTISANS TODAY.</h2>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Molestias, expedita, saepe, vero rerum deleniti beatae veniam harum neque nemo praesentium cum alias asperiores commodi.</p>
					
						<form method="post" name="sentMessage" id="contactForm" action="">
							<div class="form-row">
                                <div class="col-md-10">
                                <input id="autocomplete" name="autocomplete" placeholder="Search Artisans" type="text" class="form-control" autocomplete="false" value="" required/>
                                </div>
                                <button type="submit" name="setLocation" class="btn purple-bn1 mb-2"><i class="fa fa-arrow-right" aria-hidden="true"></i> </button>
                            </div>
						</form>
					</div>
				</div>
				<div class="col-lg-6 right-img"></div>
			</div>
		</div>
	</section>
	<section class="moba-works">
		<div class="container">
			<h4>HOW MOBA WORKS</h4>
			<div class="row">
				<div class="col-lg-4 mt-3">
					<span>1</span><br><br>
					<h5 class="mt-3">Describe The Task</h5>
					<p>
						asdolor sit amet, consectetur adipisicing elit, sed 
						do eiusmodtempor incididunt ut labore etdolore 
						magna aliqua.
					</p>
				</div>
				<div class="col-lg-4">
					<span>2</span><br><br>
					<h5 class="mt-3">Get Matched</h5>
					<p>
						asdolor sit amet, consectetur adipisicing elit, sed 
						do eiusmodtempor incididunt ut labore etdolore 
						magna aliqua.
					</p>				
				</div>
				<div class="col-lg-4">
					<span>3</span><br><br>
					<h5 class="mt-3">Get It Done</h5>
					<p>
						asdolor sit amet, consectetur adipisicing elit, sed 
						do eiusmodtempor incididunt ut labore etdolore 
						magna aliqua.
					</p>				
				</div>
			</div>
			<div class="moba-line mt-5"></div>
		</div>
	</section>
	<section class="moba-category">
		<div class="container">
			<h4>WHO ARE YOU LOOKING FOR?</h4>
			<p>Select the category that best fits the task you want to get done.</p>

			<div class="row">
				<?php $userHome->pageContent("category"); ?>
			</div>
		</div>
	</section>
	
<?php $pageHeader->footer(); ?>
<?php $pageHeader->jsFooter(); ?>
  </body>

</html>