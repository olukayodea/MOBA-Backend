<?php
    include_once("includes/functions.php");
    if (isset($_POST['setLocation'])) {

        $data['latitude'] = $_POST['lat'];
        $data['longitude'] = $_POST['lng'];
        $data['code'] = $_POST['country_code'];
        $data['city'] = $_POST['city'];
        $data['state'] = $_POST['state'];
        $data['state_code'] = $_POST['state'];
        $data['country'] = $_POST['country'];
        $_SESSION['location'] = $data;
        $cookie = serialize($data);

        setcookie("l_d", $cookie, time()+(60*60*24), "/");

        header("location: ".URL);
    }
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
						<h2>SET LOCATION.</h2>
                        <p>type your city or address in the space below.</p>
                        
                        <form method="post" name="sentMessage" id="contactForm" action="">
                            <div class="form-row">
                                <div class="col-md-10">
                                <input id="autocomplete" name="autocomplete" placeholder="Enter your address or city" onfocus="geolocate()" type="text" class="form-control" autocomplete="false" value="<?php echo $data['address']; ?>" required/>
                                </div>
                                <button type="submit" name="setLocation" class="btn purple-bn1-home mb-2"><i class="fa fa-location-arrow" aria-hidden="true"></i> </button>
                                
                            </div>

                            <input type="hidden" name="city" id="locality" placeholder="City" value="<?php echo $data['city']; ?>">
                            <input type="hidden" name="state" id="administrative_area_level_1" placeholder="State/Province" value="">
                            <input type="hidden" name="postal_code" id="postal_code" placeholder="Zip/Postal Code" value="">
                            <input type="hidden" name="country_code" id="country_code" placeholder="Country Code" value="">
                            <input type="hidden" name="country" id="country" placeholder="Country" value="">
                            <input type="hidden" name="lat" id="lat" placeholder="lat" value="">
                            <input type="hidden" name="lng" id="lng" placeholder="lng" value="">
                        </form>
					</div>
					
				</div>
				
				<div class="col-lg-6 right-img"></div>
				
			</div>
		</div>
		
	</section>
	
<?php $pageHeader->footer(); ?>
<?php $pageHeader->jsFooter(); ?>

<script type="text/javascript" src="<?php echo URL; ?>js/places.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GoogleAPI; ?>&libraries=places&callback=initAutocomplete" async defer></script>
  </body>

</html>