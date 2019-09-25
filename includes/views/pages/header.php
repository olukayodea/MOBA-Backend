<?php
    class pageHeader extends database {
        function loginStrip($showCat=false, $login=false) {           
            global $country;
            global $wallet;
            global $inbox;
            $regionData = $country->getLoc($_SESSION['location']['code']);
            $msgCount = $inbox->getSortedList($_SESSION['users']['ref'], "to_id", "status", "SENT", "read", 0, "ref", "DESC", "AND", false, false, "count"); ?>
            
            <script>
            window.fbAsyncInit = function() {
                FB.init({
                appId      : '444545979676403',
                cookie     : true,
                xfbml      : true,
                version    : 'v3.3'
                });
                
                FB.AppEvents.logPageView();
                <?php if ($login == true) { ?>
                checkLoginState();
                <?php } ?>
                
            };

            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
            </script>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="<?php echo URL; ?>">MOBA</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <?php if (isset($_SESSION['users'])) { ?>
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo URL; ?>">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo URL; ?>hire">Post Ad</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo ucwords(strtolower($_SESSION['users']['screen_name'])); ?></a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="<?php echo URL; ?>ads">Posted Ads</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo URL; ?>bankAccounts">Bank Accounts</a>
                                    <a class="dropdown-item" href="<?php echo URL; ?>paymentCards">Payment Cards</a>
                                    <a class="dropdown-item" href="<?php echo URL; ?>transaction"> Transactions</a>
                                    <a class="dropdown-item" href="<?php echo URL; ?>wallet"> Wallets (<?php echo $regionData['currency_symbol'].number_format($wallet->balance($_SESSION['users']['ref'], $regionData['ref']), 2); ?>)</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo URL; ?>inbox">Messages <?php if ($msgCount > 0){ ?>  <span id="badge3"><?php echo $msgCount; ?></span><?php } ?></a></a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo URL; ?>profile">Profile</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="Javascript:void(0);" onclick="signOut();">Logout</a>
                                </div>
                            </li>

                            <?php if ($_SESSION['users']['user_type'] == "1") {
                                global $users;
                                $list = $users->getSortedList("1", "verified", false, false, false, false, "ref", "ASC", "AND", false, false, "count");?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="dropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Administrator</a>
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
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/users/admin">System Administrators</a>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/accuntVerification">Account Verification Request<?php if ($list > 0){ ?>  <span id="badge2"><?php echo $list; ?></span><?php } ?></a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo URL; ?>admin/options">System Settings</a>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo URL; ?>">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo URL; ?>hire">Post Ad</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo URL; ?>login">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo URL; ?>login?join">Register</a>
                            </li>
                        </ul>
                    <?php } ?>
                    <form method="get" class="form-inline my-2 my-lg-0" action="<?php echo URL."search"; ?>">
                        <?php if (isset($_SESSION['users'])) { ?>
                            <button type="button" class="btn btn-outline-succes my-2 my-sm-0" onclick="location='<?php echo URL.'notifications' ;?>'"><i class="fas fa-bell"></i><span id="badge"></span></button>
                        <?php } ?>
                        <button type="button" class="btn btn-outline-succes my-2 my-sm-0" onclick="location='<?php echo URL.'selectCity' ;?>'"><i class="fas fa-location-arrow"></i></button>
                        <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="s" id="s" value="<?php echo $_REQUEST['s']; ?>" required>
                        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                    </form>
                </div>
            </nav>
        <?php if ($showCat == true) {
                global $category;
                $parentList = $category->categoryList(); ?>
                
                <ul class="nav">
                <?php for ($i = 0; $i < count($parentList); $i++) {
                    $subCat = $category->categoryList($parentList[$i]['ref']);
                    $sub = false;
                    if (count($subCat) > 0) {
                        $sub = true;
                     } ?>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown<?php if ($sub == true) { ?>-toggle<?php } ?>" href="<?php echo $this->seo($parentList[$i]['ref'], "category"); ?>" role="button" aria-haspopup="false" aria-expanded="false">
                    <img src="<?php echo $category->getIcon($parentList[$i]['ref']); ?>" height="40" width="40"><br>
                    <?php echo ucwords(strtolower($parentList[$i]['category_title'])); ?></a>
                        <?php if ($sub == true) { ?>
                        <div class="dropdown-menu">
                            <?php for ($j = 0; $j < count($subCat); $j++) { ?>
                            <a class="dropdown-item" href="<?php echo $this->seo($subCat[$j]['ref'], "category"); ?>">
                            <img src="<?php echo $category->getIcon($subCat[$j]['ref']); ?>" height="40" width="40"><br>
                            <?php echo ucwords(strtolower($subCat[$j]['category_title'])); ?></a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </li>
                <?php } ?>
                </ul>
            <?php }
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
    
    <script type="text/javascript">
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
            FB.getLoginStatus(function(response) {
                if (response && response.status === 'connected') {
                    FB.logout(function(response) {
                        console.log('User signed out facebook.');
                    });
                }
            });
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
    <style type="text/css">
        @media screen and (min-width: 768px){
        .dropdown:hover .dropdown-menu, .btn-group:hover .dropdown-menu{
                display: block;
            }
            .dropdown-menu{
                margin-top: 0;
            }
            .dropdown-toggle{
                margin-bottom: 2px;
            }
            .navbar .dropdown-toggle, .nav-tabs .dropdown-toggle{
                margin-bottom: 0;
            }
        }
        </style>
        <?php }

        public function selector() { ?>
    <select name="jumpMenu" id="jumpMenu" onchange="MM_jumpMenu('parent',this,0)" class="form-control">
        <option value="?filter=all">Show all</option>
        <option value="?filter=client"<?php if ($_SESSION['filter'] == "client") { ?> selected<?php } ?>>Show all Posted Jobs</option>
        <option value="?filter=vendor"<?php if ($_SESSION['filter'] == "vendor") { ?> selected<?php } ?>>Show all Available Service Providers</option>
    </select>
    <script type="text/javascript">
    function MM_jumpMenu(targ,selObj,restore){ //v3.0
        eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
        if (restore) selObj.selectedIndex=0;
    }
    </script>
        <?php }
    }
?>