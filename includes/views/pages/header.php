<?php
    class pageHeader extends database {
        function loginStrip($home=false, $login=false) {           
            global $country;
            global $wallet;
            global $inbox;
            $regionData = $country->getLoc($_SESSION['location']['code']);
            $msgCount = $inbox->getSortedList($_SESSION['users']['ref'], "to_id", "status", "SENT", "read", 0, "ref", "DESC", "AND", false, false, "count"); ?>
            

            <?php if  ($home === true) {
                $nav = "nav-link"; ?>
            <nav class="navbar absolute-top navbar-expand-lg navbar-dark opaque-navbar">
            <?php } else {
                $nav = "nav-link1"; ?>
            <nav class="navbar absolute-top1 navbar-expand-lg navbar-dark bg-white">
            <?php } ?>
                <div class="container">
                    <a class="navbar-brand" href="<?php echo URL; ?>"><img src="<?php echo URL; ?>images/logo.png" width="80"></a>
                    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-auto">
                        <?php if ($_SESSION['users']['user_type'] != 1) { ?>
                            <li class="nav-item">
                                <a class="<?php echo $nav; ?>" href="<?php echo URL."allCategories"; ?>">ALL SERVICES</a>
                            </li>
                        <?php } ?>
                        <?php if (isset($_SESSION['users'])) { ?>
                            <li class="nav-item dropdown">
                                <a class="<?php echo $nav; ?> dropdown-toggle" href="#" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo strtoupper($_SESSION['users']['screen_name']); ?></a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="<?php echo URL; ?>ads">Posted Ads</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo URL; ?>bankAccounts">Bank Accounts</a>
                                    <a class="dropdown-item" href="<?php echo URL; ?>paymentCards">Payment Cards</a>
                                    <a class="dropdown-item" href="<?php echo URL; ?>transaction"> Transactions</a>
                                    <a class="dropdown-item" href="<?php echo URL; ?>wallet"> Wallets (<?php echo $regionData['currency_symbol'].number_format($wallet->balance($_SESSION['users']['ref'], $regionData['ref']), 2); ?>)</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo URL; ?>notification/inbox">Messages <?php if ($msgCount > 0){ ?>  <span id="badge3"><?php echo $msgCount; ?></span><?php } ?></a></a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo URL; ?>profile">Profile</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="Javascript:void(0);" onclick="signOut();">Logout</a>
                                </div>
                            </li>
                            <?php if ($_SESSION['users']['user_type'] == "2") {
                                global $users;
                                $list = $users->getSortedList("1", "verified", false, false, false, false, "ref", "ASC", "AND", false, false, "count");?>
                                <li class="nav-item dropdown">
                                    <a class="<?php echo $nav; ?> dropdown-toggle" href="#" id="dropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">ADMINISTRATOR</a>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/adverts">Posted Ads</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/banks">Banks</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/category">Categories</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/country">Country</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/rating">Ratings</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/accounts">Saved Accounts</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/cards">Saved Cards</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/transaction.pending">Pending Payment</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/transactions">Transactions</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/users">Users</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/users/providers">Service Providers</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/users/admin">System Administrators</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/accuntVerification">Account Verification Request<?php if ($list > 0){ ?>  <span id="badge2"><?php echo $list; ?></span><?php } ?></a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/options">System Settings</a>
                                    </div>
                                </li>
                            <?php } ?>
                        <?php } else { ?>
                        <li class="nav-item">
                        <a class="<?php echo $nav; ?>" href="<?php echo URL; ?>login?join">SIGNUP</a>
                        </li>
                        <li class="nav-item">
                        <a class="<?php echo $nav; ?>" href="<?php echo URL; ?>login">LOGIN</a>
                        </li>
                        <?php } ?>
                    </ul>
                    <form method="get" class="form-inline my-2 my-lg-0" action="<?php echo URL."search"; ?>">
                        <?php if (isset($_SESSION['users'])) { ?>
                            <button type="button" class="btn btn-outline-succes my-2 my-sm-0" onclick="location='<?php echo URL.'notifications' ;?>'"><i class="fas fa-bell" style="color:#3e3e94"></i><span id="badge"></span></button>
                            <button type="button" class="btn btn-outline-succes my-2 my-sm-0" onclick="location='<?php echo URL.'notifications/inbox' ;?>'"><i class="fas fa-envelope" style="color:#3e3e94"></i><span id="badge4"></span></button>
                        <?php } ?>
                        <button type="button" class="btn btn-outline-succes my-2 my-sm-0" onclick="location='<?php echo URL.'selectCity' ;?>'"><i class="fas fa-location-arrow" style="color:#3e3e94"></i></button>
                        <div class="row ml-2">
                            <div class="col-lg-10 p-0">					
                                <input type="text" class="form-control pd1 float-left" type="search" placeholder="Search" aria-label="Search" name="s" id="s" value="<?php echo $_REQUEST['s']; ?>" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="submit" class="btn purple-bn1 pd1">GO</button>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </nav>
        <?php
        }

        function navigation() {
            global $users;
            global $warning;
            global $errorMessage; ?>
            <div align="center">
                <?php if (isset($_REQUEST['done'])) { ?>
                    <div class="alert alert-success" role="alert">
                        <strong><?php echo $_REQUEST['done']; ?></strong>
                    </div>
                <?php } ?>
                <?php if (isset($errorMessage)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <strong><?php echo $errorMessage; ?></strong>
                    </div>
                <?php } ?>
                <?php if (isset($_REQUEST['error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <strong><?php echo $_REQUEST['error']; ?></strong>
                    </div>
                <?php } ?>
                <?php if (isset($_REQUEST['warning'])) { ?>
                    <div class="alert alert-warning" role="alert">
                        <strong><?php echo $_REQUEST['warning']; ?></strong>
                    </div>
                <?php } ?>
                <?php if (isset($warning)) { ?>
                    <div class="alert alert-warning" role="alert">
                        <strong><?php echo $warning; ?></strong>
                    </div>
                <?php } ?>
                <?php if ((isset($_SESSION['users'])) && ($_SESSION['users']['status'] == "NEW")) {
                    if ($users->listOnValue($_SESSION['users']['ref'], "status") == "NEW") { ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Your account is inactive, you will not be able to perform major functions on this site until you activate your account.<br>Click on the activation link in the Welcome E-Mail sent to you to activate this account</strong>
                    </div>
                    <?php } ?>
                <?php } else if ((isset($_SESSION['users'])) && ($_SESSION['users']['status'] == "INACTIVE")) { ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Your account has been deadivated.<br>Please contact us to resolve this issue</strong>
                    </div>
                <?php } ?>
            </div>
        <?php }

        function footer() { ?>
            <section class="moba-footer">	
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="float-left mr-3"><img src="<?php echo URL; ?>images/logo.png" width="80"></a></div>
                        <p>
                            Moba  dolor sit amet, consectetur adipisicing seddo 
                            eiusmodtempor consectetur incididun labore edipisicing etdolore.
                        </p>
                    </div>
                    <div class="col-lg-3">
                        <h6>QUICK LINK</h6>
                        <p>
                            <a href="#">Home</a><br>
                            <a href="#">Post Ad</a><br>
                            <a href="#">Sign Up</a><br>
                            <a href="#">Log In</a>
                        </p>
                    </div>
                    <div class="col-lg-2">
                        <h6>SOCIAL</h6>	
                        <a href="#">Facebook</a><br>
                        <a href="#">Twitter</a><br>
                        <a href="#">Linkedin</a><br>
                        <a href="#">instagram</a>
                        
                    </div>
                    <div class="col-lg-3">
                        <h6>CONTACT</h6>
                        <p>
                            07058794031, 08056569644<br>
                            info@moba.com
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <?php }

        function jsFooter() {
            global $options;
            $data = json_encode( explode( ",", $options->get("text_filter") ) ); ?>
            <!-- Optional JavaScript -->
    <!-- then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6/js/select2.min.js"></script>
    <script src="<?php echo URL; ?>js\jquery.confirm.min.js" async defer></script>
    <script src="https://apis.google.com/js/platform.js?onload=onLoad" async defer></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js" async defer></script>
    <script type="text/javascript">
	TweenLite.set('.right-img', {backgroundSize:"110%"});
	TweenLite.to('.right-img', 1.3, {backgroundSize:"100%", ease:Power1.easeOut, delay: 0.7}); 
        $(document).ready(function(){
            if ( !$.cookie("l_d") ) {
                getLocation();
            }

            if($('#content').length){
                $( '#content' ).focus();
            }

            $( "#s" ).autocomplete({
                source: "<?php echo URL; ?>includes/views/scripts/filterHome",
                minLength: 2,
                select: function( event, ui ) {
                    window.location.href = ui.item.id;
                }
            });
            $(".dropdown, .btn-group").hover(function(){
                var dropdownMenu = $(this).children(".dropdown-menu");
                if(dropdownMenu.is(":visible")){
                    dropdownMenu.parent().toggleClass("open");
                }
            });
        });

        $("#content, #project_dec, #subject, #message").keyup(function() {
            var restrictedWords = jQuery.parseJSON( '<?php echo strtolower( $data ); ?>' );
            var txtInput = $(this).val();  
            var error = 0;  
            for (var i = 0; i < restrictedWords.length; i++) {  
                var val = restrictedWords[i];
                if ( ( txtInput.toLowerCase() ).indexOf( val.toString() ) > -1 ) {  
                    var pattern = new RegExp(val.toString(), 'gi');
                    var res = txtInput.replace( pattern, "[censored word]" );
                    $(this).val( res );
                }  
            }
        });

        function getLocation() {
            if (navigator.geolocation) {
            var geoError = function() {
                location="<?php echo URL."selectCity"; ?>";
            };
            navigator.geolocation.getCurrentPosition(showPosition, geoError);
            } else {
            location="<?php echo URL."selectCity"; ?>";
            }
        }

        function showPosition(position) {
            var date = new Date();
            var cookieName = "js_loc";
            var cookieValue = position.coords.latitude+"_"+position.coords.longitude;

            if (cookieValue != "") {
                date.setTime(date.getTime()+(60*60));

                document.cookie = cookieName + "=" + (cookieValue || "") + "; path=/";
            }
        }
        
        function signOut() {
            var auth2 = gapi.auth2.getAuthInstance();
            if (auth2) {
                auth2.signOut().then(function () {
                    console.log('User signed out google.');
                    
                    var revokeAllScopes = function() {
                    auth2.disconnect();
                    }
                });
            }
            location='<?php echo URL; ?>login?logout';
        }

        function onLoad() {
        gapi.load('auth2', function() {
            gapi.auth2.init();
        });
        }
        <?php if (isset($_SESSION['users'])) { ?>
        var auto_refresh = setInterval(function () {
            var ref = <?php echo $_SESSION['users']['ref']; ?>;
            var dataString = "user="+ref;
            $.ajax({
              type: "POST",
              url: "<?php echo URL; ?>includes/views/scripts/notificationCounter",
              data: dataString,
              cache: false,
              success: function(html){
                  $('#badge').html('');
                  $(html).appendTo("#badge");
              }
            });
            $.ajax({
              type: "POST",
              url: "<?php echo URL; ?>includes/views/scripts/messagesCounter",
              data: dataString,
              cache: false,
              success: function(html){
                  $('#badge4').html('');
                  $(html).appendTo("#badge4");
                  $('#badge3').html('');
                  $(html).appendTo("#badge3");
              }
            });
            $.ajax({
              type: "POST",
              url: "<?php echo URL; ?>includes/views/scripts/adminNotification",
              data: dataString,
              cache: false,
              success: function(html){
                  $('#badge2').html('');
                  $(html).appendTo("#badge2");
              }
            });
        }, 2000);
        <?php } ?>
    </script>
        <?php }

        function headerFiles() { ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="google-signin-client_id" content="<?php echo GoogleClientId; ?>">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="<?php echo URL; ?>css/main.css" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js" integrity="sha256-lPE3wjN2a7ABWHbGz7+MKBJaykyzqCbU96BJWjio86U=" crossorigin="anonymous"></script>
    <!-- jQuery --->
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

    <link href="<?php echo URL; ?>css/style.css" rel="stylesheet" type="text/css">
    <script src="<?php echo URL; ?>SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
    <script src="<?php echo URL; ?>SpryAssets/SpryValidationPassword.js" type="text/javascript"></script>
    <script src="<?php echo URL; ?>SpryAssets/SpryValidationConfirm.js" type="text/javascript"></script>
    <script src="<?php echo URL; ?>SpryAssets/SpryValidationTextarea.js" type="text/javascript"></script>
    <script src="<?php echo URL; ?>SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
    <link href="<?php echo URL; ?>SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css">
    <link href="<?php echo URL; ?>SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css">
    <link href="<?php echo URL; ?>SpryAssets/SpryValidationConfirm.css" rel="stylesheet" type="text/css">
    <link href="<?php echo URL; ?>SpryAssets/SpryValidationTextarea.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo URL; ?>SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo URL; ?>css/imageUpload.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="<?php echo URL; ?>css/fileinput.css" media="all" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
        <?php }
    }
?>